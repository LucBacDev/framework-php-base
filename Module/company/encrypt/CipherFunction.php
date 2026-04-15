<?php

namespace Company\Encrypt;

class CipherFunction
{

    static function GenerateKey($algo)
    {
        return CipherAdapter::getInstance($algo)->GenerateKey();
    }

    static function EnCipher($algo, $input, $key)
    {
        return CipherAdapter::getInstance($algo)->Encipher($input, $key);
    }

    static function DeCipher($algo, $input, $key)
    {
        return CipherAdapter::getInstance($algo)->Decipher($input, $key);
    }

    static function EnCipherArray($algo, $inputs, $key_algo)
    {
        foreach ($inputs as $key => $item){
            $inputs[$key] = static::EnCipher($algo, $item, $key_algo);
        }

        return $inputs;
    }

    static function DeCipherArray($algo, $inputs, $key_algo)
    {
        foreach ($inputs as $key => $item){
            $inputs[$key] = static::DeCipher($algo, $item, $key_algo);
        }

        return $inputs;
    }
}