<?php

namespace OpentokLaravel\Facades;

use Illuminate\Support\Facades\Facade as BaseFacade;

class Opentok extends BaseFacade {

   /**
	* Get the registered name of the component.
	*
	* @return string
    */
    protected static function getFacadeAccessor() { return 'Opentok'; }

}
