<?php namespace Yaseek\YNO\App;

use Yaseek\YNO\Core\Helper;
use Yaseek\YNO\Core\Database;



/**
 * Представление сообщения
 * @package Yaseek\YNO\App
 */
class Message {



    /**
     * Имя таблицы сообщений
     */
    private const TABLE = 'messages';



    /**
     * Приводит текст сообщения
     * @param string $value Текст сообщения
     * @param int $length Максимальная длинна
     * @return string
     */
    private static function _cast_message($value, $length = 8192) {
        $value = mb_substr(strval($value), 0, $length);
        $value = htmlspecialchars(strip_tags($value));
        return trim($value);
    }



    /**
     * Добавляет новое сообшение
     * @param array $request Запрос
     * @return bool
     * @throws \Exception
     */
    public static function insert($request) {
        $db = Application::database();
        $message = self::_cast_message($request['message']);
        if ($message === '') { // На всякий случай проверим текст
            throw new \Exception('Empty message given');
        }
        try {
            $db->beginTransaction();
            $token = Token::insert(
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



}
