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

    $router->with('/message', function() use ($router) {

        $router->respond(
            array('POST'), '/post',
            function(KRequest $request, KResponse $response) {
                $api = new Api($request, $response);
                $api->process(function() use ($api, $request) {
                    return Message::post($api->getRequestParams(array(
                        'token' => Api::TYPE_STRING,
                        'expires' => Api::TYPE_INTEGER,
                        'signature' => Api::TYPE_STRING,
                        'title' => Api::TYPE_STRING,
                        'message' => Api::TYPE_STRING,
                    )));
                });
            }
        );

        $router->respond(
            array('POST'), '/insert',
            function(KRequest $request, KResponse $response) {
                $api = new Api($request, $response);
                $api->process(function() use ($api, $request) {
                    return Message::insert($api->getRequestParams(array(
                        'thread' => Api::TYPE_UUID,
                        'message' => Api::TYPE_STRING,
                    )));
                });
            }
        );

    });

    $router->respond(
        array('POST'), '/conversation',
        function(KRequest $request, KResponse $response) {
            $api = new Api($request, $response);
            $api->process(function() use ($api, $request) {
                $params = $api->getRequestParams(array(
                    'thread' => Api::TYPE_STRING,
                ));
                $token = Token::getInstance($params['thread']);
                return $token->conversation(true);
            });
        }
    );

    $router->respond(
        array('POST'), '/signin',
        function(KRequest $request, KResponse $response) {
            $api = new Api($request, $response);
            $api->process(function() use ($api, $request) {
                $params = $api->getRequestParams(array(
                    'login' => Api::TYPE_STRING,
                    'password' => Api::TYPE_STRING,
                ));
                $actor = Application::actor(); // Достаем пользователя
                if ($actor->signIn($params['login'], $params['password'])) {
                    $session = $actor->session(); // Это просто шорткат
                    $location = $session->get('return');
                    $session->delete('return'); // Всё
                    return isset($location) ? $location : '/';
                }
                throw new \Exception('Invalid login and/or password');
            });
        }
    );

});
