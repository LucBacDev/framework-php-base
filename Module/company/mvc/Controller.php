<?php

namespace Company\MVC;

abstract class Controller extends \Macroable {

    /** @var MvcContext */
    protected $context;

    /** @var \Slim\Http\Request */
    protected $req;

    /** @var \Slim\Http\Response */
    protected $resp;

    protected $_input = false;

    function __construct(MvcContext $context) {
        $this->context = $context;
        $this->req = $context->app->slim->request;
        $this->resp = $context->app->slim->response;
        //nếu là Restful
        if ($this->isRest()) {
            $this->resp->header('Content-type', 'application/json');
        }

        if (ENV("HEADER_ACTIVE")) {
            $arrHeader = ENV("HEADER_LIST");
            if (!empty($arrHeader) && is_array($arrHeader)) {
                foreach ($arrHeader as $header) {
                    header($header);
                }
            }
        }

        $this->init();
    }

    /** run after __construct, tobe overrided */
    protected function init() {
        
    }

    protected function escape($str) {
        $str = stripslashes($str);
        $arr_search = array('&', '<', '>', '"', "'");
        $arr_replace = array('&amp;', '&lt;', '&gt;', '&#34;', '&#39;');
        $str = str_replace($arr_search, $arr_replace, $str);

        return $str;
    }

    protected function getCookie($name) {
        return call_user_func(array($this->context->app->slim, 'getCookie'), $name);
    }

    protected function setCookie($name, $value) {
        return call_user_func(array($this->context->app->slim, 'setCookie'), $name, $value);
    }

    /**
     * Undocumented function
     *
     * @param string|array $key
     * @param mixed $default
     * @return mixed
     */
    function input($key = null, $default = null) {
        if($this->_input === false) {
            $input = file_get_contents('php://input');
            if (strlen($input) && ($input[0] == '{' || $input[0] == '[')) {
                $this->_input = json_decode($input, true);
            } else {
                $this->_input = array_merge($_GET, $_POST);
            }
        }
        if ($key === null) {
            return $this->_input;
        } else if (is_array($key)) {
            $ret = [];
            foreach ($key as $k) {
                $ret[$k] = $this->_input[$k] ?? $default;
            }
            return $ret;
        } else if (is_string($key) || is_numeric($key)) {
            return $this->_input[$key] ?? $default;
        } else {
            throw new \Exception("Invalid input key");
        }
    }

    /**
     * Kiểm tra xem request có phải là rest không
     */
    protected function isRest() {
        if (strtolower($this->req->headers('content-type', "")) == 'application/json') {
            return true;
        }
        $input = trim(file_get_contents('php://input'));
        if ($input) {
            if (in_array($input[0], ['{', '[']) && in_array($input[strlen($input) - 1], ['}', ']'])) {
                return true;
            }
        }
        return false;
    }

    protected function outputJSON($data) {
        $this->resp->header('Content-Type', 'application/json');
        $this->resp->setBody(Json::encode($data));
    }

}
