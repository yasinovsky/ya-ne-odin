<?php namespace Yaseek\YNO\Core;

use Illuminate\Database\Capsule\Manager as Capsule;



/**
 * Представление базы данных
 * @package Yaseek\YNO\Core
 */
class Database {



    /**
     * @param array $params Параметры соединения
     * @return \Illuminate\Database\Connection
     */
    public static function getConnection($params) {
        $capsule = new Capsule();
        $capsule->addConnection($params);
        $capsule->setFetchMode(\PDO::FETCH_ASSOC);
        return $capsule->getConnection();
    }



    /**
     * Экранирует текстовую строку
     * @param \Illuminate\Database\Connection $db Объект для работы с БД
     * @param string $text Текст
     * @return string
     */
    public static function escape($db, $text) {
        static $pdo = null;
        if (is_null($pdo)) { $pdo = $db->getPdo(); }
        $text = $pdo->quote(strval($text));
        // И вернём без ограничивающих кавычек
        return mb_substr($text, 1, -1, 'utf-8');
    }



    /**
     * Добавляет строку с уникальным ключом в таблицу
     * @param \Illuminate\Database\Connection $db Объект для работы с БД
     * @param string $table Имя таблицы
     * @param string $field Имя колонки
     * @param callable $callback Генератор
     * @param array $row Данные строка
     * @param bool $return_id Возвращать номер строки, если есть ai-колонка
     * @return int|mixed
     */
    public static function insertUniqueRow($db, $table, $field, $callback, $row = array(), $return_id = true) {
        $result = null;
        $statement = $db->table($table);
        do {
            try {
                $unique = $callback(); // Получаем "уникальность"
                if (intval($statement->where($field, $unique)->count()) === 0) {
                    $row[$field] = $unique; // Меняем или задаем "уникальность"
                    if ($return_id) { $result = $statement->insertGetId($row); }
                    else { $statement->insert($row); $result = $unique; }
                }
            }
            catch (\Exception $e) { $result = null; }
        }
        while (is_null($result));
        return $result;
    }



    /**
     * Обходит условие и мызывает callback функцию на каждой записи
     * @param \Illuminate\Database\Connection $db Объект для работы с БД
     * @param \Illuminate\Database\Query\Builder $query
     * @param callable $callback Обработчик
     */
    public static function statementTraverse($db, $query, $callback) {
        $statement = $db->getPdo()->prepare($query->toSql());
        $statement->execute($query->getBindings());
        while ($record = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $callback($record);
        }
    }



}
