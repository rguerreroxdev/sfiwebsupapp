<?php
//-----------------------------------------------

Class Crypt
{
    //-------------------------------------------

    private static string $key = "cryptdecrypt";

    //-------------------------------------------

    /**
     * Encripta un valor recibido
     * 
     * @param $data Valor a encriptar, puede ser de cualquier tipo
     * 
     * @return string Valor encriptado
     * 
     */
    public static function encriptar($data): string
    {
        $cipher = "aes-256-cbc";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext = openssl_encrypt($data, $cipher, self::$key, $options=0, $iv);
        return base64_encode($iv.$ciphertext);
    }

    //-------------------------------------------

    /**
     * Desencripta un valor recibido, que fue encriptado por el método encriptar de esta misma clase
     * 
     * @param string $data Valor encrpitado por el método encriptar de esta misma clase
     * 
     * @return string Valor desencriptado
     * 
     */
    public static function desencriptar($data)
    {
        $cipher = "aes-256-cbc";
        $data = base64_decode($data);
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = substr($data, 0, $ivlen);
        $ciphertext = substr($data, $ivlen);
        return openssl_decrypt($ciphertext, $cipher, self::$key, $options=0, $iv);
    }

    //-------------------------------------------
}

//-----------------------------------------------