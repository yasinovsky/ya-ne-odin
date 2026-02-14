<?php namespace Yaseek\YNO\Core\Logger;



/**
 * Представление контекста для логгера
 * @package Yaseek\YNO\Core\Logger
 */
class Context {



    /**
     * Текст сообщения
     * @var string
     */
    private $_message = null;

    /**
     * Контекст
     * @var array
     */
    private $_context = array();



    /**
     * Конструтор
     * @param array $arguments Аргументы функции
     */
    public function __construct($arguments) {
        if (array_key_exists(0, $arguments) && is_string($arguments[0])) {
            $this->_message = trim($arguments[0]); // Сохраним сообщение
        }
        if (array_key_exists(1, $arguments) && is_array($arguments[1])) {
            foreach ($arguments[1] as $param) { // Разберемся с контекстом
                $this->_context[] = $param;
            }
        }
    }



    /**
     * Возвращает сообщение
     * @return string
     */
    public function message() {
        return $this->_message;
    }



    /**
     * Возвращает контекст
     * @return array
     */
    public function context() {
        return $this->_context;
    }



}
