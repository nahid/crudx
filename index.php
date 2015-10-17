<?php
/*
*@Author:		Nahid Bin Azhar
*@Author URL:	http://nahid.co
*/

require_once "crudx.php";

$user = Crudx::table('users');

$user->name="Firozx";
$user->username="firoz";
$user->password="123456";
$user->created_at="2015-10-10";


$save=$user->where('id','=',5)->save();

if($save){
	echo 'Successfully Updated';
}