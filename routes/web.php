<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->group(['prefix' => 'rekognition'], function () use ($router) {

    $router->group(['middleware' => 'authAccess'], function () use ($router) {
        $router->post('/detectLabels', 'RekognitionController@detectLabels');
    });

});