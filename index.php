<?php
/*
*@Author:		Nahid Bin Azhar
*@Author URL:	http://nahid.co
*/

require_once "crudx.php";

$crud = new Crudx;

$data=$crud->table('users')->insertMany([
		['name'=>'Firoz Serniabat', 'username'=>'firoz', 'password'=>123456],
		['name'=>'Suhel Chowdhury', 'username'=>'suhel', 'password'=>123456]
	]);

var_dump($data);