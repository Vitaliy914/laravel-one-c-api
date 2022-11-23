<?php
namespace Vitaliy914\OneCApi;

use Illuminate\Support\ServiceProvider;

class OneCApiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = $this->getPatch() . 'config/one-c.php';
        $this->publishes([
            $configPath => config_path('one-c.php'),
        ],
            'config'
        );

        $this->publishes([
            $this->getPatch() . 'migrations' => database_path('migrations'),
        ],
            'migrations'
        );


        include __DIR__ . '/routes/api.php';
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $configPath = $this->getPatch() . 'config/one-c.php';
        $this->mergeConfigFrom($configPath, 'one-c');
    }

    /**
     * @return string
     */
    private function getPatch()
    {
        return __DIR__ . '/laravel-one-c-api/';
    }
}
