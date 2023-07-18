<?php

namespace Service;

use JPM\SessionSharingBundle\DTO\JpmObject;
use JPM\SessionSharingBundle\Service\CryptService;
use JPM\SessionSharingBundle\Service\SessionDataJpmObjectTransformer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Serializer\SerializerInterface;


class SessionDataJpmObjectTransformerTest extends TestCase
{
    private function getSessionDataJpmObjectTransformer(
        SerializerInterface $serializer = null,
    ):SessionDataJpmObjectTransformer{
        return new SessionDataJpmObjectTransformer(
            serializer: $serializer ?? $this->createMock(SerializerInterface::class),
            cryptService: new CryptService(
                'def0000031429fb75a4765f30c2e57acc45f7cda73d16163f3d2bcf8f9807db24580d06bfe12500bb0413058bcedbc045d84f8ecf574ca194169057f875ff4c6118cb8c1'
            ),
            idpUrl: 'http://host-ipd.test'
        );
    }
    /**
     * @test
     */
    public function itCreatesSessionDataJpmObjectTransformer(){
        $transformer = $this->getSessionDataJpmObjectTransformer();
        $this->assertInstanceOf(SessionDataJpmObjectTransformer::class,$transformer);
    }

    /**
     * @test
     */
    public function itReturnsJpmObject(){
        $base64Input = 'eyJvcmlnaW4iOiJodHRwOlwvXC9ob3N0LWlwZC50ZXN0IiwiZGF0YSI6ImRlZjUwMjAwZTZjZTZlZDk1NWRmYjUzZWIxMjIyZWJkYTliMDI2NjZmNTZhZWEwNzQ5MGI1MzdkNTlhY2JiMzBjYWUwNjE2OWJhNWVlNzg0NDZiYjEzMTg4ODNhNjk4ZDM1NDI5YjlmMzdkMWY1MjM3Y2YxOWUxZjc3MjNlNDgzOTkyMTBmODZmYzAzNjE1ODljYjNkNDY2MjNkMjE3MDY4OWNjZjkyZDZlOWUwMjIzMmQ2ZGIyNzRkMmQ5ZWNjMjUzZDgwOWQ0M2Y1OTlkNGQ2ZGM3NDY3Y2MyN2RiMTMyYmI3MDBkZjUiLCJjaGVja3N1bSI6IjE4ODQwNTYwMTgifQ==';
        $deserializedMockedObject = new JpmObject(
            origin: 'http://host-ipd.test',
            data:  'def50200f7cfaef632713dc64ca0641225652ddd3f821b2ebfdb5c02997ec4fe41e0b723cf0c49506ad9f38d834f5459368afa7db90344fa41606d0ecb3fddacd850adc46269c9e116b16c68f81c8917f2fe3701b4d66aaa2ef71b588d449163c8ae930a7fe0889257240ec145816d852e4536e1',
            checksum: 2731256195
        );

        $expectedObject = new JpmObject(
            origin: 'http://host-ipd.test',
            data:  '4347ccee90adc0916c44e72758cb9f5a',
            checksum: 2731256195
        );
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->method('deserialize')->willReturn($deserializedMockedObject);
        $transformer = $this->getSessionDataJpmObjectTransformer($serializer);
        $jpmObject = $transformer->transform($base64Input);

        $this->assertInstanceOf(JpmObject::class,$jpmObject);
        $this->assertEquals($jpmObject->data,$expectedObject->data);
        $this->assertEquals($jpmObject->checksum,$expectedObject->checksum);
    }

    /**
     * @test
     */
    public function itReturnsErrorWhenHasNoOrigin(){

        $this->expectException(AccessDeniedHttpException::class);

        $base64Input = 'eyJvcmlnaW4iOiJodHRwOlwvXC9ob3N0LWlwZC50ZXN0IiwiZGF0YSI6ImRlZjUwMjAwZTZjZTZlZDk1NWRmYjUzZWIxMjIyZWJkYTliMDI2NjZmNTZhZWEwNzQ5MGI1MzdkNTlhY2JiMzBjYWUwNjE2OWJhNWVlNzg0NDZiYjEzMTg4ODNhNjk4ZDM1NDI5YjlmMzdkMWY1MjM3Y2YxOWUxZjc3MjNlNDgzOTkyMTBmODZmYzAzNjE1ODljYjNkNDY2MjNkMjE3MDY4OWNjZjkyZDZlOWUwMjIzMmQ2ZGIyNzRkMmQ5ZWNjMjUzZDgwOWQ0M2Y1OTlkNGQ2ZGM3NDY3Y2MyN2RiMTMyYmI3MDBkZjUiLCJjaGVja3N1bSI6IjE4ODQwNTYwMTgifQ== ◀eyJvcmlnaW4iOiJodHRwOlwvXC9ob3N0LWlwZC50ZXN0IiwiZGF0YSI6ImRlZjUwMjAwZTZjZTZlZDk1NWRmYjUzZWIxMjIyZWJkYTliMDI2NjZmNTZhZWEwNzQ5MGI1MzdkNTlhY2JiMzBjYWUwNjE2OWJhNWVl';
        $deserializedMockedObject = new JpmObject(
            origin: '',
            data:  'def50200f7cfaef632713dc64ca0641225652ddd3f821b2ebfdb5c02997ec4fe41e0b723cf0c49506ad9f38d834f5459368afa7db90344fa41606d0ecb3fddacd850adc46269c9e116b16c68f81c8917f2fe3701b4d66aaa2ef71b588d449163c8ae930a7fe0889257240ec145816d852e4536e1',
            checksum: 2731256195
        );

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->method('deserialize')->willReturn($deserializedMockedObject);
        $transformer = $this->getSessionDataJpmObjectTransformer($serializer);
        $transformer->transform($base64Input);
    }

    /**
     * @test
     */
    public function itReturnsErrorWhenIdpHostNoMatches(){

        $this->expectException(AccessDeniedHttpException::class);

        $base64Input = 'eyJvcmlnaW4iOiJodHRwOlwvXC9ob3N0LWlwZC50ZXN0IiwiZGF0YSI6ImRlZjUwMjAwZTZjZTZlZDk1NWRmYjUzZWIxMjIyZWJkYTliMDI2NjZmNTZhZWEwNzQ5MGI1MzdkNTlhY2JiMzBjYWUwNjE2OWJhNWVlNzg0NDZiYjEzMTg4ODNhNjk4ZDM1NDI5YjlmMzdkMWY1MjM3Y2YxOWUxZjc3MjNlNDgzOTkyMTBmODZmYzAzNjE1ODljYjNkNDY2MjNkMjE3MDY4OWNjZjkyZDZlOWUwMjIzMmQ2ZGIyNzRkMmQ5ZWNjMjUzZDgwOWQ0M2Y1OTlkNGQ2ZGM3NDY3Y2MyN2RiMTMyYmI3MDBkZjUiLCJjaGVja3N1bSI6IjE4ODQwNTYwMTgifQ== ◀eyJvcmlnaW4iOiJodHRwOlwvXC9ob3N0LWlwZC50ZXN0IiwiZGF0YSI6ImRlZjUwMjAwZTZjZTZlZDk1NWRmYjUzZWIxMjIyZWJkYTliMDI2NjZmNTZhZWEwNzQ5MGI1MzdkNTlhY2JiMzBjYWUwNjE2OWJhNWVl';
        $deserializedMockedObject = new JpmObject(
            origin: 'http://fake-host-ipd.test',
            data:  'def50200f7cfaef632713dc64ca0641225652ddd3f821b2ebfdb5c02997ec4fe41e0b723cf0c49506ad9f38d834f5459368afa7db90344fa41606d0ecb3fddacd850adc46269c9e116b16c68f81c8917f2fe3701b4d66aaa2ef71b588d449163c8ae930a7fe0889257240ec145816d852e4536e1',
            checksum: 2731256195
        );

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->method('deserialize')->willReturn($deserializedMockedObject);
        $transformer = $this->getSessionDataJpmObjectTransformer($serializer);
        $transformer->transform($base64Input);
    }

    /**
     * @test
     */
    public function itReturnsErrorWhenCheckSumNoMatches(){

        $this->expectException(AccessDeniedHttpException::class);

        $base64Input = 'eyJvcmlnaW4iOiJodHRwOlwvXC9ob3N0LWlwZC50ZXN0IiwiZGF0YSI6ImRlZjUwMjAwZTZjZTZlZDk1NWRmYjUzZWIxMjIyZWJkYTliMDI2NjZmNTZhZWEwNzQ5MGI1MzdkNTlhY2JiMzBjYWUwNjE2OWJhNWVlNzg0NDZiYjEzMTg4ODNhNjk4ZDM1NDI5YjlmMzdkMWY1MjM3Y2YxOWUxZjc3MjNlNDgzOTkyMTBmODZmYzAzNjE1ODljYjNkNDY2MjNkMjE3MDY4OWNjZjkyZDZlOWUwMjIzMmQ2ZGIyNzRkMmQ5ZWNjMjUzZDgwOWQ0M2Y1OTlkNGQ2ZGM3NDY3Y2MyN2RiMTMyYmI3MDBkZjUiLCJjaGVja3N1bSI6IjE4ODQwNTYwMTgifQ== ◀eyJvcmlnaW4iOiJodHRwOlwvXC9ob3N0LWlwZC50ZXN0IiwiZGF0YSI6ImRlZjUwMjAwZTZjZTZlZDk1NWRmYjUzZWIxMjIyZWJkYTliMDI2NjZmNTZhZWEwNzQ5MGI1MzdkNTlhY2JiMzBjYWUwNjE2OWJhNWVl';
        $deserializedMockedObject = new JpmObject(
            origin: 'http://host-ipd.test',
            data:  'def50200f7cfaef632713dc64ca0641225652ddd3f821b2ebfdb5c02997ec4fe41e0b723cf0c49506ad9f38d834f5459368afa7db90344fa41606d0ecb3fddacd850adc46269c9e116b16c68f81c8917f2fe3701b4d66aaa2ef71b588d449163c8ae930a7fe0889257240ec145816d852e4536e1',
            checksum: 11111111
        );

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->method('deserialize')->willReturn($deserializedMockedObject);
        $transformer = $this->getSessionDataJpmObjectTransformer($serializer);
        $transformer->transform($base64Input);
    }

    /**
     * @test
     */
    public function itReturnsErrorWhenHasNoJpmObject(){

        $this->expectException(AccessDeniedHttpException::class);
        $base64Input = 'eyJvcmlnaW4iOiJodHRwOlwvXC9ob3N0LWlwZC50ZXN0IiwiZGF0YSI6ImRlZjUwMjAwZTZjZTZlZDk1NWRmYjUzZWIxMjIyZWJkYTliMDI2NjZmNTZhZWEwNzQ5MGI1MzdkNTlhY2JiMzBjYWUwNjE2OWJhNWVlNzg0NDZiYjEzMTg4ODNhNjk4ZDM1NDI5YjlmMzdkMWY1MjM3Y2YxOWUxZjc3MjNlNDgzOTkyMTBmODZmYzAzNjE1ODljYjNkNDY2MjNkMjE3MDY4OWNjZjkyZDZlOWUwMjIzMmQ2ZGIyNzRkMmQ5ZWNjMjUzZDgwOWQ0M2Y1OTlkNGQ2ZGM3NDY3Y2MyN2RiMTMyYmI3MDBkZjUiLCJjaGVja3N1bSI6IjE4ODQwNTYwMTgifQ== ◀eyJvcmlnaW4iOiJodHRwOlwvXC9ob3N0LWlwZC50ZXN0IiwiZGF0YSI6ImRlZjUwMjAwZTZjZTZlZDk1NWRmYjUzZWIxMjIyZWJkYTliMDI2NjZmNTZhZWEwNzQ5MGI1MzdkNTlhY2JiMzBjYWUwNjE2OWJhNWVl';
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->method('deserialize')->willReturn(null);
        $transformer = $this->getSessionDataJpmObjectTransformer($serializer);
        $transformer->transform($base64Input);
    }


}