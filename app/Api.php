<?php namespace Yaseek\YNO\App;

use Yaseek\YNO\Core\Helper;

use Klein\Request;
use Klein\Response;



/**
 * Обработчик api запросов
 * @package Yaseek\YNO\App
 */
class Api {



    /**
     * @var Request
     */
    protected $_request = null;

    /**
     * @var Response
     */
    protected $_response = null;



    const TYPE_NUMERIC = 1;
    const TYPE_INTEGER = 2;
    const TYPE_FLOAT = 3;
    const TYPE_STRING = 4;
    const TYPE_UUID = 5;
    const TYPE_LIST = 6;
    const TYPE_UUID_LIST = 7;



    /**
     * Конструктор
     * @param Request $request Запрос
     * @param Response $response Ответ
     */
    public function __construct(Request $request, Response $response) {
        $this->_request = $request;
        $this->_response = $response;
    }



    /**
     * Выполняет проверку ключей и типов
     * @param array $params Параметры запроса
     * @param array $mandatory Требование к полям
     * @throws \Exception
     */
    private static function _check_mandatories($params, $mandatory) {
        foreach ($mandatory as $key => $type) {
            if (!array_key_exists($key, $params)) {
                throw new \Exception('Mandatory key "' . $key . '" not exists');
            }
            $value = $params[$key];
            switch ($type) {
                case self::TYPE_NUMERIC:
                    if (!is_numeric($value)) { throw new \Exception('Key "' . $key . '" is not a numeric'); }
                    break;
                case self::TYPE_INTEGER:
                    if (!is_int($value)) { throw new \Exception('Key "' . $key . '" is not an integer'); }
                    break;
                case self::TYPE_FLOAT:
                    if (!is_float($value)) { throw new \Exception('Key "' . $key . '" is not an float'); }
                    break;
                case self::TYPE_STRING:
                    if (!is_string($value)) { throw new \Exception('Key "' . $key . '" is not an string'); }
                    break;
                case self::TYPE_UUID:
                    if (!Helper::isUuid($value)) { throw new \Exception('Key "' . $key . '" is not an uuid'); }
                    break;
                case self::TYPE_LIST:
                    if (!is_array($value)) { throw new \Exception('Key "' . $key . '" is not an list'); }
                    foreach ($value as $val) { if (is_array($val)) { throw new \Exception('Key "' . $key . '" is not an list'); } }
                    break;
                case self::TYPE_UUID_LIST:
                    if (!is_array($value)) { throw new \Exception('Key "' . $key . '" is not an list of uuid'); }
                    foreach ($value as $val) {
                        if (!Helper::isUuid($val)) { throw new \Exception('Key "' . $key . '" is not an list of uuid'); }
                    }
                    break;
            }
        }
    }



    /**
     * Возвращает параметры запроса
     * @param array $mandatory Обязательные поля
     * @return array
     * @throws \Exception
     */
    public function getRequestParams($mandatory = null) {
        $params = array();
        if ($body = $this->_request->body()) {
            $params = Helper::jsonDecode($body);
        }
        if ($files = $this->_request->files()->all()) {
            $params = array_merge($params, $files);
        }
        if (isset($mandatory) && is_array($mandatory)) {
            self::_check_mandatories($params, $mandatory);
        }
        return $params;
    }



    /**
     * Выполняет запрос к API
     * @param callable $callback Обработчик
     */
    public function process($callback) {
        $result = $key = null;
        if (!is_array($result)) {
            $result = array('result' => null, 'error' => null);
            try { $result['result'] = $callback(); }
            catch (\Exception $e) {
                $result['error'] = array(
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                );
            }
        }
        $this->_response->json($result);
    }



}
