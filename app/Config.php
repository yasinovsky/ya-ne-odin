<?php namespace Yaseek\YNO\App;

use Yaseek\YNO\Core\Config as CoreConfig;



/**
 * Конфигурация приложения
 * @method array environment($key = null)
 * @package Yaseek\YNO\App
 */
class Config extends CoreConfig {



    /**
     * Данные
     * @var array
     */
    protected $_data = array();



    /**
     * Слой конфигурации
     * @var string
     */
    private $_layer = null;



    /**
     * Конструктор
     * @param string $file
     * @throws CoreConfig\Exception\DomainException
     */
    public function __construct($file) {
        $this->_data = CoreConfig::getInstance($file)->data();
    }



    /**
     * Задаёт слой конфигурации
     * @param string $value Алиас
     */
    public function layer($value) {
        $this->_layer = $value;
    }



    /**
     * Возвращает плоское значение
     * @param string $name Имя ключа
     * @param mixed $default Значение по-умолчанию
     * @return mixed
     */
    protected function _get_value_plain($name, $default) {
        return
            array_key_exists($name, $this->_data)
                ? (array_key_exists($this->_layer, $this->_data[$name])
                    ? $this->_data[$name][$this->_layer]
                    : $this->_data[$name]
                )
                : $default;
    }



    /**
     * Возвращает вложенное значение
     * @param string $name Имя ключа
     * @param string $key Имя вложенного ключа
     * @param mixed $default Значение по-умолчанию
     * @return mixed
     */
    protected function _get_value_nested($name, $key, $default) {
        $result = $default; // Результат пока пуст
        if (array_key_exists($name, $this->_data)) {
            switch (true) {
                case
                    array_key_exists($this->_layer, $this->_data[$name]) &&
                    array_key_exists($key, $this->_data[$name][$this->_layer]):
                        $result = $this->_data[$name][$this->_layer][$key];
                        break;
                case
                    array_key_exists($key, $this->_data[$name]):
                        $result = $this->_data[$name][$key];
                        break;
            }
        }
        return $result;
    }



}
