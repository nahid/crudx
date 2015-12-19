<?php
/*
*@Author:		Nahid Bin Azhar
*@Author URL:	http://nahid.co
*/

require_once "crudx.php";

$user = Crudx::table('posts');

$user->blog="Hello newwww blog";
$user->user_id=1;


//Crudx::table('user')->where('id', '=', 5)->get()->result();


$data=Crudx::table('posts')
->join('users', 'posts.user_id', 'users.id')
->get(['name']);



var_dump($data->getQueryString());
