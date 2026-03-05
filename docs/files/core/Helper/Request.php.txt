<?php namespace Yaseek\YNO\Core\Helper;



/**
 * Представление запроса
 * @package Yaseek\YNO\Core\Helper
 */
class Request {



    /**
     * Информация о сервере и среде исполнения
     * @var array
     */
    private $_server = array();



    /**
     * Конструктор
     */
    public function __construct() {
        $this->_server = $_SERVER;
    }



    /**
     * Возвращает имя запрошенного хоста
     * @return string|null
     */
    public function hostname() {
        return array_key_exists('HTTP_HOST', $this->_server)
            ? $this->_server['HTTP_HOST'] : null;
    }



    /**
     * Возвращает IP-адрес клиента
     * @return string|null
     */
    public function remoteAddress() {
        $result = null;
        foreach(array('HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $this->_server)) { $result = $this->_server[$key]; break; }
        }
        return $result;
    }



    /**
     * Соответствует ли IP-адрес клиента сети
     * @param string $network Сеть (192.168.1.1/16)
     * @link https://stackoverflow.com/questions/594112
     * @return bool
     */
    public function remoteMatch($network) {
        $remote = $this->remoteAddress();
        list ($subnet, $bits) = explode('/', $network);
        if ($bits === null) { $bits = 32; } // По умолчанию
        $remote = ip2long($remote); $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits); $subnet &= $mask;
        return ($remote & $mask) == $subnet;
    }



    /**
     * Возвращает строку с именем User-agent
     * @return string|null
     */
    public function userAgent() {
        return array_key_exists('HTTP_USER_AGENT', $this->_server)
            ? $this->_server['HTTP_USER_AGENT'] : null;
    }



}
