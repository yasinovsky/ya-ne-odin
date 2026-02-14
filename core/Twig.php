<?php namespace Yaseek\YNO\Core;



/**
 * Представление конфигурации Twig
 * @package DL\Nauka\Core
 */
class Twig extends \Twig_Environment {



    /**
     * Режим прав на файлы
     */
    const MODE_FILE = 0664;

    /**
     * Режим прав на директории
     */
    const MODE_DIRECTORY = 0775;



    /**
     * @deprecated since 1.22 (to be removed in 2.0)
     */
    protected function writeCacheFile($file, $content) {
        $directory = dirname($file);
        if (!file_exists($directory)) {
            $mask = umask(0002); // Сохраним текущее
            mkdir($directory, self::MODE_DIRECTORY, true);
            umask($mask); // Вернем старое значение
        }
        parent::writeCacheFile($file, $content);
        chmod($file, self::MODE_FILE);
    }



}
