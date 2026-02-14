<?php namespace Yaseek\YNO\Core\Config;

use Yaseek\YNO\Core\Config;
use Yaseek\YNO\Core\Config\Exception\ParseException;



/**
 * Конфиг, представленный файлом сериализованных данных
 * @package Yaseek\YNO\Core\Config
 */
class Serialized extends Config {



    /**
     * Конструктор
     * @param string $file Путь к файлу
     * @throws Exception\ParseException
     */
    protected function __construct($file) {
        parent::__construct($file); // Проверимся!
        // Подавляем тут E_NOTICE, а ниже уже нормально проверим ...
        $this->_data = @unserialize(file_get_contents($this->_file));
        if ($this->_data === false) { // Не получилось распарсить файл?
            throw new ParseException('Cannot parse file "' . $this->_file . '"');
        }
    }



}
