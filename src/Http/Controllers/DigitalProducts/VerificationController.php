<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\DigitalProducts;

use DuncanMcClean\SimpleCommerce\Orders\EloquentOrderRepository;
use DuncanMcClean\SimpleCommerce\Orders\EntryOrderRepository;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Statamic\Facades\Entry;

class VerificationController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'license_key' => ['required', 'string'],
        ]);

        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)) {
            $orderQuery = Entry::query()
                ->where('collection', SimpleCommerce::orderDriver()['collection'])
                ->whereIn('order_status', [
                    OrderStatus::Placed->value,
                    OrderStatus::Dispatched->value,
                ])
                ->where('items->0->metadata->license_key', $validated['license_key'])
                ->orWhere('items->1->metadata->license_key', $validated['license_key'])
                ->orWhere('items->2->metadata->license_key', $validated['license_key'])
                ->orWhere('items->3->metadata->license_key', $validated['license_key'])
                ->orWhere('items->4->metadata->license_key', $validated['license_key'])
                ->orWhere('items->5->metadata->license_key', $validated['license_key'])
                ->orWhere('items->6->metadata->license_key', $validated['license_key'])
                ->orWhere('items->7->metadata->license_key', $validated['license_key'])
                ->orWhere('items->8->metadata->license_key', $validated['license_key'])
                ->orWhere('items->9->metadata->license_key', $validated['license_key'])
                ->limit(1)
                ->get();
        }

        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EloquentOrderRepository::class)) {
            $orderModel = new (SimpleCommerce::orderDriver()['model']);

            $orderQuery = $orderModel::query()
                ->whereIn('order_status', [
                    OrderStatus::Placed->value,
                    OrderStatus::Dispatched->value,
                ])
                ->whereRaw("JSON_EXTRACT(items, '$[0].metadata.license_key') = ?", [$validated['license_key']])
                ->limit(1)
                ->get();
        }

        return $orderQuery->count() > 0
            ? $this->validResponse($validated)
            : $this->invalidResponse($validated);
    }

    protected function validResponse($validated): array
    {
        return [
            'license_key' => $validated['license_key'],
            'valid' => true,
        ];
    }

    protected function invalidResponse($validated): array
    {
        return [
            'license_key' => $validated['license_key'],
            'valid' => false,
        ];
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
