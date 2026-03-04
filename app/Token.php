<?php namespace Yaseek\YNO\App;

use Yaseek\YNO\Core\Helper;
use Yaseek\YNO\Core\Database;



/**
 * Представление токена
 * @package Yaseek\YNO\App
 */
class Token {



    /**
     * Идентификатор
     * @var int
     */
    private $_id = null;

    /**
     * Идентификатор uuid
     * @var string
     */
    private $_uuid = null;

    /**
     * Значение
     * @var string
     */
    private $_value = null;



    /**
     * Имя таблицы токенов
     */
    private const TABLE = 'tokens';



    /**
     * Конструктор
     * @param array $record Описание записи
     */
    private function __construct($record) {
        $this->_id = $record['id'];
        $this->_uuid = $record['uuid'];
        $this->_value = $record['value'];
    }



    /**
     * Возвращает представление токена
     * @param string $identifier Значение токена или uuid
     * @return Token
     * @throws \Exception
     */
    public static function getInstance($identifier) {
        $db = Application::database();
        $statement = $db->table(self::TABLE)
            ->select('id', 'uuid', 'value')
                ->where('active', 1);
        switch (true) {
            case Helper::isUuid($identifier):
                $statement->where('uuid', $identifier);
                break;
            default:
                $identifier = Database::escape($db, $identifier);
                $statement->where('value', $identifier);
                break;
        }
        if ($statement->count() !== 1) { // Единственный?
            throw new \Exception('Token not exists');
        }
        return new Token($statement->first());
    }



    /**
     * Возвращает идентификатор
     * @return int
     */
    public function id() {
        return $this->_id;
    }



    /**
     * Возвращает идентификатор uuid
     * @return string
     */
    public function uuid() {
        return $this->_uuid;
    }



    /**
     * Возвращает значение
     * @return string
     */
    public function value() {
        return $this->_value;
    }



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
            array(strval($value), strval($expires), $config['salt']),
            $digest
        );
        return hash($config['algorithm'], $digest);
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
            'token' => $value,
            'expires' => $expires,
            'signature' => $signature,
        );
    }



    /**
     * Добавляет новый токен и возвращает его представление
     * @param string $title Заголовок
     * @param string $value Значение
     * @param int $expires Дата устаревания
     * @param string $signature Подпись
     * @return Token
     * @throws \Exception
     */
    public static function insert($title, $value, $expires, $signature) {
        $time = time(); // Единожды получим время
        $config = Application::config()->token();
        $calculated = self::_get_token_signature(
            $config['signature'], $value, $expires
        );
        if ($calculated !== $signature) { // Неверная подпись
            throw new \Exception('Invalid token signature');
        }
        if ($expires <= $time) { // Токен уже устарел
            throw new \Exception('Token signature expired');
        }
        if (self::_exists($value)) { // Токен уже существует
            throw new \Exception('Token already exists');
        }
        $db = Application::database();
        $id = Database::insertUniqueRow(
            $db, self::TABLE, 'uuid',
            function() { return Helper::getUuid(); },
            array( // Необходимое и достаточное
                'value' => $value, 'active' => 1,
                'created' => $time, 'title' => $title,
            ),
            true
        );
        $statement = $db->table(self::TABLE)->where('id', $id);
        return self::getInstance($statement->value('uuid'));
    }



}
