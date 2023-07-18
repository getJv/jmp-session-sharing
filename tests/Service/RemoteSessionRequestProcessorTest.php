<?php

namespace Service;

use JPM\SessionSharingBundle\Service\HostService;
use JPM\SessionSharingBundle\Service\JpmSessionClient;
use JPM\SessionSharingBundle\Service\RemoteSessionRequestProcessor;
use JPM\SessionSharingBundle\Service\SessionUrlResponseGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class RemoteSessionRequestProcessorTest extends TestCase
{

    public function getSessionRequestProcessor(
        SessionUrlResponseGenerator $responseGenerator = null,
        HostService           $hostManager = null,
        TokenStorageInterface $tokenStorage = null,
    ):RemoteSessionRequestProcessor{


        $generatorMock = $this->createMock(SessionUrlResponseGenerator::class);
        $hostManagerMock = $this->createMock(HostService::class);
        $tokenStorageMock = $this->createMock(TokenStorageInterface::class);

        return new RemoteSessionRequestProcessor(
            sessionUrlResponseGenerator: $responseGenerator ?? $generatorMock,
            hostManager: $hostManager ?? $hostManagerMock,
            tokenStorage: $tokenStorage ?? $tokenStorageMock
        );
    }


    /**
     * @test
     */
    public function itSessionRequestProcessor(){
        $hostManager = $this->getSessionRequestProcessor();
        $this->assertInstanceOf(RemoteSessionRequestProcessor::class,$hostManager);
    }

    /**
     * @test
     */
    public function itReturnsNullWhenUnknownHost(){

        //Request/sessionMock
        $sessionMock = $this->createMock( SessionInterface::class);
        $sessionMock->method('getId')->willReturn('1');
        $requestMock = $this->createMock(Request::class);
        $requestMock->method('getSession')->willReturn($sessionMock);

        $hostManagerMock = $this->createMock(HostService::class);
        $hostManagerMock->method('isFromKnownHost')->willReturn(false);

        $tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $tokenStorageMock->method('getToken')->willReturn(null);

        $processor = $this->getSessionRequestProcessor(
            hostManager: $hostManagerMock,
            tokenStorage: $tokenStorageMock
        );


        $this->assertNull($processor->process($requestMock));
    }

    /**
     * @test
     */
    public function itReturnsNullWhenHostIsKnownButHasNoSession(){

        //Request/sessionMock
        $sessionMock = $this->createMock( SessionInterface::class);
        $sessionMock->method('getId')->willReturn('1');
        $requestMock = $this->createMock(Request::class);
        $requestMock->method('getSession')->willReturn($sessionMock);

        $hostManagerMock = $this->createMock(HostService::class);
        $hostManagerMock->method('isFromKnownHost')->willReturn(true);

        $tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $tokenStorageMock->method('getToken')->willReturn(null);

        $processor = $this->getSessionRequestProcessor(
            hostManager: $hostManagerMock,
            tokenStorage: $tokenStorageMock
        );


        $this->assertNull($processor->process($requestMock));
    }

    /**
     * @test
     */
    public function itReturnsResponseRedirect(){

        //Request/sessionMock
        $sessionMock = $this->createMock( SessionInterface::class);
        $sessionMock->method('getId')->willReturn('1');
        $requestMock = $this->createMock(Request::class);
        $requestMock->method('getSession')->willReturn($sessionMock);

        $hostManagerMock = $this->createMock(HostService::class);
        $hostManagerMock->method('isFromKnownHost')->willReturn(true);

        $tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $tokenInterfaceMock = $this->createMock(TokenInterface::class);
        $tokenStorageMock->method('getToken')->willReturn($tokenInterfaceMock);

        $urlGenerationMock = $this->createMock(SessionUrlResponseGenerator::class);
        $urlGenerationMock->method('generate')
            ->willReturn('http://test.com');

        $processor = $this->getSessionRequestProcessor(
            responseGenerator: $urlGenerationMock,
            hostManager: $hostManagerMock,
            tokenStorage: $tokenStorageMock,
        );


        $this->assertInstanceOf(RedirectResponse::class,
            $processor->process($requestMock));
    }




}