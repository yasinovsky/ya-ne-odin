<?php namespace Yaseek\YNO\Core;

use Yaseek\YNO\Core\Logger\Context;



/**
 * Логгер событий
 * @method void info($message, $context = null)
 * @method void notice($message, $context = null)
 * @method void warning($message, $context = null)
 * @method void error($message, $context = null)
 * @method void security($message, $context = null)
 * @package Yaseek\YNO\Core
 */
class Logger {



    /**
     * Путь к корню
     * @var string
     */
    private static $_root = null;



    /**
     * Символ новой строки
     */
    const NL = "\r\n";



    /**
     * Режим прав на файлы
     */
    const MODE_FILE = 0664;

    /**
     * Режим прав на директории
     */
    const MODE_DIRECTORY = 0775;



    /**
     * Конструктор
     * @param string $root Путь к корню приложения
     */
    public function __construct($root) {
        self::$_root = $root;
    }



    /**
     * Возвращает трейс отладки до события
     * @param bool|true $as_string Возвращать строкой
     * @return array|string
     */
    private function _backtrace($as_string = true) {
        $stack = debug_backtrace();
        array_shift($stack); array_shift($stack); // Убираем "себя" :)
        return $as_string
            ? Helper::jsonEncode($stack)
            : $stack;
    }



    /**
     * Возвращает путь к директории лог-файлов
     * @param string $date
     * @return string
     */
    private static function _get_directory($date) {
        static $logs = null;
        if (is_null($logs)) {
            $logs = self::$_root . '/temp/logs';
        }
        $result = $logs . '/' . str_replace('-', '/', $date);
        if (!file_exists($result)) {
            $mask = umask(0002); // Сохраним текущее
            mkdir($result, self::MODE_DIRECTORY, true);
            umask($mask); // Вернем старое значение
        }
        return $result;
    }



    /**
     * Пишет событие в файл
     * @param array $time Дата/время
     * @param string $name Имя/тип ошибки
     * @param Context $context Контекст
     * @param bool $trace Трассировать выполнение
     */
    private function _to_file($time, $name, $context, $trace = false) {
        $request = Helper::request(); // Один раз достанем запрос
        $file = self::_get_directory($time['date']) . '/' . $name . '.log';
        $data =
            $time['date'] . ' ' . $time['time'] . ' ' . $time['usec'] . ' - ' . $context->message() . self::NL
            . ($request->remoteAddress() ? '  Client:   ' . $request->remoteAddress() . ', ' . $request->userAgent() . self::NL : '')
            . '  Context:  ' . Helper::jsonEncode($context->context()) . self::NL;
        if ($trace) {
            $data .= '  Trace:    ' . $this->_backtrace() . self::NL;
        }
        $exists = file_exists($file);
        file_put_contents($file, $data . self::NL, FILE_APPEND);
        if (!$exists) { chmod($file, self::MODE_FILE); } // Поменяем права
    }



    /**
     * Возвращает дату/время
     * @param int $precision Точность
     * @return array
     */
    private static function _get_time($precision = 4) {
        $microtime = microtime(true);
        $time = floor($microtime);
        return array(
            'date' => date('Y-m-d', $time),
            'time' => date('H:i:s', $time),
            'usec' => substr($microtime - $time, 2, $precision),
        );
    }



    /**
     * Пишет событие в лог
     * @param string $name Имя функции
     * @param array $arguments Аргументы функции
     */
    public function __call($name, $arguments) {
        $time = self::_get_time();
        $context = new Context($arguments);
        $this->_to_file($time, $name, $context, $name == 'error');
    }



}
