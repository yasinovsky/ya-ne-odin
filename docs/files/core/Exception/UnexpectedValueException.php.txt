<?php namespace Yaseek\YNO\Core\Exception;



/**
 * Создается исключение, если значение не совпадает с набором значений
 * Обычно это происходит, когда функция вызывает другую функцию и ожидает, что возвращаемое значение будет
 * определенного типа, или значение, не включая арифметические ошибки, или ошибки, связанные с буфером
 * @package Yaseek\YNO\Core\Exception
 */
class UnexpectedValueException extends RuntimeException {
}
