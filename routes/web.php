<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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
$router->group(['prefix' => 'api'], function () use ($router) {

    $router->group(['prefix' => 'v1'], function () use ($router) {

        $router->group(['prefix' => 'project'], function () use ($router) {
            $router->get('/', ['middleware' => 'manager', 'uses' => 'Project\ProjectController@index']);
            $router->post('/', ['middleware' => 'manager', 'uses' => 'Project\ProjectController@store']);
            $router->post('/edit', ['middleware' => 'manager', 'uses' => 'Project\ProjectController@update']);
            $router->get('/tasks','Project\ProjectController@tasks');
        });

        $router->group(['prefix' => 'tasks'], function () use ($router) {
            $router->get('/', 'Task\TaskController@index');
            $router->post('/', 'Task\TaskController@store');
            $router->post('/edit', 'Task\TaskController@update');
            $router->post('/destroy','Task\TaskController@destroy');
        });

        $router->group(['prefix' => 'user'], function () use ($router) {
            $router->get('/', ['middleware' => 'manager', 'uses' => 'UserController@show']);
            $router->get('/profile', 'UserController@profile');
            $router->post('/create', ['middleware' => 'manager', 'uses' => 'AuthController@store']);
            $router->get('/projects','UserController@projects');
            $router->get('/tasks','UserController@tasks');
            $router->get('/project/{id}/tasks','UserController@projectTasks');
        });
        $router->post('/login', 'AuthController@login');
    });
});


$router->get('/', function () use ($router) {
    return "Direct Access not permitted!";
});
