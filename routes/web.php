<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use App\Models\Page;

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

$router->get('/', function () use ($router) {
    // $pages = Page::with("facebookLog")->get();

    // dd($pages->toArray());

    return $router->app->version();
});

$router->get('/version', function () use ($router) {
    return $router->app->version();
});

$router->group([

    'prefix' => 'api'

], function ($router) {
    $router->post('login', 'AuthController@login');
    $router->post('logout', 'AuthController@logout');
    $router->post('refresh', 'AuthController@refresh');
    $router->post('user', 'AuthController@me');

    // Post
    $router->get('post/{id}', 'PostController@to');

    // Index
    $router->get('main', 'MainController@index');

    // Pages
    $router->get('pages', 'PageController@index');
    $router->post('page/store', 'PageController@store');
    $router->post('page/{id}', 'PageController@show');
    $router->post('page/{id}/edit', 'PageController@edit');
    $router->post('page/{id}/update', 'PageController@update');
    $router->post('page/{id}/delete', 'PageController@delete');

    // Districts
    $router->get('districts', 'DistrictController@index');
    $router->post('district/{page_id}/store', 'DistrictController@store');
    $router->post('district/{id}/delete', 'DistrictController@delete');

    // Scrap district
    $router->post('scrap-district', 'ScrapDistrictController@run');

    // Hijri
    $router->get('hijris', 'HijriController@index');
    $router->get('probable', 'HijriController@probableNextMonth');
    $router->post('submit-start-date', 'HijriController@saveStartMonthDate');
    $router->post('del-start-date', 'HijriController@delStartMonthDate');

    // Config
    $router->get('configs', 'ConfigController@index');
    $router->post('config', 'ConfigController@store');
    $router->post('config/{id}/delete', 'ConfigController@delete');

    // Telegram
    $router->post('telegram/hook', 'TelegramHookController@hook');
});
