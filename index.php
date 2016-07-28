<?php
/*
*@Author:		Nahid Bin Azhar
*@Author URL:	http://nahid.co
*/

require_once 'src/Crudx.php';

use Nahid\Crudx\Crudx;

//use Nahid\Crudx\Crudx;
$config = [
    'host' => 'localhost',
    'user' => 'root',
    'password' => 'bolbona',
    'database' => 'crudx_db',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => 'tbl_',
];

$db = new Crudx($config);

//Crudx::table('user')->where('id', '=', 5)->get()->result();

/*
$user = $db->table('userget()
$user->name = 'Arufur Naim';
$user->username = 'naim';
$user->created_at = date('Y-m-d');

if($user->save()) {
    echo 'Success';
}*/

echo '<pre>';
var_dump($db->table('users')->where('username', '=', 'nahid')->first(['name'])->result());
