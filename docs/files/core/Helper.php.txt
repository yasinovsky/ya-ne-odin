<?php namespace Yaseek\YNO\Core;

use Ramsey\Uuid\Uuid;

use Yaseek\YNO\App\Application;
use Yaseek\YNO\Core\Helper\Request;
use Yaseek\YNO\Core\Helper\Token;



/**
 * Вспомогательные процедуры
 * @package Yaseek\YNO\Core
 */
class Helper {



    /**
     * Возвращает идентификатор uuid
     * @param null|string $string Строка
     * @param null|string $type Имя типа домена
     * @return string
     */
    public static function getUuid($string = null, $type = null) {
        static $urn = null;
        if (is_null($urn)) { // Загружаем эффективно один раз
            $urn = Application::config()->environment('urn');
        }
        $result = isset($type) // В неймспейсе формируем типа "urn:[env]:[type]:[string]"
            ? Uuid::uuid5(Uuid::NAMESPACE_DNS, 'urn:' . $urn . ':' . $type . ':' . $string)
            : Uuid::uuid4();
        return strval($result);
    }



    /**
     * Является ли строка идентификатором uuid
     * @param mixed $string Строка
     * @return bool
     */
    public static function isUuid($string) {
        static $pattern = null;
        if (is_null($pattern)) {
            $pattern = array(); // Временно к массиву
            foreach (array(8, 4, 4, 4, 12) as $length) {
                $pattern[] = '[0-9a-f]{' . $length . '}';
            }
            $pattern = '/^' . implode('-', $pattern) . '$/';
        }
        return is_string($string) && preg_match($pattern, $string);
    }



    /**
     * Проверяет идентификатор UUID
     * @param mixed $string Строка
     * @return string
     * @throws \Exception
     */
    public static function castUuid($string) {
        if (!self::isUuid($string)) {
            throw new \Exception('Value "' . strval($string) . ' is not uuid"');
        }
        return $string;
    }



    /**
     * Возвращает случайный токен
     * @param int $length Длинна токена
     * @return string
     */
    public static function getToken($length = 64) {
        static $token = null;
        if (is_null($token)) {
            $token = new Token();
        }
        return $token->get($length);
    }



    /**
     * Возвращает представление запроса
     * @return Request
     */
    public static function request() {
        static $result = null;
        if (is_null($result)) {
            $result = new Request();
        }
        return $result;
    }



    /**
     * Декодирует JSON строку, содержащую массив
     * @param string $string JSON строка
     * @return array
     * @throws \Exception
     */
    public static function jsonDecode($string) {
        $result = json_decode($string, true);
        if (!is_array($result)) { // Этого так нельзя оставлять
            throw new \Exception('Cannot decode json string');
        }
        return $result;
    }



    /**
     * Возвращает JSON-представление данных
     * @param array $value Массив
     * @param null|int $options Битовая маска значений
     * @param int $depth Максимальная глубина
     * @return string
     * @throws \Exception
     */
    public static function jsonEncode($value, $options = null, $depth = 512) {
        static $default_options = null;
        if (is_null($default_options)) {
            $default_options =
                JSON_UNESCAPED_SLASHES + // Не экранировать символ "/"
                JSON_UNESCAPED_UNICODE; // Не кодировать символы Unicode
        }
        if (!is_array($value)) { throw new \Exception('Value is not an array'); }
        return json_encode($value, isset($options) ? $options : $default_options, $depth);
    }



}
