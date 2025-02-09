<?php

namespace DuncanMcClean\SimpleCommerce\Console\Commands;

use DuncanMcClean\SimpleCommerce\Data\Currencies;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Collection;
use Statamic\Facades\Site;
use Statamic\Support\Str;
use Stillat\Proteus\Support\Facades\ConfigWriter;

use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class InstallCommand extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:simple-commerce:install';

    protected $description = 'Installs Simple Commerce.';

    public function handle(): void
    {
        // todo: configure database orders

        $this
            ->publishConfig()
            ->promptForSiteCurrencies()
            ->promptForProductsCollection()
            ->promptToPublishCheckoutStubs();

        $this->line('  <fg=green;options=bold>Simple Commerce has been installed!</> 🎉');
        $this->newLine();
        $this->line('  For more information on getting started, please review the documentation: <comment>https://simple-commerce.duncanmcclean.com</comment>');
    }

    private function publishConfig(): self
    {
        $this->call('vendor:publish', [
            '--tag' => 'simple-commerce-config',
            '--force' => true,
        ]);

        return $this;
    }

    private function promptForSiteCurrencies(): self
    {
        $sites = Site::all()->map(function ($site) {
            $currency = search(
                label: "Which currency should the {$site->name()} site use?",
                options: fn (string $value) => Currencies::mapWithKeys(fn ($currency) => [$currency['code'] => "{$currency['name']} ({$currency['code']})"])
                    ->when($site->attribute('currency'), fn ($currencies) => $currencies->filter(fn ($name, $code) => $code === $site->attribute('currency')))
                    ->when(strlen($value) > 0, fn ($currencies) => $currencies->filter(fn ($name) => Str::contains($name, $value, ignoreCase: true)))
                    ->all(),
            );

            return array_merge($site->rawConfig(), [
                'attributes' => array_merge($site->attributes(), ['currency' => $currency]),
            ]);
        });

        Site::setSites($sites->all())->save();

        return $this;
    }

    private function promptForProductsCollection(): self
    {
        $collection = select(
            label: 'Which collection contains your products?',
            options: Collection::all()
                ->mapWithKeys(fn ($collection) => [$collection->handle() => $collection->title()])
                ->push('Create a "Products" collection')
                ->all(),
            hint: 'If you need to, you can always add additional collections later.'
        );

        if ($collection === 'Create a "Products" collection') {
            $name = text('What should the collection be called?', default: 'Products');

            Collection::make($collection = Str::studly($name))
                ->title($name)
                ->routes([Site::default()->handle() => Str::plural(Str::kebab($name)).'/{slug}'])
                ->save();
        }

        ConfigWriter::write('statamic.simple-commerce.products.collections', [$collection]);

        return $this;
    }

    private function promptToPublishCheckoutStubs(): self
    {
        if (File::exists(resource_path('views/checkout'))) {
            return $this;
        }

        $this->line('  Simple Commerce is designed to be flexible and customizable, and that includes the checkout process.');
        $this->line('  You can either publish the pre-built Checkout page provided by Simple Commerce, or build your own from scratch.');

        $choice = select('What would you like to do?', [
            'Publish the pre-built Checkout page',
            'I will build my own Checkout page',
        ]);

        if ($choice === 'I will build my own Checkout page') {
            return $this;
        }

        $this->call('vendor:publish', [
            '--tag' => 'simple-commerce-checkout-stubs',
            '--force' => true,
        ]);

        $routes = File::get(base_path('routes/web.php'));

        $routes .= <<<'PHP'

Route::statamic('checkout', 'checkout.index', ['title' => 'Checkout', 'layout' => 'checkout.layout'])
    ->name('checkout');

Route::statamic('checkout/confirmation', 'checkout.confirmation', ['title' => 'Order Confirmation', 'layout' => 'checkout.layout'])
    ->name('checkout.confirmation')
    ->middleware('signed');
PHP;

        File::put(base_path('routes/web.php'), $routes);

        $this->components->info("Published. You'll find the Checkout views in <comment>resources/views/checkout</comment>. Feel free to customize them as necessary.");

        return $this;
    }
}
