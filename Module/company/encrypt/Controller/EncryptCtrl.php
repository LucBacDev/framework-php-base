<?php
namespace Company\Encrypt\Controller;

use Company\Auth\Auth;
use Company\Encrypt\Model as M;

class EncryptCtrl extends \Company\MVC\Controller
{
    protected $auth;

    function init() {
        parent::init();
        $this->auth = Auth::getInstance();
    }

    function getKeyEncrypt($algo = null){
        $algo = strtoupper($algo);

        $key = \Company\Encrypt\CipherFunction::GenerateKey($algo);
        $res = [
            "key_algo" => $key,
            "id" => $algo. "_" .hash("crc32", $key)
        ];

        $this->resp->setBody(json_encode(result(true, $res)));
    }

    function updateEncrypt($encryptID = null) {
        $this->auth->requireAdmin();

        M\EncryptMapper::makeInstance()->updateEncrypt($encryptID, $this->input());
        $this->resp->setBody(json_encode(result(true)));
    }

    function getEncrypt($encryptID = null){
        $this->auth->requireAdmin();

        $res =
            ($encryptID) ?
                M\EncryptMapper::makeInstance()->getEncrypt($encryptID) :
                M\EncryptMapper::makeInstance()->getAllEncrypt();

        $this->resp->setBody(json_encode($res));
    }

    function deleteEncrypt($id) {
        $this->auth->requireAdmin();

        M\EncryptMapper::makeInstance()->deleteEncrypt($id);
        $this->resp->setBody(json_encode(result(true)));
    }
}