# Laravel-Payzen

[![Payzen](https://img.shields.io/static/v1?message=Payzen&color=blue&logo=Payzen&logoColor=FFFFFF&label=)](https://payzen.io/en-EN/)
[![Laravel](https://img.shields.io/badge/Laravel-v5/6/7/8/9/10-828cb7.svg?logo=Laravel&color=FF2D20)](https://laravel.com/)
[![Run Tests](https://github.com/noweh/laravel-payzen/actions/workflows/run-tests.yml/badge.svg?branch=master)](https://github.com/noweh/laravel-payzen/actions/workflows/run-tests.yml)
[![MIT Licensed](https://img.shields.io/github/license/noweh/laravel-payzen)](LICENSE)
[![last version](https://img.shields.io/packagist/v/noweh/laravel-payzen)](https://packagist.org/packages/noweh/laravel-payzen)
[![Downloads](https://img.shields.io/packagist/dt/noweh/laravel-payzen)](https://packagist.org/packages/noweh/laravel-payzen)

The library provides an easy and fast Payzen form creation.
This helps to instanciate all required parameters and create the form to access to payment interface.
To know required parameters, go to https://payzen.io/en-EN/form-payment/quick-start-guide/sending-a-payment-form-via-post.html

## Installation
First you need to add the component to your composer.json
```
composer require noweh/laravel-payzen
```
Update your packages with *composer update* or install with *composer install*.

Laravel uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.

### Laravel without auto-discovery

    Noweh\Payzen\PayzenServiceProvider::class,

To use the facade, add this in app.php:

    'Payzen' => Noweh\Payzen\PayzenFacade::class,

### Service Provider
After updating composer, add the ServiceProvider to the providers array in config/app.php

## Configuration file

Next, you must migrate config :

    php artisan vendor:publish --provider="Noweh\Payzen\PayzenServiceProvider"

## Create a payment form
Now we are finally ready to use the package! Here is a little example:
```php
     $blocks_html = \Payzen::set_amount(300)
        ->set_trans_id(123456)
        ->set_order_info(\Payzen::ascii_transcode('an information', 'an', 255, true))
        ->set_order_info2(\Payzen::ascii_transcode('another information', 'an', 255, true))
        ->set_url_return(request()->fullUrl())
        ->set_return_mode('POST')
        ->set_signature()
        ->get_form('<div><input id="spSubmit" type="submit" value="Pay" class="Button Button--black"></div>')
    ;
```

## Check Payzen response signature
```php
     $payzen = \Payzen::set_params(\Arr::where(request()->all(), function($value, $key) {
                 	return strrpos($key, 'vads_', -5) !== false;
             	}))
     			->set_signature()
     		;
             return (request()->input('signature') && ($payzen::get_signature() === request()->input('signature')));
    ;
```

## Other useful functions

### add_product
Add a product to the order
#### Parameters
array $product , must have the following keys : 'label,amount,type,ref,qty
#### Example
```php
    \Payzen::add_product(
        [
            'label' => 'Concert Coldplay 2016',
            'amount' => 235.00,
            'type' => 'ENTERTAINMENT',
            'ref' => 'COLD016',
            'qty' => 3
        ]
    );
```
Note : the amount of each products price **must not** be multiplied by 100

### set_amount
Defines the total amount of the order. If you doesn't give the amount in parameter, it will be automaticly calculated by the sum of products you've got in your basket.
#### Parameters
[optional] int $amount, Payzen format. ex : for a product with a price of 150€, give 15000
#### Example
```php
   $payzen = \Payzen::add_product(
       [
           'label' => 'Concert Coldplay 2016',
           'amount' => 235.00,
           'type' => 'ENTERTAINMENT',
           'ref' => 'COLD016',
           'qty' => 3
       ]
   );
   $payzen->set_amount();
   echo $payzen->get_amount(); //will display 705.00 (3*235.00)
```

### get_amount
Get total amount of the order
#### Parameters
[optional] bool $decimal if true, you get a decimal otherwise you get standard Payzen amount format (int). Default value is true.
#### Example
```php
  $payzen = \Payzen::add_product(
      [
          'label' => 'Concert Coldplay 2016',
          'amount' => 235.00,
          'type' => 'ENTERTAINMENT',
          'ref' => 'COLD016',
          'qty' => 3
      ]
  );
  $payzen->set_amount();
  echo $payzen->get_amount(); //will display 705.00 (3*235.00)
  echo $payzen->get_amount(false); //will display 70500 (3*235.00)
```

### set_params
Method to do massive assignement of parameters
#### Parameters
array $params associative array of Payzen parameters
#### Example
```php
   \Payzen::set_params(
       [
           'vads_page_action' => 'PAYMENT',
           'vads_action_mode' => 'INTERACTIVE',
           'vads_payment_config' => 'SINGLE',
           'vads_version' => 'V2',
           'vads_trans_date' => gmdate('YmdHis'),
           'vads_currency' => '978'
       ]
   );
```

### ascii_transcode
Method to convert an input in a compatible for Payzen
#### Parameters
string $input, text to convert

string $allow, n|a|an|ans

int $length 1..255

boolean $truncate allow returning truncated result if the transcoded $input length is over $length
#### Example
```php
    \Payzen::ascii_transcode('123nd', 'n', 5, true);
```

### check signature in response
Checking Payzen response signature
#### Example
```php
    \Payzen::isResponseSignatureValid();
```
