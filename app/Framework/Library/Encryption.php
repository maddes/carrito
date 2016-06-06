<?php

namespace Carrito\Framework\Library;

final class Encryption
{
    private $key;

    public function __construct($app)
    {
        $this->key = hash('sha256', $app->get('config')->get('config_encryption'), true);
    }

    public function encrypt($value)
    {
        return strtr(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, hash('sha256', $this->key, true), $value, MCRYPT_MODE_ECB)), '+/=', '-_,');
    }

    public function decrypt($value)
    {
        return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, hash('sha256', $this->key, true), base64_decode(strtr($value, '-_,', '+/=')), MCRYPT_MODE_ECB));
    }
}
