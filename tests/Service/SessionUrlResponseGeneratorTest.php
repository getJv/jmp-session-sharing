<?php

namespace Service;

use JPM\SessionSharingBundle\DTO\JpmObject;
use JPM\SessionSharingBundle\Service\CryptService;
use JPM\SessionSharingBundle\Service\SessionDataJpmObjectTransformer;
use JPM\SessionSharingBundle\Service\SessionUrlResponseGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Serializer\SerializerInterface;


class SessionUrlResponseGeneratorTest extends TestCase
{

    private function getSessionUrlResponseGenerator(
        SerializerInterface $serializer = null,
    ):SessionUrlResponseGenerator{
        return new SessionUrlResponseGenerator(
            serializer: $serializer ?? $this->createMock(SerializerInterface::class),
            cryptService: new CryptService(
            'def0000031429fb75a4765f30c2e57acc45f7cda73d16163f3d2bcf8f9807db24580d06bfe12500bb0413058bcedbc045d84f8ecf574ca194169057f875ff4c6118cb8c1'
            ),
            attrNameUrlCallback: 'callback',
            appUrl: 'http://host-idp.test',
            attrNameUrlToken: 'token'
        );
    }

    /**
     * @test
     */
    public function itCreatesSessionDataJpmObjectTransformer(){
        $transformer = $this->getSessionUrlResponseGenerator();
        $this->assertInstanceOf(SessionUrlResponseGenerator::class,$transformer);
    }

    /**
     * @test
     */
    public function itGeneratesTheUrl(){

        $serializer = $this->createMock(SerializerInterface::class);
        $serializedObject = '{"origin":"http:\/\/host-ipd.test","data":"def50200e6ce6ed955dfb53eb1222ebda9b02666f56aea07490b537d59acbb30cae06169ba5ee78446bb1318883a698d35429b9f37d1f5237cf19e1f7723e48399210f86fc0361589cb3d46623d2170689ccf92d6e9e02232d6db274d2d9ecc253d809d43f599d4d6dc7467cc27db132bb700df5","checksum":"1884056018"}';
        $serializer->method('serialize')->willReturn($serializedObject);
        $generator = $this->getSessionUrlResponseGenerator($serializer);

        $request = $this->createMock(Request::class);
        $request->method('get')->willReturn(base64_encode('http://remote.test'));
        $url = $generator->generate($request);
        $expectedUrl = "http://remote.test?token=eyJvcmlnaW4iOiJodHRwOlwvXC9ob3N0LWlwZC50ZXN0IiwiZGF0YSI6ImRlZjUwMjAwZTZjZTZlZDk1NWRmYjUzZWIxMjIyZWJkYTliMDI2NjZmNTZhZWEwNzQ5MGI1MzdkNTlhY2JiMzBjYWUwNjE2OWJhNWVlNzg0NDZiYjEzMTg4ODNhNjk4ZDM1NDI5YjlmMzdkMWY1MjM3Y2YxOWUxZjc3MjNlNDgzOTkyMTBmODZmYzAzNjE1ODljYjNkNDY2MjNkMjE3MDY4OWNjZjkyZDZlOWUwMjIzMmQ2ZGIyNzRkMmQ5ZWNjMjUzZDgwOWQ0M2Y1OTlkNGQ2ZGM3NDY3Y2MyN2RiMTMyYmI3MDBkZjUiLCJjaGVja3N1bSI6IjE4ODQwNTYwMTgifQ==";
        $this->assertEquals($url,$expectedUrl);


    }






}