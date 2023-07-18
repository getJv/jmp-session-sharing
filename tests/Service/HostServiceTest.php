<?php

namespace Service;

use JPM\SessionSharingBundle\Service\HostService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class HostServiceTest extends TestCase
{
    /**
     * @test
     */
    public function itCreatesAHostService(){
        $hostManager = $this->getHostService();
        $this->assertInstanceOf(HostService::class,$hostManager);
    }

    /**
     * @test
     */
    public function itReturnsFalseWhenHasNoKnownHost(){
        $requestMock            = $this->createMock(Request::class);
        $hostManager = $this->getHostService(knownRemoteHosts:"");
        $this->assertFalse($hostManager->isFromKnownHost($requestMock));
    }

    /**
     * @test
     */
    public function itReturnsFalseWhenIsNotFromKnownHost(){
        $hostManager = $this->getHostService();
        $encodedHost = base64_encode('http://fake-remote.test/resourcer?type=any');
        $requestMock            = $this->createMock(Request::class);
        $requestMock->method('get')->willReturn($encodedHost);

        $this->assertFalse($hostManager->isFromKnownHost($requestMock));
    }

    /**
     * @test
     */
    public function itReturnsTrueWhenNotFromKnownHost(){
        $hostManager = $this->getHostService();
        $encodedHost = base64_encode('http://remote.test/resourcer?type=any');
        $requestMock            = $this->createMock(Request::class);
        $requestMock->method('get')->willReturn($encodedHost);

        $this->assertTrue($hostManager->isFromKnownHost($requestMock));
    }

    private function getHostService(
        string $knownRemoteHosts = null,
        string $hostSeparator = null,
        string $hostRegexFinder = null,
        string $attrNameUrlCallback = null
    ):HostService{
        return new HostService(
            knownRemoteHosts: $knownRemoteHosts ?? "remote.test",
            hostSeparator: $hostSeparator ?? ',',
            hostRegexFinder: $hostRegexFinder ?? '^(http:\/\/|https:\/\/)(www\.)?',
            attrNameUrlCallback: $attrNameUrlCallback ?? 'callback'
        );
    }

}