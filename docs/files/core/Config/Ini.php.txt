<?php namespace Yaseek\YNO\Core\Config;

use Yaseek\YNO\Core\Config;
use Yaseek\YNO\Core\Config\Exception\ParseException;



/**
 * Конфиг, представленный файлом "php ini"
 * @package Yaseek\YNO\Core\Config
 */
class Ini extends Config {



    /**
     * Конструктор
     * @param string $file Путь к файлу
     * @throws Exception\ParseException
     */
    protected function __construct($file) {
        parent::__construct($file); // Проверимся!
        $this->_data = parse_ini_file($this->_file, true);
        if ($this->_data === false) { // Не получилось распарсить файл?
            throw new ParseException('Cannot parse file "' . $this->_file . '"');
        }
    }



}
