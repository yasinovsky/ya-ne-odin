<?php namespace Yaseek\YNO\Core\Exception;



/**
 * Генерируется исключение, чтобы указать ошибки диапазона во время исполнения программы
 * Как правило, это означает, что была арифметическая ошибка, отличная от потери значимости и переполнения
 * Это версия класса DomainException, доступная на этапе исполнения
 * @package Yaseek\YNO\Core\Exception
 */
class RangeException extends RuntimeException {
}
