<?php

namespace JPM\SessionSharingBundle\Service;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
class CryptService
{
    public function __construct(
        private string $tokenSyncSecret,
    ){
    }
    public function encrypt(string $rawData): string{
        $key = Key::loadFromAsciiSafeString($this->tokenSyncSecret);
        return Crypto::encrypt($rawData,$key);
    }
    public function decrypt(string $protectedData): string{
        $key = Key::loadFromAsciiSafeString($this->tokenSyncSecret);
        return Crypto::decrypt( $protectedData, $key);
    }



}