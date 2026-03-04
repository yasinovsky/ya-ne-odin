<?php namespace Yaseek\YNO\Core\Helper;



/**
 * Генератор случайных токенов
 * @package Yaseek\YNO\Core\Helper
 */
class Token {



    /**
     * Алфавит
     * @var string
     */
    private $_alphabet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * Длинна алфавита
     * @var int
     */
    private $_alphabet_length = null;



    /**
     * Конструктор
     * @param string|null $alphabet Алфавит
     */
    public function __construct($alphabet = null) {
        if (isset($alphabet)) { $this->_alphabet = $alphabet; }
        $this->_alphabet_length = strlen($this->_alphabet);
    }



    /**
     * Возвращает случайное число
     * @param int $min Минимальное значение
     * @param int $max Максимальное значение
     * @return int
     */
    private static function _rand_secure($min, $max) {
        $range = $max - $min;
        $log = ceil(log($range, 2));
        $bytes = intval(($log / 8) + 1); // Длинна в байтах
        $bits = intval($log + 1); // Получаем длинну в битах
        $filter = intval((1 << $bits) - 1); // Все нижние биты к 1
        do {
            $random = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $random = $random & $filter; // Отбрасываем нерелевантные биты
        }
        while ($random >= $range);
        return $min + $random;
    }



    /**
     * Возвращает токен
     * @param int $length Длинна токена
     * @return string
     */
    public function get($length) {
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $this->_alphabet[
                self::_rand_secure(0, $this->_alphabet_length)
            ];
        }
        return $result;
    }



}
