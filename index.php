<?php
/*
*@Author:		Nahid Bin Azhar
*@Author URL:	http://nahid.co
*/

require_once 'vendor/autoload.php';

use Nahid\Crudx\Facade\Crudx;
//use Nahid\Crudx\Crudx;


$db = new Crudx;

//Crudx::table('user')->where('id', '=', 5)->get()->result();


$data=Crudx::table('posts')
->first(['blog', 'id']);



var_dump($data->get(['id', 'blog'])->result());
