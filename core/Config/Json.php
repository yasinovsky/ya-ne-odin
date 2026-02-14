<?php namespace Yaseek\YNO\Core\Config;

use Yaseek\YNO\Core\Config;
use Yaseek\YNO\Core\Config\Exception\ParseException;



/**
 * Конфиг, представленный файлом JSON
 * @package Yaseek\YNO\Core\Config
 */
class Json extends Config {



    /**
     * Конструктор
     * @param string $file Путь к файлу
     * @throws Exception\ParseException
     */
    protected function __construct($file) {
        parent::__construct($file); // Проверимся!
        $this->_data = json_decode(file_get_contents($this->_file), true);
        if (is_null($this->_data)) { // Не получилось распарсить файл?
            throw new ParseException('Cannot parse file "' . $this->_file . '"');
        }
    }



}
