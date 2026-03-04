<?php namespace Yaseek\YNO\App\Actor;



/**
 * Представление сессии акёра
 * @package Yaseek\YNO\App\Actor
 */
class Session extends \Yaseek\YNO\Core\Session {



    /**
     * Возвращает экземпляр сессии
     * @return Session
     */
    public static function getInstance() {
        static $instance = null;
        if (is_null($instance)) {
            $instance = new Session();
        }
        return $instance;
    }



}
