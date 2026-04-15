<?php

namespace Company\Session;

use Company\MVC\Bootstrap;
use Company\User\Model\UserEntity;

class Session extends SessionAdapter {

    protected $mapper;

    function __construct() {
        parent::__construct();
        $this->data = isset($_SESSION) ? $_SESSION : [];
    }

    function setID($sessionID) {
        $this->sessionID = $sessionID;
        $this->data = SessionMapper::makeInstance()->loadSession($sessionID);
        if(isset($this->data['user'])) {
            $this->data['user'] = new UserEntity($this->data['user']);
        }
    }
    
    function getID() {
       return $this->sessionID;
    }

    function saveSession() {
        if(!$this->sessionID){
            $this->sessionID = uid();
        }
        //$mapper = SessionMapper::makeInstance();
        $app = Bootstrap::getInstance();
        //$expire = \DateTimeEx::create()->addSecond($this->getConfigExpire());
        //lưu cookie
       /** if (!isset($_COOKIE['PHPSESS'])) {
            setcookie("PHPSESS", $this->getID(), time() + 3600 * 24 * 365 * 10, $app->rewriteBase);
        }

        //chỉ lưu vào CSDL khi Session !empty
        if (!empty($this->data)) {
            $mapper->save($this->getID(), $this->data, $expire->toIsoString($showTime = true));
        }
       */
    }

    function clearSession($id = null) {
        //SessionMapper::makeInstance()->filterID($id)->delete();
        session_start();
        $_SESSION = [];
        session_destroy();
    }

}
