<?php namespace Yaseek\YNO\App;

use Yaseek\YNO\Core\Logger;



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
