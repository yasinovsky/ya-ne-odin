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
