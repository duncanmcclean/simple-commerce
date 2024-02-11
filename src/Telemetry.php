<?php

namespace DuncanMcClean\SimpleCommerce;

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Statamic\Statamic;

class Telemetry
{
    public static function send(): void
    {
        if (! config('simple-commerce.enable_telemetry', true)) {
            return;
        }

        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return;
        }

        if (! app()->environment('production') && ! app()->environment('testing')) {
            return;
        }

        $lastSentAt = null;
        $telemetryPath = storage_path('statamic/addons/simple-commerce/telemetry.json');

        if (File::exists($telemetryPath)) {
            $telemetry = json_decode(File::get($telemetryPath), true);
            $lastSentAt = Carbon::parse($telemetry['last_sent_at']);

            if ($lastSentAt->diffInDays(Carbon::now()) < 30) {
                return;
            }
        }

        [$ordersCountSinceLastTelemetry, $ordersGrandTotalSinceLastTelemetry] = static::ordersSinceLastTelemetry($lastSentAt ?? null);

        if (is_null($ordersCountSinceLastTelemetry) && is_null($ordersGrandTotalSinceLastTelemetry)) {
            return;
        }

        $payload = [
            'site_hash' => md5(config('app.url')),
            'statamic_version' => Statamic::version(),
            'addon_version' => SimpleCommerce::version(),
            'php_version' => phpversion(),
            'data' => [
                'last_sent_at' => $lastSentAt?->timestamp ?? null,
                'orders_count' => $ordersCountSinceLastTelemetry,
                'orders_grand_total' => $ordersGrandTotalSinceLastTelemetry,
            ],
        ];

        try {
            $request = Http::post('https://doublethree.digital/api/telemetry/simple-commerce', $payload);
        } catch (\Exception $e) {
            Log::warning("Simple Commerce was unable to phone home. Error: {$e->getMessage()}");

            return;
        }

        if (! $request->ok()) {
            return;
        }

        File::ensureDirectoryExists(storage_path('statamic/addons/simple-commerce'));

        File::put(storage_path('statamic/addons/simple-commerce/telemetry.json'), json_encode([
            'last_sent_at' => Carbon::now()->timestamp,
            'payload' => $payload,
        ]));
    }

    protected static function ordersSinceLastTelemetry(?Carbon $lastSentAt = null): array
    {
        $query = Order::query()
            ->wherePaymentStatus(PaymentStatus::Paid)
            ->when($lastSentAt, function ($query) use ($lastSentAt) {
                $query->where('status_log->paid', '>=', $lastSentAt->format('Y-m-d H:i'));
            });

        return [
            $query->count(),
            (int) $query->get()->map->grandTotal()->sum(),
        ];
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
