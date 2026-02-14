<?php namespace Yaseek\YNO;



$root = dirname(__DIR__); // Корень
require($root . '/app/application.php');
$app = App\Application::getInstance($root);

$router = App\Application::router();

// Включаем роуты нашего приложения
require($root . '/app/routes.php');



$router->onHttpError(function($code, \Klein\Klein $router) use ($app) {
    $message = null;
    switch ($code) {
        case 404:
            $message =
                'Страница, на которую вы хотели перейти, не найдена. ' .
                'Возможно, введен некорректный адрес или страница была удалена.';
            break;
        case 405:
            $message =
                'К сожалению, вы не можете этого сделать.';
            break;
        case 500:
            $message =
                'К сожалению, произошла ошибка. ' .
                'Мы уже знаем о ней и работаем над решением проблемы. ' .
                'Пожалуйста, попробуйте зайти на эту страницу позже.';
            break;
    }
    $router->response()->body(
        $router->app()->twig->render(
            '/pages/error.twig', array(
                'code' => $code, 'message' => $message,
                'application' => $app,
            )
        )
    );
    return null;
});



$router->onError(function(\Klein\Klein $router, $message, $class, $exception) use ($app) {
    App\Application::logger()->error(
        $message,
        array(array(
            'get' => $router->request()->paramsGet()->all(),
            'post' => $router->request()->paramsPost()->all(),
            'server' => $router->request()->server()->all(),
        ))
    );
    /** @var \Exception $exception */
    switch ($exception->getCode()) {
        case 403:
            $code = 403;
            $message =
                'Страница, на которую вы хотели перейти, недоступна. ' .
                'Возможно, у вашего пользователя недостаточно полномочий.';
            break;
        case 404:
            $code = 404;
            $message =
                'Страница, на которую вы хотели перейти, не найдена. ' .
                'Возможно, введен некорректный адрес или страница была удалена.';
            break;
        default:
            $code = 500;
            $message =
                'К сожалению, произошла ошибка. ' .
                'Мы уже знаем о ней и работаем над решением проблемы. ' .
                'Пожалуйста, попробуйте зайти на эту страницу позже.';
            break;
    }
    $router->response()->code($code);
    $router->response()->body(
        $router->app()->twig->render(
            '/pages/error.twig', array(
                'code' => $code, 'message' => $message,
                'application' => $app,
            )
        )
    );
});



$router->dispatch();
