<?php

namespace Noweh\Payzen;

use Illuminate\Support\ServiceProvider;

class PayzenServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //Publishes package config file to applications config folder
        $this->publishes([__DIR__.'/config/payzen.php' => config_path('payzen.php')]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('payzen', function() {
        	return new Payzen();
		});
    }
}
