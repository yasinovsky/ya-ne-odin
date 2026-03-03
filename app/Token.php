<?php namespace Yaseek\YNO\App;

use Yaseek\YNO\Core\Helper;



/**
 * Представление токена
 * @package Yaseek\YNO\App
 */
class Token {



    /**
     * Имя таблицы токенов
     */
    private const TABLE = 'tokens';



    /**
     * Создаёт новый токен
     * @param array $config Конфигурация
     * @return string
     */
    private static function _get_token_value($config) {
        static $generators = null;
        if (is_null($generators)) {
            $generators = array( // Коллекция генераторов
                'vowels' => new Helper\Token($config['vowels']),
                'consonants' => new Helper\Token($config['consonants']),
            );
        }
        $result = ''; // Сюда результат
        $syllables = $config['syllables'];
        $vowels = $generators['vowels']->get($syllables);
        $consonants = $generators['consonants']->get($syllables);
        for ($i = 0; $i < $syllables; $i++) {
            $result .= $consonants[$i] . $vowels[$i];
        }
        return $result;
    }



    /**
     * Возвращает подпись для токена
     * @param array $config Конфигурация
     * @param string $value Значение
     * @param int $expires Дата устаревания
     * @return string
     */
    private static function _get_token_signature($config, $value, $expires) {
        $digest = $config['digest'];
        $digest = str_replace( // Сделаем замены шаблонов
            array('{%VALUE%}', '{%EXPIRES%}', '{%SALT%}'),
            array($value, strval($expires), $config['salt']),
            $digest
        );
        return hash($config['algorithm'], $digest);
    }



    /**
     * Приводит и возвращает токен
     * @param mixed $value Значение
     * @return string
     */
    private static function _cast_token($value) {
        return preg_replace('/[^a-z]/i', '', strval($value));
    }



    /**
     * Проверяет существование токена
     * @param string $value Токен
     * @return bool
     * @throws \Exception
     */
    private static function _exists($value) {
        static $db = null;
        if (is_null($db)) { $db = Application::database(); }
        $statement = $db->table(self::TABLE)
            ->where('value', $value);
        return $statement->count() === 1;
    }



    /**
     * Создает и возвращает описание нового токена
     * @return array
     * @throws \Exception
     */
    public static function make() {
        $config = Application::config()->token();
        do { $value = self::_get_token_value($config); }
        while (self::_exists($value)); // Убедимся :)
        $expires = time() + $config['lifetime'];
        $signature = self::_get_token_signature(
            $config['signature'], $value, $expires
        );
        return array(
            'value' => $value,
            'signature' => $signature,
            'expires' => $expires,
        );
    }



}
