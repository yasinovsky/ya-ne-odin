<?php namespace Yaseek\YNO\Core\Exception;



/**
 * Создается исключение при попытке произвести недопустимую операцию над пустым контейнером
 * Например такую, как удаление элемента пустого контейнера
 * @package Yaseek\YNO\Core\Exception
 */
class UnderflowException extends RuntimeException {
}
