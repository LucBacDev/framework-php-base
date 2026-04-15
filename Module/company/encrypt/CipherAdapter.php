<?php

namespace Company\Encrypt;

use Company\Exception\BadRequestException;

class CipherAdapter
{
    const SUSB = "SUSB";

    static function getInstance($algo){
        switch ($algo){
            case self::SUSB:
                return new SUSBCipher();
            default:
                throw new BadRequestException("Cipher type invalid");
        }
    }
}