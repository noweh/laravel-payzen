<?php

namespace Noweh\Payzen\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Noweh\Payzen\PayzenServiceProvider;
use Noweh\Payzen\PayzenFacade;

class TestCase extends OrchestraTestCase
{
    /**
     * Load package service provider.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            PayzenServiceProvider::class,
        ];
    }

    /**
     * Load package alias.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageAliases($app): array
    {
        return [
            'Payzen' => PayzenFacade::class,
        ];
    }
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('payzen', [
            'url' => 'https://secure.payzen.eu/vads-payment/',
            'site_id' => 1234,
            'key' => 'QWERTY135',
            'env' => 'TEST',
            'params' => [
                'vads_page_action' => 'PAYMENT',
                'vads_action_mode' => 'INTERACTIVE',
                'vads_payment_config' => 'SINGLE',
                'vads_version' => 'V2',
                'vads_currency' => '978'
            ],
        ]);
    }
}
