<?php

namespace Service;

use JPM\SessionSharingBundle\Service\CryptService;
use PHPUnit\Framework\TestCase;


class CryptServiceTest extends TestCase
{
    private function getCryptService(
        string $tokenSyncSecret = null,
    ):CryptService{
        return new CryptService(
            tokenSyncSecret: $tokenSyncSecret ?? 'def00000581c19f04b57d75091fd22ae5ba9b20797f39497132d7acdd30ab7c3c8951111b8fa00a23687288686097beaaec1be4999c005a7620a70c917c692fa0c6d9d6a',
        );
    }

    /**
     * @test
     */
    public function itCreatesACryptService(){
        $cryptService = $this->getCryptService();
        $this->assertInstanceOf(CryptService::class,$cryptService);
    }

    /**
     * @test
     */
    public function itCreatesEncryptsValues(){
        $cryptService = $this->getCryptService();
        $rawData = 'my-deep-secret';
        $protectedData = $cryptService->encrypt($rawData);
        $this->assertNotEquals($rawData,$protectedData);
        $this->assertStringStartsWith('def',$protectedData);
    }

    /**
     * @test
     */
    public function itDecryptsValues(){
        $cryptService = $this->getCryptService();
        $rawData = 'my-deep-secret';
        $encryptedValue = 'def502008128c7daa8e06006453d97332cc02ef3608b562d79ed9b06799c182be9cec55036206681399bd3270e196796961a750e5c162df51fe9f64fc5592f96acbca26ad461f5fe462774376e346338e1bf51f7f77437bc0c3e5095601b00ba2506';
        $decryptedData = $cryptService->decrypt($encryptedValue);
        $this->assertEquals($rawData,$decryptedData);
    }
}