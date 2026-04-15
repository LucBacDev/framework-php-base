<?php

namespace Company\Encrypt;

abstract class BaseCipher
{
    abstract function GenerateKey();

    abstract function EnCipher($input, $key);

    abstract function DeCipher($input, $key);
}