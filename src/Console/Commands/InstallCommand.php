<?php

namespace DuncanMcClean\SimpleCommerce\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Statamic\Console\RunsInPlease;

use function Laravel\Prompts\confirm;

class InstallCommand extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:simple-commerce:install';

    protected $description = 'Installs Simple Commerce.';

    public function handle(): void
    {
        $this
            ->promptToCreateProductsCollection()
            ->promptToPublishCheckoutStubs();
    }

    private function promptToCreateProductsCollection(): self
    {
        // todo

        return $this;
    }

    private function promptToPublishCheckoutStubs(): self
    {
        if (! confirm('Would you like to publish the checkout stubs?')) {
            return $this;
        }

        $this->call('vendor:publish', [
            '--tag' => 'simple-commerce-checkout-stubs',
            '--force' => true,
        ]);

        $routes = File::get(base_path('routes/web.php'));

        $routes .= <<<PHP

Route::statamic('checkout', 'checkout.index', ['title' => 'Checkout', 'layout' => 'checkout.layout'])
    ->name('checkout');

Route::statamic('checkout/confirmation', 'checkout.confirmation', ['title' => 'Order Confirmation', 'layout' => 'checkout.layout'])
    ->name('checkout.confirmation')
    ->middleware('signed');
PHP;

        File::put(base_path('routes/web.php'), $routes);

        $this->components->info("Published. You'll find the checkout stubs in resources/views/checkout.");

        return $this;
    }
}
