<?php

namespace Service;

use JPM\SessionSharingBundle\DTO\JpmObject;
use JPM\SessionSharingBundle\Service\SessionDataJpmObjectTransformer;
use JPM\SessionSharingBundle\Service\SessionRefreshProcessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;


class SessionRefreshProcessorTest extends TestCase
{

    public function getRefreshProcessor(
         SessionDataJpmObjectTransformer $transformer = null,
         TokenStorageInterface $tokenStorage = null,
         string                $attrNameUrlCallback = null,
         string                $appUrl  = null,
         string                $idpUrl  = null,
    ):SessionRefreshProcessor{


        $transformerMock = $this->createMock(SessionDataJpmObjectTransformer::class);
        $tokenStorageMock = $this->createMock(TokenStorage::class);

        return new SessionRefreshProcessor(
            sessionDataJpmObjectTransformer: $transformer ?? $transformerMock,
            tokenStorage:$tokenStorage ?? $tokenStorageMock,
            attrNameUrlCallback: $attrNameUrlCallback ?? 'callback',
            appUrl: $appUrl ?? 'http://remote.test',
            idpUrl: $idpUrl ?? 'http://host-idp.test/login',
        );
    }


    /**
     * @test
     */
    public function itSessionRequestProcessor(){
        $hostManager = $this->getRefreshProcessor();
        $this->assertInstanceOf(SessionRefreshProcessor::class,$hostManager);
    }



    /**
     * @test
     */
    public function itReturnsNullWhenHasSession(){

        $userMock = $this->createMock(UserInterface::class);

        $tokenMock = $this->createMock(TokenInterface::class);
        $tokenMock->method('getUser')->willReturn($userMock);

        $tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $tokenStorageMock->method('getToken')->willReturn($tokenMock);

        $hostManager = $this->getRefreshProcessor(tokenStorage:$tokenStorageMock);
        $requestMock = $this->createMock(Request::class);
        $result = $hostManager->process($requestMock);
        $this->assertNull($result);

    }

    /**
     * @test
     */
    public function itReturnsLoginRedirectWhenNoSessionIsFound(){

        $hostManager = $this->getRefreshProcessor();
        $requestMock = $this->createMock(Request::class);
        $queryBagMock = $this->createMock(ParameterBag::class);
        $requestMock->query = $queryBagMock;
        $result = $hostManager->process($requestMock);
        $this->assertInstanceOf(RedirectResponse::class,$result);
        $this->assertEquals(
            'http://host-idp.test/login?callback=aHR0cDovL3JlbW90ZS50ZXN0',
            $result->getTargetUrl()
        );

    }

    /**
     * @test
     */
    public function itReturnsARedirectWhenSessionIsCreated(){


        $requestMock = $this->createMock(Request::class);
        $requestMock->method('getPathInfo')->willReturn('http://remote.test');
        $queryBagMock = $this->createMock(ParameterBag::class);
        $queryBagMock->method('get')->willReturn('1234');
        $requestMock->query = $queryBagMock;

        $SDJOTransformer = $this->createMock(SessionDataJpmObjectTransformer::class);
        $SDJOTransformer->method('transform')
            ->willReturn(new JpmObject('','',''));

        $hostManager = $this->getRefreshProcessor(
            transformer: $SDJOTransformer
        );
        $result = $hostManager->process($requestMock);
        $this->assertInstanceOf(RedirectResponse::class,$result);
        $this->assertEquals(
            'http://remote.test',
            $result->getTargetUrl()
        );

    }






}