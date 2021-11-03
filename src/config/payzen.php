<?php

return [
	'url' => 'https://secure.payzen.eu/vads-payment/',
	'site_id' => env('PAYZEN_SITE_ID'),
	'key' => 'YOUR_KEY',
	'env' => 'PRODUCTION',
	'params' => [
		//Put here your generals payment call parameters
		'vads_page_action' => 'PAYMENT',
		'vads_action_mode' => 'INTERACTIVE',
		'vads_payment_config' => 'SINGLE',
		'vads_version' => 'V2',
		'vads_currency' => '978'
    ]
];
