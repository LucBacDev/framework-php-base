<?php

namespace Company\Session;

abstract class SessionAdapter {

    static protected $instance;
    protected $data = [];
    protected $sessionID;
    static protected $expire = 3600; //mặc định 1 tiếng hết hạn

    /**
     * Cho phép override instance
     * @param Session $instance
     */

    static function setInstance(Session $instance) {
        SessionAdapter::$instance = $instance;
    }

    function __construct() {
        //do noting
    }

    /**
     * Cấu hình thời gian hết hạn
     * @param type $expireSec tính bằng giây, nếu không có request mới đến server sẽ tự động hết hạn session
     */
    static function config($expireSec) {
        if ($expireSec) {
            SessionAdapter::$expire = $expireSec;
        }
    }

    /** @return Session */
    static function getInstance() {
        if (!SessionAdapter::$instance) {
            SessionAdapter::$instance = new Session;
        }

        return SessionAdapter::$instance;
    }

    function getID() {
        if (!$this->sessionID) {
            $this->sessionID = uid();
        }
        return $this->sessionID;
    }

    function set($key, $value) {
        $this->data[$key] = $value;
        $_SESSION[$key] = $value;
    }

    function get($key, $default = null) {
        return arrData($this->data, $key, $default);
    }

    function getConfigExpire() {
        return SessionAdapter::$expire;
    }

}
