<?php namespace Yaseek\YNO\App;

use Klein\App as KApp;
use Klein\Request as KRequest;
use Klein\Response as KResponse;
use Klein\ServiceProvider as KService;



/**
 * Роутинг для приложения
 * @package Yaseek\YNO\App
 */

$router = Application::router();

$router->respond(
    array('GET'), '/',
    function(KRequest $request, KResponse $response, KService $service, KApp $app) use ($router) {
        return $app->twig->render(
            '/pages/main.twig', array(
                'application' => Application::getInstance(),
            )
        );
    }
);

$router->respond(
    array('GET'), '/signin',
    function(KRequest $request, KResponse $response, KService $service, KApp $app) use ($router) {
        return $app->twig->render(
            '/pages/signin.twig', array(
                'application' => Application::getInstance(),
            )
        );
    }
);



$router->respond(
    array('GET'), '/version',
    function(KRequest $request, KResponse $response, KService $service, KApp $app) use ($router) {
        return Application::version();
    }
);
