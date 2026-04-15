<?php
/**
$text = "NGUYEN VAN A 123123 ^^^";
var_dump("text", $text);

$cipherAlphabet = \Company\Encrypt\SUSBCipher::GenerateKey();
var_dump("cipherAlphabet", $cipherAlphabet);

$encipherResult = \Company\Encrypt\SUSBCipher::Encipher($text,$cipherAlphabet);
var_dump("encipherResult", $encipherResult);
$decipherResult = \Company\Encrypt\SUSBCipher::Decipher($encipherResult, $cipherAlphabet);
var_dump("decipherResult", $decipherResult);
 */
namespace Company\Encrypt;

class SUSBCipher extends BaseCipher
{
    protected $plainAlphabet = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

    function GenerateKey(){
        $arrPlainAlphabet = str_split($this->plainAlphabet);
        shuffle($arrPlainAlphabet);
        $cipherAlphabet = implode($arrPlainAlphabet);
        return $cipherAlphabet;
    }

    function Cipher($input, $oldAlphabet, $newAlphabet)
    {
        $output = "";
        $inputLen = strlen($input);

        if (strlen($oldAlphabet) != strlen($newAlphabet))
            return false;

        for ($i = 0; $i < $inputLen; ++$i)
        {
            $oldCharIndex = strpos($oldAlphabet, $input[$i]);

            if ($oldCharIndex !== false)
                $output .= $newAlphabet[$oldCharIndex];
            else
                $output .= $input[$i];
        }

        return $output;
    }

    function Encipher($input, $cipherAlphabet)
    {
        return $this->Cipher($input, $this->plainAlphabet, $cipherAlphabet);
    }

    function Decipher($input, $cipherAlphabet)
    {
        return $this->Cipher($input, $cipherAlphabet, $this->plainAlphabet);
    }
}