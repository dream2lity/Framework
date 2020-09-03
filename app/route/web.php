<?php

use core\Route\Route;
use core\Http\Response\JsonResponse;

Route::get('/', function () {
    echo 'Hello World!' . PHP_EOL;
    exit();
});

Route::get('test', function (){

    return \core\Config\Env\Env::get();
});

Route::get('foo', function (){
    echo 'In Foo !' . PHP_EOL;
    exit();
});


Route::get('bar', function (){
    echo 'In Bar !' . PHP_EOL;
    exit();
});


Route::get('foo/{name}', function ($name){
    echo 'I\'m ' . $name . '!!!!!' . PHP_EOL;
    exit();
});

Route::get('foo/{name}/place/{place}', function ($name, $place){
    echo 'I\'m ' . $name . '!!!!! In ' . $place . '!' . PHP_EOL;
    exit();
});

Route::post('user/{param2}/{param1}', function ($name, $place, $id){
    return $id . ': I\'m ' . $name . '!!!!! In ' . $place . '!';
}, [
    'name' => 'param1',
    'place' => 'param2',
    'id' => 'i'
]);

Route::get('user{id}/name{name}', function ($id, $name) {
    echo 'user info: ' . PHP_EOL;
    echo "\tID: $id" . PHP_EOL;
    echo "\tNAME: $name" . PHP_EOL;
    exit();
});

Route::get('guzzle', function () {
//    $res = new \core\Http\Response\Json2Response(null, 200, ['Content-type'=>'application/json']);
//    $res->render();
    $res = new JsonResponse('Hello World!!', 200, [], null, [
        [
            'name' => 'id',
            'value' => '20180204',
        ]
    ]);
    return $res;
});

Route::match(['GET', 'POST'],'baz', function () {
    return 'In baz!';
});

Route::get('controller/foo', 'FooController@index');

Route::get('controller/foo/{id}/{name}', 'FooController@display');

Route::get('controller/fooInfo', 'FooController@display');

Route::get('controller/foo/alias', 'FooController@alias', [
    'foo' => 'id',
    'bar' => 'name',
]);

Route::get('foo/all', 'FooController@all');

Route::get('foo/add', 'FooController@add');

Route::get('foo/one/{name}', 'FooController@one');

Route::get('foo/foo', 'FooController@foo');