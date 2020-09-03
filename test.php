<?php

include_once 'libs/Mysql.php';

$db = new Mysql('172.17.207.161','3306', 'test', 'common', '123456');
//$sql = <<<EOF
//create table `my_test`(
//  id int(11) not null auto_increment,
//  name varchar(255),
//  age int(3),
//  sex enum('男','女'),
//  primary key(id) using btree
//) engine=innodb default charset=utf8mb4
//EOF;
//var_dump($db->exec($sql));

$sql = 'insert into my_test (`name`, age, sex) values (?, ?, ?)';
$arr = [
    'Timmy', 26, '男'
];
var_dump($db->prepare($sql, $arr));

$sql = 'select * from my_test where name = ? and age = ?';
$arr = [
    'Tom', 29
];
var_dump($db->prepare($sql, $arr));
