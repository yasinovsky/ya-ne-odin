<?php namespace Yaseek\YNO\App;

use Klein\App as KApp;
use Klein\Request as KRequest;
use Klein\Response as KResponse;
use Klein\ServiceProvider as KService;

use Yaseek\YNO\Core\Helper;



/**
 * Роутинг для бекофиса
 * @package Yaseek\YNO\App
 */

$router = Application::router();

$router->with('/backoffice', function() use ($router) {

    $router->respond(
        array('GET'), '',
        function(KRequest $request, KResponse $response, KService $service, KApp $app) {
            $actor = Application::actor();
            if ($actor->authenticated() === false) {
                // Сохраним урл возврата в сесси пользователя
                $actor->session()->set('return', $request->uri());
                return $response->redirect('/signin');
            }
            return $app->twig->render(
                '/pages/backoffice.twig', array(
                    'threads' => Token::threads(),
                    'application' => Application::getInstance(),
                )
            );
        }
    );

    $router->respond(
        array('GET'), '/thread/[s:uuid]',
        function(KRequest $request, KResponse $response, KService $service, KApp $app) {
            $actor = Application::actor();
            if ($actor->authenticated() === false) { // Тут проще
                throw new \Exception('Forbidden', 403);
            }
            $identifier = Helper::castUuid($request->uuid);
            return $app->twig->render(
                '/pages/backoffice/thread.twig', array(
                    'thread' => Token::getInstance($identifier),
                    'application' => Application::getInstance(),
                )
            );
        }
    );

});
