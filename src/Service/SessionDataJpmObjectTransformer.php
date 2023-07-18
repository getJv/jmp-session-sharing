<?php

namespace JPM\SessionSharingBundle\Service;

use JPM\SessionSharingBundle\DTO\JpmObject;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Serializer\SerializerInterface;

class SessionDataJpmObjectTransformer
{
    public function __construct(
        private SerializerInterface $serializer,
        private CryptService        $cryptService,
        private string              $idpUrl,
    ){
    }

    public function transform(string $base64Data):JpmObject {
        $jpmObject = $this->validateJmpObject($base64Data);
        $this->validateIdp($jpmObject);
        $this->validateChecksum($jpmObject);

       return new JpmObject(
            origin: $jpmObject->origin,
            data:  $this->cryptService->decrypt($jpmObject->data),
            checksum: $jpmObject->checksum
        );
    }

    private function validateJmpObject(string $base64Data):JpmObject{
        $serializedObject = base64_decode($base64Data);
        $jpmObject = $this->serializer->deserialize(
            data: $serializedObject,
            type: JpmObject::class,
            format: 'json'
        );

        if(!($jpmObject instanceof JpmObject)){
            throw new AccessDeniedHttpException();
        }

        return $jpmObject;
    }

    private function validateIdp(JpmObject $jpmObject):void{
        if(!$jpmObject->origin){
            throw new AccessDeniedHttpException();
        }
        $regexRoot = "(http(s)?:\/\/)?(www\.)?";
        $domain = preg_replace("/" . $regexRoot. "/" ,"",$jpmObject->origin);
        $domain = substr($domain,0,strlen($domain)-1);
        $regex = "/(" . $regexRoot . ")?" . $domain. "/";
        $isFromIdp = preg_match($regex,$this->idpUrl);
        if(!$isFromIdp){
            throw new AccessDeniedHttpException();
        }
    }
    private function validateChecksum(JpmObject $jpmObject):void{
        $isChecksumNotEqual = crc32($jpmObject->data . $jpmObject->origin ) != $jpmObject->checksum;
        if($isChecksumNotEqual){
            throw new AccessDeniedHttpException();
        }
    }
}