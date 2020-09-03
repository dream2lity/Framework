<?php

use core\Controller\Controller;
use core\Config\Env\Env;
use core\Log\Log;

class FooController extends Controller
{
    private $mysql;

    function __construct()
    {
        $this->mysql = new Mysql(
            Env::get('mysql')['host'],
            Env::get('mysql')['port'],
            Env::get('mysql')['database'],
            Env::get('mysql')['user'],
            Env::get('mysql')['password']
        );
    }

    function display($id, $name)
    {
        return [
            'id' => $id,
            'name' => $name,
        ];
    }

    function alias($foo, $bar)
    {
        return [
            'foo' => $foo,
            'bar' => $bar,
        ];
    }

    function all()
    {
        $sql = 'select * from my_test';
        return $this->makeResponse($this->mysql->query($sql));
    }

    function add($name, $age, $sex)
    {
        $sql = 'insert into my_test (`name`, age, sex) values (?, ?, ?)';
        $arr = func_get_args();
        return $this->mysql->prepare($sql, $arr);
    }

    function one($name)
    {
        $sql = 'select * from my_test where name = ?';
        $arr = func_get_args();
        return $this->mysql->prepare($sql, $arr);
    }

    function foo()
    {
        $data = 'foo......';
        Log::debug($data);
        return $this->makeResponse($data);
    }

    function makeResponse($data)
    {
        return call('Response', 'send', ['data'=>$data]);
    }
}