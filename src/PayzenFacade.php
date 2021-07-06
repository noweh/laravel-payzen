<?php

namespace Noweh\Payzen;

use Illuminate\Support\Facades\Facade;

class PayzenFacade extends Facade
{
	protected static function getFacadeAccessor()
	{
		return 'payzen';
	}
}
