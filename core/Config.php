<?php namespace Yaseek\YNO\Core;

use Yaseek\YNO\Core\Config\Exception\DomainException;
use Yaseek\YNO\Core\Config\Exception\InvalidArgumentException;



/**
 * Конфигурация системы
 * @package Yaseek\YNO\Core
 */
class Config {



    /**
     * Файл
     * @var string
     */
    protected $_file = null;

    /**
     * Данные
     * @var array
     */
    protected $_data = array();



    /**
     * Значение по умолчанию
     */
    const DEFAULT_VALUE = null;



    /**
     * Расширения файлов типа "php ini file"
     */
    const EXTENSION_INI = 'ini';

    /**
     * Расширения файлов сериализованных данных
     */
    const EXTENSION_SERIALIZED = 'ser';

    /**
     * Расширения файлов json-данных
     */
    const EXTENSION_JSON = 'json';



    /**
     * Конструктор
     * @param string $file Путь к файлу
     * @throws Config\Exception\InvalidArgumentException
     */
    protected function __construct($file) {
        if (!file_exists($file)) { // Проверим, файл обязательно должен существовать!
            throw new InvalidArgumentException('File "' . $file . '" is not exists');
        }
        if (!is_readable($file)) { // ... и быть доступным, (пока) хотя-бы, на чтение!
            throw new InvalidArgumentException('File "' . $file . '" is not readable');
        }
        $this->_file = $file;
    }



    /**
     * Возвращает все данные
     * @return array
     */
    public function data() {
        return $this->_data;
    }



    /**
     * Возвращает объект конфиг-файла
     * @param string $file Путь к файлу
     * @throws Config\Exception\DomainException
     * @return Config
     */
    public static function getInstance($file) {
        static $instances = array();
        $hash = crc32($file); // Быстрее чем md5
        if (!array_key_exists($hash, $instances)) {
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            switch ($extension) {
                case self::EXTENSION_INI:
                    $instances[$hash] = new Config\Ini($file);
                    break;
                case self::EXTENSION_JSON:
                    $instances[$hash] = new Config\Json($file);
                    break;
                case self::EXTENSION_SERIALIZED:
                    $instances[$hash] = new Config\Serialized($file);
                    break;
                default:
                    throw new DomainException( // Непонятный файл?
                        'Invalid file extension "' . $extension . '"'
                    );
            }
        }
        return $instances[$hash];
    }



    /**
     * Является ли значение скаляром
     * @param mixed $value Значение
     * @return bool
     */
    private static function _is_scalar($value) {
        return is_string($value) || is_int($value);
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
                ? $this->_data[$name]
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
        return
            array_key_exists($name, $this->_data) &&
            array_key_exists($key, $this->_data[$name])
                ? $this->_data[$name][$key]
                : $default;
    }



    /**
     * Магическая перегрузка методов
     * @param string $name Имя метода
     * @param array $arguments Список аргументов
     * @throws Config\Exception\InvalidArgumentException
     * @return mixed
     */
    public function __call($name, $arguments) {
        $default = self::DEFAULT_VALUE;
        if (empty($arguments)) { // Продуем искать плоский ключ
            $result = $this->_get_value_plain($name, $default);
        }
        else {
            $key = $arguments[0]; // Имя ключа данных и значение по умолчанию
            if (array_key_exists(1, $arguments)) { $default = $arguments[1]; }
            if (self::_is_scalar($key) === false) { // Важно! Ключ должен быть
                throw new InvalidArgumentException( // ... строкой или числом!
                    'Key argument should be either a string or an integer'
                );
            }
            // В данном случае пробуем искать вложенное значение ...
            $result = $this->_get_value_nested($name, $key, $default);
        }
        return $result;
    }



}
