<?php

namespace JPM\SessionSharingBundle\Test;

use JPM\SessionSharingBundle\Service\HostService;
use PHPUnit\Framework\TestCase;

class JpmSessionSharingKernelTest extends TestCase
{
    public function testServiceWiring()
    {
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        $container = $kernel->getContainer();
        $bundle = $container->get('JPM\\SessionSharingBundle\\Service\\HostService');
        $this->assertInstanceOf(HostService::class, $bundle);
    }
}