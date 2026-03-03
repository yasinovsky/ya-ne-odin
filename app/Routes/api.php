<?php namespace Yaseek\YNO\App;

use Klein\Request as KRequest;
use Klein\Response as KResponse;



/**
 * Роутинг для api приложения
 * @package Yaseek\YNO\App
 */

$router = Application::router();

$router->with('/api', function() use ($router) {

    $router->respond(
        array('POST'), '/token',
        function(KRequest $request, KResponse $response) {
            $api = new Api($request, $response);
            $api->process(function() use ($api, $request) {
                return Token::make();
            });
        }
    );

});
