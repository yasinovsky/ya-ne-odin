<?php namespace Yaseek\YNO\App;

use Yaseek\YNO\App\Actor\Session;



/**
 * Представление акёра
 * @package Yaseek\YNO\App
 */
class Actor {



    /**
     * Идентификатор актёра
     * @var null|int
     */
    protected $_id = null;

    /**
     * UUID актёра
     * @var null|string
     */
    protected $_uuid = null;

    /**
     * Имя акёра
     * @var null|string
     */
    protected $_name = null;



    /**
     * Объект сессии пользователя
     * @var Session
     */
    private $_session = null;



    /**
     * Имя таблицы с актёрами
     */
    protected const TABLE = 'actors';



    /**
     * Имя ключа в сессии пользователя
     */
    private const KEY_SESSION = 'actor';



    /**
     * Конструктор
     * @param array|null $record Описание
     */
    private function __construct($record = null) {
        $this->_session = Session::getInstance();
        if (is_array($record)) { // Конструируем
            $this->_sign_in($record, false);
        }
    }



    /**
     * Задаёт атрибуты (id, uuid) пользователя
     * @param array $record Описание актёра
     * @param bool $session Обновить сессию
     */
    protected function _sign_in($record, $session) {
        $this->_id = intval($record['id']);
        $this->_uuid = $record['uuid'];
        $this->_name = $record['name'];
        if ($session) {
            $this->_session->set(
                self::KEY_SESSION, array(
                    'id' => $this->_id,
                    'uuid' => $this->_uuid,
                    'name' => $this->_name,
                )
            );
        }
    }



    /**
     * Приводит строку логина
     * @param string $value Значение
     * @param string $pattern Резулярное выражение
     * @return string
     */
    private static function _cast_login($value, $pattern = '/[^a-z0-9]/i') {
        return preg_replace($pattern, '', strval($value));
    }



    /**
     * Авторизует актёра
     * @param string $login Логин
     * @param string $password Пароль
     * @return bool
     * @throws \Exception
     */
    public function signIn($login, $password) {
        $identifier = self::_cast_login($login);
        if ($identifier === $login) { // А вдруг?
            $db = Application::database();
            $statement = $db->table(self::TABLE)
                ->select('id', 'uuid', 'name', 'password')
                    ->where('login', $login)
                    ->where('active', 1);
            if ($statement->count() === 1) {
                $record = $statement->first();
                if (password_verify($password, $record['password'])) {
                    $this->_sign_in($record, true);
                    return true;
                }
            }
        }
        // Скроем косвенные признаки
        Application::randomSleep();
        return false;
    }



    /**
     * Осуществляет выход текущего актёра
     * @param bool $destroy Уничтожать сессию?
     */
    public function signOut($destroy = true) {
        if ($this->authenticated() && $destroy) {
            $this->session()->destroy();
        }
        // Текущее представление - анонимный!
        $this->_id = null; $this->_uuid = null;
        $this->_session->delete(self::KEY_SESSION);
    }



    /**
     * Возвращает текущего актёра
     * @return Actor
     */
    public static function getInstance() {
        static $instance = null;
        if (is_null($instance)) {
            $session = Session::getInstance();
            $actor = $session->get(self::KEY_SESSION);
            $instance = new Actor($actor);
        }
        return $instance;
    }



    /**
     * Возвращает идентификатор актёра
     * @return int|null
     */
    public function id() {
        return $this->_id;
    }



    /**
     * Возвращает UUID актёра
     * @return null|string
     */
    public function uuid() {
        return $this->_uuid;
    }



    /**
     * Возвращает имя акёра
     * @return string|null
     */
    public function name() {
        return $this->_name;
    }



    /**
     * Пользовтаель авторизован?
     * @return bool
     */
    public function authenticated() {
        return isset($this->_id);
    }



    /**
     * Возвращает объект сессии
     * @return Session
     */
    public function session() {
        return $this->_session;
    }



}
