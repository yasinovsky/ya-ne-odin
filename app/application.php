<?php namespace Yaseek\YNO\App;

use Yaseek\YNO\Core\Database;
use Yaseek\YNO\Core\Logger;
use Yaseek\YNO\Core\Helper;
use Yaseek\YNO\Core\Twig;

use Klein\Klein as Route;



/**
 * Основной класс приложения
 * @package Yaseek\YNO\App
 */
class Application {



    /**
     * Путь к корню приложения
     * @var string
     */
    private static $_root = null;



    /**
     * Конструктор
     */
    private function __construct() {
        date_default_timezone_set('Europe/Moscow');
        $root = self::$_root; // Корневая директория
        spl_autoload_register(function($name) use ($root) {
            $path = explode('\\', $name);
            $namespace = array_shift($path);
            switch ($namespace) {
                case 'Yaseek':
                    // @todo Что-то тут не очень "красиво" ...
                    if ($path[0] == 'YNO') { array_shift($path); }
                    $scope = strtolower(array_shift($path));
                    $file = $root . DIRECTORY_SEPARATOR . $scope . DIRECTORY_SEPARATOR
                        . implode(DIRECTORY_SEPARATOR, $path) . '.php';
                    break;
                default:
                    $file = null;
                    break;
            }
            if (isset($file) && file_exists($file)) {
                require($file); // Включаем файл класса
            }
        });
        require_once( // Загрузчик пакетов composer
            $root . DIRECTORY_SEPARATOR . 'vendor'
            . DIRECTORY_SEPARATOR . 'autoload.php'
        );
    }



    /**
     * Возвращает экземпляр приложения
     * @param string $root Путь к корню приложения
     * @return Application
     * @throws \Exception
     */
    public static function getInstance($root = null) {
        static $instance = null;
        if (is_null($instance)) {
            if (!isset($root)) { // При первом создании нужен корень
                throw new \Exception('Root folder must be defined');
            }
            self::$_root = $root; // Корень
            $instance = new Application();
        }
        return $instance;
    }



    /**
     * Возвращает конфигурацию приложения
     * @return Config
     * @throws \Exception
     */
    public static function config() {
        static $result = null;
        if (is_null($result)) {
            try {
                // Загрузим конфигурацию всего приложения из лежащего уровнем выше json-файла,
                $file = self::$_root . DIRECTORY_SEPARATOR . pathinfo(__FILE__, PATHINFO_FILENAME)
                    . '.' . Config::EXTENSION_JSON; // ... совпадающего по имени с этим исполняемым
                $result = new Config($file); // Стартуем новую конфигурацию
            }
            catch (\Exception $e) {
                self::logger()->error($e->getMessage());
                throw new \Exception($e->getMessage());
            }
        }
        return $result;
    }



    /**
     * Возвращает роутер
     * @param bool $twig Стартовать Twig
     * @return Route
     */
    public static function router($twig = true) {
        static $result = null;
        if (is_null($result)) {
            $result = new Route();
            // Валидатор строк uuid, реализованный на сервисе роутера
            $result->service()->addValidator('uuid', function($string) {
                return Helper::isUuid($string);
            });
            if ($twig) {
                $result->app()->register('twig', function() {
                    $root = self::$_root; // Верхний уровень приложения
                    $loader = new \Twig_Loader_Filesystem($root . '/app/Views');
                    // $twig = new \Twig_Environment($loader,
                    $twig = new Twig($loader, // Своё, не устраивают права файлов
                        self::config()->environment('production')
                            ? array('cache' => $root . '/temp/templates')
                            : array()
                    );
                    return $twig;
                });
            }
        }
        return $result;
    }



    /**
     * Соединяется с базой данных
     * @param string $alias Алиас базы данных
     * @return \Illuminate\Database\Connection
     * @throws \Exception
     */
    private static function _get_db_connection($alias) {
        $config = self::config();
        try {
            // Получаем объект работы с СУБД \Illuminate\Database\Connection
            $connection = Database::getConnection($config->database($alias));
        }
        catch (\Exception $e) {
            self::logger()->error($e->getMessage());
            throw $e; // Дальше нет никакого смысла
        }
        // На боевых серверах нам не нужен лог запросов - экономим время и память
        if ($config->environment('production')) { $connection->disableQueryLog(); }
        return $connection;
    }



    /**
     * Возвращает объект для работы с БД
     * @param bool $check Проверять соединение
     * @param string $alias Алиас базы данных
     * @return \Illuminate\Database\Connection
     * @throws \Exception
     */
    public static function database($check = false, $alias = 'master') {
        static $result = array();
        if (!array_key_exists($alias, $result)) {
            /** @var \Illuminate\Database\Connection[] $result */
            $result[$alias] = self::_get_db_connection($alias);
        }
        if ($check) {
            $attempts = 0; // Номер текущей попытки
            $success = false; // Был ли достигнут успех?
            while ($attempts++ < 4) {
                try {
                    if (!array_key_exists($alias, $result)) { // Проверим ключ
                        throw new \Exception('Key "' . $alias . '" not exists');
                    }
                    $result[$alias]->getPdo()->query('select 1');
                    $success = true; // Запрос был успешно выполнен
                    break; // Прерываемся - соединение еще живое!
                }
                catch (\Exception $e) {
                    sleep($attempts * 2); unset($result[$alias]);
                    try { $result[$alias] = self::_get_db_connection($alias); }
                    catch (\Exception $e) { continue; } // На следующий виток!
                }
            }
            if ($success === false) {
                throw new \Exception( // Проверим наличие ошибки
                    isset($e) && ($e instanceof \Exception)
                        ? $e->getMessage() : 'Cannot connect to database'
                );
            }
        }
        return $result[$alias];
    }



    /**
     * Возвращает номер версии приложения
     * @return string
     */
    public static function version() {
        $value = '0.0.1';
        return $value;
    }



    /**
     * Возвращает логгер
     * @return Logger
     */
    public static function logger() {
        static $result = null;
        if (is_null($result)) {
            $result = new Logger(self::$_root);
        }
        return $result;
    }



}
