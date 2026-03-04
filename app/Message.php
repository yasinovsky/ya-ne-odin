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



}
