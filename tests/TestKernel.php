<?php

namespace JPM\SessionSharingBundle\Test;

use JPM\SessionSharingBundle\JPMSessionSharingBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{

    /**
     * @inheritDoc
     */
    public function registerBundles(): iterable
    {
        return [
            new JPMSessionSharingBundle()
        ];
    }

    /**
     * @inheritDoc
     */
    public function registerContainerConfiguration(LoaderInterface $loader):void
    {
        // TODO: Implement registerContainerConfiguration() method.
    }
}