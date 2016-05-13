<?php
namespace Nahid\Crudx\Facade;

use Nahid\Crudx\Crudx;
/**
* 
*/

class Crudx
{

	public static function __callStatic($method, $args)
	{
		$crudx = new Crudx();
		if(method_exists($crudx, $method)) {
			return call_user_func_array([$crudx, $method], $args);
		}
	}
}