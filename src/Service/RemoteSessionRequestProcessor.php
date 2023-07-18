<?php

namespace JPM\SessionSharingBundle\Service;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RemoteSessionRequestProcessor
{
    public function __construct(
        private SessionUrlResponseGenerator $sessionUrlResponseGenerator,
        private HostService           $hostManager,
        private TokenStorageInterface $tokenStorage,
    ){
    }

    public function process(Request $request):RedirectResponse|null
    {
        $isFromKnownHost = $this->hostManager->isFromKnownHost($request);
        $hasLocalSession = $request->getSession()->getId() && $this->tokenStorage->getToken();
        if ($isFromKnownHost && $hasLocalSession) {
            return new RedirectResponse(
                url: $this->sessionUrlResponseGenerator->generate($request)
            );
        }
        return null;
    }

}