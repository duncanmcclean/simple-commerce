<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\DigitalProducts;

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Products\ProductType;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Statamic\Assets\Asset;
use Statamic\Facades\AssetContainer;
use ZipArchive;

class DownloadController extends Controller
{
    public function __invoke(Request $request)
    {
        $order = Order::find($request->orderId);
        $item = $order->lineItems()->firstWhere('id', $request->lineItemId);

        if (! $item->metadata()->has('license_key') || $item->metadata()->get('license_key') !== $request->get('license_key')) {
            abort(401);
        }

        $product = $item->product();

        $zip = new ZipArchive;
        $zip->open(storage_path("{$order->id()}__{$item->id()}__{$product->id()}.zip"), ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($product->purchasableType() === ProductType::Product) {
            if (! $product->has('downloadable_asset')) {
                throw new \Exception("Product [{$product->id()}] does not have any digital downloadable assets.");
            }

            $product->toAugmentedArray()['downloadable_asset']->value()->get()
                ->each(function (Asset $asset) use ($request, $order, $item, $product, &$zip) {
                    if ($item->metadata()->has('download_history') && $product->has('download_limit')) {
                        if (collect($item->metadata()->get('download_history'))->count() >= $product->get('download_limit')) {
                            abort(405, "You've reached the download limit for this product.");
                        }
                    }

                    $order->updateLineItem($item->id(), [
                        'metadata' => array_merge($item->metadata()->toArray(), [
                            'download_history' => array_merge([
                                [
                                    'timestamp' => now()->timestamp,
                                    'ip_address' => $request->ip(),
                                ],
                            ], $item->metadata()->get('download_history', [])),
                        ]),
                    ]);

                    $zip->addFile($asset->resolvedPath(), "{$product->get('slug')}/{$asset->basename()}");
                });
        }

        if ($product->purchasableType() === ProductType::Variant) {
            $productVariant = $product->variant($item->variant()['variant']);

            if (! $productVariant->has('downloadable_asset')) {
                throw new \Exception("Product [{$product->id()}] does not have any digital downloadable assets.");
            }

            $productVariantsField = $product->resource()->blueprint()->field('product_variants');

            $downloadableAssetField = collect($productVariantsField->get('option_fields'))
                ->where('handle', 'downloadable_asset')
                ->first();

            collect($productVariant->get('downloadable_asset'))
                ->map(function ($assetPath) use ($downloadableAssetField) {
                    $assetContainer = isset($downloadableAssetField['field']['container'])
                        ? AssetContainer::findByHandle($downloadableAssetField['field']['container'])
                        : AssetContainer::all()->first();

                    return $assetContainer->asset($assetPath);
                })
                ->each(function (Asset $asset) use ($request, $order, $item, $product, $productVariant, &$zip) {
                    if (config('sc-digital-products.download_history')) {
                        if ($item->metadata()->has('download_history') && $productVariant->has('download_limit') && $product->get('download_limit') !== null) {
                            if (collect($item->metadata()->get('download_history'))->count() >= $productVariant->get('download_limit')) {
                                abort(405, "You've reached the download limit for this product.");
                            }
                        }

                        $order->updateLineItem($item->id(), [
                            'metadata' => array_merge($item->metadata()->toArray(), [
                                'download_history' => array_merge([
                                    [
                                        'timestamp' => now()->timestamp,
                                        'ip_address' => $request->ip(),
                                    ],
                                ], $item->metadata()->get('download_history', [])),
                            ]),
                        ]);
                    }

                    $zip->addFile($asset->resolvedPath(), "{$productVariant->key()}/{$asset->basename()}");
                });
        }

        $zip->close();

        return response()->download(storage_path("{$order->id()}__{$item->id()}__{$product->id()}.zip"), "{$product->get('slug')}.zip");
    }
}
