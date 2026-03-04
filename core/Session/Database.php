<?php namespace Yaseek\YNO\Core\Session;

use Yaseek\YNO\Core\Helper;
use Yaseek\YNO\App\Application;



/**
 * Обработчик пользовательских сессий в БД
 * @package Yaseek\YNO\Core\Session
 */
class Database implements \SessionHandlerInterface {



    /**
     * @var \PDO
     */
    private $_db = null;

    /**
     * Дата устаревания сессии
     * @var int
     */
    private $_expires = null;



    /**
     * Имя таблицы с данными сессий
     */
    const TABLE = 'sessions';



    /**
     * Конструктор
     * @throws \Exception
     */
    private function __construct() {
        // Сырой PDO здесь исключительно для скорости
        $this->_db = Application::database()->getPdo();
    }



    /**
     * Возвращает обработчик сессий в БД
     * @return Database
     */
    public static function getInstance() {
        static $instance = null;
        if (is_null($instance)) {
            $instance = new Database();
        }
        return $instance;
    }



    /**
     * Задаёт дату устаревания сессии
     * @param int $time Дата-время
     */
    public function expires($time) {
        $this->_expires = $time;
    }



    /**
     * Возвращает данные сессии
     * @param string $token Идентификатор сессии
     * @return string|null
     */
    protected function _fetch_data($token) {
        static $statement = null;
        if (is_null($statement)) {
            $statement = $this->_db->prepare(
                'SELECT data FROM ' . self::TABLE
                . ' WHERE token = :token AND expires >= :time'
                . ' LIMIT 1'
            );
        }
        $statement->execute(array(
            ':token' => $token, ':time' => time()
        ));
        $sessions = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return empty($sessions) ? null : $sessions[0]['data'];
    }



    /**
     * Создает идентификатор сессии
     * @return string
     */
    public function create_sid() {
        return Helper::getToken();
    }



    /**
     * Проверяет, существует ли сессия
     * @param string $token Идентификатор сессии
     * @return bool
     */
    public function validate_sid($token) {
        $statement = $this->_db->prepare(
            'SELECT token FROM ' . self::TABLE
            . ' WHERE token = :token AND expires >= :time'
            . ' LIMIT 1'
        );
        $statement->execute(array(
            ':token' => $token, ':time' => time()
        ));
        return $statement->rowCount() == 1;
    }



    /**
     * Выполняется при открытии сессии
     * @param $path
     * @param $name
     * @return bool
     */
    public function open($path, $name) {
        return true;
    }



    /**
     * Выполняется после того, как была вызвана callback write
     * @return bool
     */
    public function close() {
        return true;
    }



    /**
     * Читает данные сессии
     * @param string $token Идентификатор сессии
     * @return string
     */
    public function read($token) {
        $data = $this->_fetch_data($token);
        return isset($data) ? $data : '';
    }



    /**
     * Записывает данные сессии
     * @param string $token Идентификатор сессии
     * @param string $data Данные
     * @return bool
     */
    public function write($token, $data) {
        $record = array(
            ':token' => $token, ':data' => $data,
            ':expires' => $this->_expires,
        );
        if ($this->validate_sid($token)) {
            $statement = $this->_db->prepare(
                'UPDATE ' . self::TABLE // Запись есть
                . ' SET data = :data, expires = :expires WHERE token = :token'
            );
        }
        else {
            $statement = $this->_db->prepare(
                'INSERT INTO ' . self::TABLE // Записи нет - добавляем
                . ' (token, data, expires) VALUES (:token, :data, :expires)'
            );
        }
        $statement->execute($record);
        return true;
    }



    /**
     * Уничтожает сессию
     * @param string $token Идентификатор сессии
     * @return bool
     */
    public function destroy($token) {
        $statement = $this->_db->prepare(
            'DELETE FROM ' . self::TABLE . ' WHERE token = :token'
        );
        $statement->execute(array(':token' => $token));
        return true;
    }



    /**
     * Сборщик мусора
     * @param int $lifetime Максимальное время жизни сессии
     * @return bool
     */
    public function gc($lifetime) {
        $statement = $this->_db->prepare(
            'DELETE FROM ' . self::TABLE . ' WHERE expires < :time'
        );
        $statement->execute(array(':time' => time()));
        return true;
    }



}
