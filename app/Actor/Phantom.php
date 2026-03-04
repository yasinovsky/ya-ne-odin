<?php namespace Yaseek\YNO\App\Actor;

use Yaseek\YNO\App\Actor;
use Yaseek\YNO\App\Application;



/**
 * Представление фантома актёра
 * @package Yaseek\YNO\App\Actor
 */
class Phantom extends Actor {



    /**
     * @var \Illuminate\Database\Connection
     */
    private $_db = null;



    /**
     * Конструктор
     * @param Actor|string|int $actor Актёр или идентификатор актёра
     * @throws \Exception
     */
    public function __construct($actor) {
        $this->_db = Application::database();
        switch (true) {
            case is_object($actor) && $actor instanceof Actor:
                $this->_id = $actor->id();
                $this->_uuid = $actor->uuid();
                break;
            case is_numeric($actor):
                $this->_id = $actor;
                $this->_uuid = $this->_db->table(self::TABLE)
                    ->where('id', $actor)->value('uuid');
                break;
            case is_string($actor);
                $this->_id = $this->_db->table(self::TABLE)
                    ->where('uuid', $actor)->value('id');
                $this->_uuid = $actor;
                break;
            default:
                throw new \Exception('Invalid type for actor');
                break;
        }
        if (is_null($this->_id) || is_null($this->_uuid)) {
            throw new \Exception('Actor is not exists');
        }
        // Приведем к числу, это важно
        $this->_id = intval($this->_id);
        // Как то не очень хорошо получается, но пока так
        $this->_name = $this->_db->table(self::TABLE)
            ->where('id', $this->_id)->value('name');
    }



    /**
     * @inheritDoc
     */
    public function signIn($login, $password) {
        throw new \Exception('it\'s impossible');
    }


    /**
     * @inheritDoc
     */
    public function signOut($destroy = true) {
        throw new \Exception('it\'s impossible');
    }



}
