<?php

namespace Damcclean\Commerce\Tests\Console;

use Damcclean\Commerce\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;

class InstallCommandTest extends TestCase
{
    /** @test */
    public function install_command_can_publish_vendor_config()
    {
        (new Filesystem())->delete(config_path().'/commerce.yaml');
        $this->assertFileNotExists(config_path().'/commerce.yaml');

        $command = $this->artisan('commerce:install');
        $command->run();

        $command->assertExitCode(0);

        $this->assertFileExists(config_path().'/commerce.php');
    }

    /** @test */
    public function install_command_can_publish_vendor_views()
    {
        (new Filesystem())->deleteDirectory(resource_path('views/vendor/commerce'));
        $this->assertDirectoryNotExists(resource_path('views/vendor/commerce'));

        $command = $this->artisan('commerce:install');
        $command->run();

        $command->assertExitCode(0);

        $this->assertDirectoryExists(resource_path('views/vendor/commerce'));
        $this->assertFileExists(resource_path('views/vendor/commerce').'/checkout.antlers.html');
        $this->assertFileExists(resource_path('views/vendor/commerce').'/layout.antlers.html');
        $this->assertFileExists(resource_path('views/vendor/commerce').'/product.antlers.html');
        $this->assertFileExists(resource_path('views/vendor/commerce').'/products.antlers.html');
        $this->assertFileExists(resource_path('views/vendor/commerce').'/search.antlers.html');
        $this->assertFileExists(resource_path('views/vendor/commerce').'/thanks.antlers.html');
    }

    /** @test */
    public function install_command_can_publish_blueprints()
    {
        (new Filesystem())->delete(resource_path('blueprints').'/coupon.yaml');
        (new Filesystem())->delete(resource_path('blueprints').'/customer.yaml');
        (new Filesystem())->delete(resource_path('blueprints').'/order.yaml');
        (new Filesystem())->delete(resource_path('blueprints').'/product.yaml');

        $this->assertFileNotExists(resource_path('blueprints').'/coupon.yaml');
        $this->assertFileNotExists(resource_path('blueprints').'/customer.yaml');
        $this->assertFileNotExists(resource_path('blueprints').'/order.yaml');
        $this->assertFileNotExists(resource_path('blueprints').'/product.yaml');

        $command = $this->artisan('commerce:install');
        $command->run();

        $command->assertExitCode(0);

        $this->assertFileExists(resource_path('blueprints').'/coupon.yaml');
        $this->assertFileExists(resource_path('blueprints').'/customer.yaml');
        $this->assertFileExists(resource_path('blueprints').'/order.yaml');
        $this->assertFileExists(resource_path('blueprints').'/product.yaml');
    }

    /** @test */
    public function install_command_can_create_file_structure()
    {
        (new Filesystem())->deleteDirectory(base_path('content/commerce'));
        $this->assertDirectoryNotExists(base_path('content/commerce'));

        $command = $this->artisan('commerce:install');
        $command->run();

        $command->assertExitCode(0);

        $this->assertDirectoryExists(base_path().'/content/commerce');
    }
}
