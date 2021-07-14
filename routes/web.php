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

        // $router->group(['middleware' => 'cors'], function () use ($router) {

        $router->group(['prefix' => 'project'], function () use ($router) {
            $router->get('/', ['middleware' => 'manager', 'uses' => 'Project\ProjectController@index']);
            $router->post('/', ['middleware' => 'manager', 'uses' => 'Project\ProjectController@store']);
            $router->post('/edit/{id:[0-9]+}', ['middleware' => 'manager', 'uses' => 'Project\ProjectController@update']);
            $router->get('/tasks', 'Project\ProjectController@tasks');
            $router->post('/destroy/{id:[0-9]+}', 'Project\ProjectController@destroy');
            $router->post('/add-user/', ['middleware' => 'manager', 'uses' => 'Project\ProjectController@addUser']);
            $router->get('/all-users/{id:[0-9]+}', ['middleware' => 'manager', 'uses' => 'Project\ProjectController@allUser']);
        });

        $router->group(['prefix' => 'tasks'], function () use ($router) {
            $router->get('/', 'Task\TaskController@index');
            $router->post('/', ['middleware' => 'auth', 'uses' => 'Task\TaskController@store']);
            $router->post('/edit/{id:[0-9]+}', ['middleware' => 'manager', 'uses' => 'Task\TaskController@update']);
            $router->post('/destroy/{id:[0-9]+}', ['middleware' => 'manager', 'uses' => 'Task\TaskController@destroy']);
            $router->post('/add-user/', ['middleware' => 'manager', 'uses' => 'Task\TaskController@addUser']);
            $router->get('/current-users/{id:[0-9]+}', ['middleware' => 'manager', 'uses' => 'Task\TaskController@currentUsers']);
        });

        $router->group(['prefix' => 'user'], function () use ($router) {
            $router->get('/', ['middleware' => 'manager', 'uses' => 'UserController@index']);
            $router->post('/create', ['middleware' => 'manager', 'uses' => 'AuthController@store']);
            $router->get('/profile', 'UserController@profile');
            $router->get('/project/{id:[0-9]+}/tasks', 'UserController@projectTasks');
            $router->get('/projects', 'UserController@projects');
            $router->get('/tasks', 'UserController@tasks');
            $router->get('/logout', 'UserController@logout');
            $router->post('/destroy/{id}', ['middleware' => 'manager', 'uses' => 'UserController@destroy']);
            $router->post('/edit/{id}', ['middleware' => 'manager', 'uses' => 'UserController@edit']);
            $router->post('/password-reset/{id}', ['middleware' => 'admin', 'uses' => 'AuthController@passwordReset']);
        });
        $router->get('/total', ['middleware' => 'manager', 'uses' => 'UserController@total']);
        $router->get('/users-list/{id:[0-9]+}', ['middleware' => 'manager', 'uses' => 'UserController@usersList']);

        $router->post('/login', 'AuthController@login');
//        $router->get('/', function () use ($router) {
//            return "Direct Access not permitted!";
//        });
    });
});


$router->get('/', function () use ($router) {
    return "Direct Access not permitted!";
});
