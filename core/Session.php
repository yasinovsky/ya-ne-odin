<?php namespace Yaseek\YNO\Core;

use Yaseek\YNO\App\Application;



/**
 * Представление сессии
 * @package Yaseek\YNO\Core
 */
class Session {



    /**
     * Идентификатор сесии
     * @var string
     */
    private $_id = null;

    /**
     * Имя сессии
     * @var string
     */
    private $_name = null;

    /**
     * Данные сессии
     * @var array
     */
    private $_data = array();



    /**
     * Дата устаревания сесии
     * @var int
     */
    private $_expires = null;



    /**
     * Присылались ли данные
     * @var bool
     */
    private $_data_changed = false;

    /**
     * Параметры установки куки
     * @var array
     */
    private $_cookie_params = array(
        'lifetime' => null,
        'path' => '/', 'domain' => '',
        'secure' => null, 'httponly' => null,
        // 'samesite' => 'Lax',
    );



    /**
     * Кастомизированный обработчик сессии
     * @var null|\SessionHandlerInterface
     */
    private $_handler = null;

    /**
     * Имя ключа с данными
     */
    private const KEY_DATA = '__data';

    /**
     * Имя ключа с датой устаревания
     */
    private const KEY_EXPIRES = '__expr';



    /**
     * Конструктор
     * @throws \Exception
     */
    public function __construct() {
        $config = Application::config();
        $lifetime = time() + $config->session('lifetime');
        if ($config->session('driver') == 'database') {
            $this->_handler = \Yaseek\YNO\Core\Session\Database::getInstance();
            @session_set_save_handler($this->_handler, false); // Перегрузим
        }
        $this->_name = $config->session('name');
        @session_name($this->_name); // Зададим имя сесии
        if ($this->_cookie_exists()) {
            $this->_session_start();
            // Загрузим изначальные значения для данных и времени устаревания
            $this->_data = $this->_get_data_section(self::KEY_DATA, array());
            $lifetime = $this->_get_data_section(self::KEY_EXPIRES, $lifetime);
        }
        $this->_configure_cookie_params();
        $this->expires($lifetime);
    }



    /**
     * Деструктор
     */
    public function __destruct() {
        // Если сессия была продолжена или данные были присланы
        if ($this->_cookie_exists() || $this->_data_changed) {
            $_SESSION[self::KEY_DATA] = $this->_data; // ... кладем
            $_SESSION[self::KEY_EXPIRES] = $this->_expires;
            @session_set_cookie_params($this->_cookie_params);
            session_write_close(); // Закрываем нашу сессию
        }
    }



    /**
     * Была ли стартована сессия?
     * @return bool
     */
    private function _cookie_exists() {
        return isset($_COOKIE) &&
            array_key_exists($this->_name, $_COOKIE);
    }



    /**
     * Перестартовывает сессию
     * @param string|null $id Идентификатор сесии
     */
    public function renew($id = null) {
        session_destroy();
        $this->_session_new($id);
    }



    /**
     * Уничтожает сессию
     */
    public function destroy() {
        session_destroy();
        $this->_data_changed = false;
    }



    /**
     * Удаляет сессию по идентификатору
     * @param string $token Идентификатор сессии
     */
    public function destroyByToken($token) {
        if (isset($this->_handler) &&
            method_exists($this->_handler, 'destroy')) {
            $this->_handler->destroy($token);
        }
    }



    /**
     * Создает новую сессию
     * @param string|null $id Идентификатор сесии
     */
    private function _session_new($id = null) {
        $this->_id = isset($id)
            ? $id : Helper::getToken();
        @session_id($this->_id);
        @session_set_cookie_params($this->_cookie_params);
        @session_start();
    }



    /**
     * Стартует сессию
     */
    private function _session_start() {
        do {
            if ($this->_cookie_exists()) {
                @session_set_cookie_params($this->_cookie_params);
                @session_start(); // Пробуем продолжить начатую
                if (!array_key_exists(self::KEY_DATA, $_SESSION) && !$this->_data_changed) {
                    session_destroy(); // Данных нет и не записывались ранее - уничтожаем!
                }
                // Ок, создавать не нужно, продолжаем её
                else { $this->_id = session_id(); break; }
            }
            // Создаем новую, используя идентификатор, если он есть...
            $this->_session_new(isset($this->_id) ? $this->_id : null);
        }
        while (false);
    }



    /**
     * Возвращает или устанавливает идентификатор сесии
     * @param string|null $id Идентификатор сесии
     * @return string
     */
    public function id($id = null) {
        if (isset($id) && $id != $this->_id) {
            $this->renew($id); // Обновим сессию
        }
        return $this->_id;
    }



    /**
     * Возвращает данные сессии по ключу
     * @param string $key Имя ключа
     * @param mixed $default Значение по умолчанию
     * @return mixed
     */
    public function get($key, $default = null) {
        return array_key_exists($key, $this->_data)
            ? $this->_data[$key] : $default;
    }



    /**
     * Записывает данные сессии по ключу
     * @param string $key Имя ключа
     * @param mixed $data Данные
     */
    public function set($key, $data) {
        $this->_data_changed = true; // Данные посылались! :)
        $this->_session_start(); // Стартуем (на всякий случай)
        $this->_data[$key] = $data; // Записываем данные по ключу
    }



    /**
     * Возвращает или устанавливает данные сессии
     * @param array|null $data
     * @return array
     */
    public function data($data = null) {
        if (is_array($data)) {
            $this->_data_changed = true;
            $this->_session_start();
            $this->_data = $data;
        }
        return $this->_data;
    }



    /**
     * Удаляет значение по ключу
     * @param string $key Имя ключа
     */
    public function delete($key) {
        if (array_key_exists($key, $this->_data)) {
            $this->_data_changed = true;
            unset($this->_data[$key]);
        }
    }



    /**
     * Возвращает данные по ключу и удаляет их из сессии
     * @param string $key Имя ключа
     * @param mixed $default Значение по умолчанию
     * @return mixed
     */
    public function extract($key, $default = null) {
        $result = $this->get($key, $default);
        $this->delete($key);
        return $result;
    }



    /**
     * Возвращает метку времени окончания сессии
     * @param int|null $time Дата устаревания сесии
     * @return int
     * @throws \Exception
     */
    public function expires($time = null) {
        if (isset($time)) {
            $this->_expires = $time; // И в параметрах тоже!
            $this->_cookie_params['lifetime'] = $time - time();
        }
        if (isset($this->_handler)) { // Зададим для БД
            $this->_handler->expires($this->_expires);
        }
        return $this->_expires;
    }



    /**
     * Возвращает срез данных из сессии
     * @param string $key Имя ключа
     * @param mixed $default Значение по-умолчанию
     * @return mixed
     */
    private function _get_data_section($key, $default) {
        return array_key_exists($key, $_SESSION)
            ? $_SESSION[$key] : $default;
    }



    /**
     * Настраивает параметры установки куки
     * @throws \Exception
     */
    private function _configure_cookie_params() {
        $config = Application::config();
        $this->_cookie_params['secure'] = $config->session('secure');
        $this->_cookie_params['httponly'] = $config->session('http-only');
    }



}
