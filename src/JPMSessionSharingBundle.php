<?php

namespace JPM\SessionSharingBundle;

use JPM\SessionSharingBundle\DependencyInjection\JpmSessionSharingExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class JPMSessionSharingBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return dirname(__DIR__);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new JpmSessionSharingExtension();
    }

}