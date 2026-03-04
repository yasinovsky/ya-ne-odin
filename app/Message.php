<?php namespace Yaseek\YNO\App;

use Yaseek\YNO\Core\Helper;
use Yaseek\YNO\Core\Database;



/**
 * Представление сообщения
 * @package Yaseek\YNO\App
 */
class Message {



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
     * Дата создания
     * @var int
     */
    private $_created = null;

    /**
     * Пользователь
     * @var Actor|null
     */
    private $_actor = null;



    /**
     * Текст
     * @var string
     */
    private $_value = null;



    /**
     * Имя таблицы сообщений
     */
    private const TABLE = 'messages';



    /**
     * Конструктор
     * @param array $record Описание записи
     */
    private function __construct($record) {
        $this->_id = $record['id'];
        $this->_uuid = $record['uuid'];
        $this->_created = $record['created'];
        $this->_value = $record['value'];
        if (isset($record['actor_id'])) { // Пользователь
            $this->_actor = new Actor\Phantom($record['actor_id']);
        }
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
     * Возвращает дату создания
     * @return int
     */
    public function created() {
        return $this->_created;
    }



    /**
     * Возвращает текст
     * @return string
     */
    public function value() {
        return $this->_value;
    }



    /**
     * Возвращает пользователя
     * @return Actor|null
     */
    public function actor() {
        return $this->_actor;
    }



    /**
     * Возвращает
     * @param Token $thread
     * @return Message[]
     * @throws \Exception
     */
    public static function getInstances($thread) {
        $result = array();
        $db = Application::database();
        $statement = $db->table(self::TABLE)
            ->select('id', 'uuid', 'value', 'created', 'actor_id')
                ->where('token_id', $thread->id())
                ->orderBy('created', 'asc');
        foreach ($statement->get() as $record) {
            $result[] = new Message($record);
        }
        return $result;
    }



    /**
     * Приводит текст сообщения
     * @param string $value Текст сообщения
     * @param int $length Максимальная длинна
     * @return string|null
     */
    private static function _cast_text($value, $length) {
        $value = mb_substr(strval($value), 0, $length);
        $value = trim(htmlspecialchars(strip_tags($value)));
        return $value === '' ? null : $value;
    }



    /**
     * Добавляет новый токен и сообшение
     * @param array $request Запрос
     * @return bool
     * @throws \Exception
     */
    public static function post($request) {
        $db = Application::database();
        if (!$title = self::_cast_text($request['title'], 255)) {
            throw new \Exception('Empty title given');
        }
        if (!$message = self::_cast_text($request['message'], 8192)) {
            throw new \Exception('Empty message given');
        }
        try {
            $db->beginTransaction();
            $token = Token::insert(
                $title, // Заголовок нашего сообщения
                $request['token'], $request['expires'],
                $request['signature'], // + Подпись!
            );
            $id = Database::insertUniqueRow(
                $db, self::TABLE, 'uuid',
                function() { return Helper::getUuid(); },
                array( // Необходимое и достаточное
                    'token_id' => $token->id(),
                    'value' => $message, 'created' => time(),
                ),
                true
            );
            $db->commit();
            return true;
        }
        catch (\Exception $e) {
            $db->rollBack(); // Откатимся
            throw $e; // Выбросим дальше
        }
    }



    public static function insert($request) {
        $db = Application::database();
        if (!$message = self::_cast_text($request['message'], 8192)) {
            throw new \Exception('Empty message given');
        }
        $actor = Application::actor();
        $token = Token::getInstance($request['thread']);
        $id = Database::insertUniqueRow(
            $db, self::TABLE, 'uuid',
            function() { return Helper::getUuid(); },
            array( // Необходимое и достаточное
                'token_id' => $token->id(),
                'value' => $message, 'created' => time(),
                'actor_id' => $actor->authenticated()
                    ? $actor->id() : null // Пользователь
            ),
            true
        );
        return true;
    }



}
