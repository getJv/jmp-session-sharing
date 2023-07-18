<?php

namespace JPM\SessionSharingBundle\Service;

use JPM\SessionSharingBundle\DTO\JpmObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class SessionUrlResponseGenerator
{
    public function __construct(
        private SerializerInterface $serializer,
        private CryptService        $cryptService,
        private string              $attrNameUrlCallback,
        private string              $appUrl,
        private string              $attrNameUrlToken,
    ){

    }

    public function generate(Request $request):string {
        $callbackUrl = base64_decode($request->get($this->attrNameUrlCallback,'/'));
        $sessionId = $request->getSession()->getId();
        $encryptedData = $this->cryptService->encrypt($sessionId);
        $jmpData = new JpmObject(
            origin: $this->appUrl,
            data:   $encryptedData ,
            checksum: crc32(($encryptedData . $this->appUrl))
        );
        $serializedData = $this->serializer->serialize($jmpData,'json');
        $protectedData =  base64_encode($serializedData);
        $hasParam = preg_match('/\?/' ,$callbackUrl);
        $paramChar = $hasParam ? '&' : '?';
        return $callbackUrl . $paramChar . $this->attrNameUrlToken . '=' . $protectedData;
    }
}