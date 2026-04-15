<?php

namespace Company\MVC;

class Cryptography {
    protected static $instance;
    static function getInstance() {
        if (!static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    function encrypt($data) {
        $fp = fopen(BASE_DIR . "/Encrypt/key/private.pem", "r");
        $priv_key = fread($fp, 8192);
        fclose($fp);
        $res = openssl_get_privatekey($priv_key, "97f014516561ef487ec368d6158eb3f4");
        $res = openssl_private_encrypt($data, $crypted, $res);
        $crypted = base64_encode($crypted);
        return $crypted;
    }

    function decrypt($crypted) {
        $fp = fopen(BASE_DIR . "/Encrypt/key/public.pem", "r");
        $pub_key = fread($fp, 8192);
        fclose($fp);
        $res = openssl_get_publickey($pub_key);
        openssl_public_decrypt(base64_decode($crypted), $decrypted, $res);
        return $decrypted;
    }
 
    function decryptFile($fileDir) {
        $output = '';
        if (is_file($fileDir)) {
            $handle = fopen($fileDir, "r");
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    $line = str_replace("\n", "", $line);
                    if ($line) {
                        $decrypt = $this->decrypt($line);
                        $output .= $decrypt;
                    }
                }
                fclose($handle);
            }
        }
        return $output;
    }

    function decryptStr($string) {
        $arrString = explode("\n", $string);
        $output = '';
        foreach ($arrString as $line) {
            if ($line) {
                $decrypt = $this->decrypt($line);
                $output .= $decrypt;
            }
        }
        return $output;
    }

}
