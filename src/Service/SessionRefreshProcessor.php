<?php

namespace JPM\SessionSharingBundle\Service;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ErrorController;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SessionRefreshProcessor
{
    public function __construct(
        private SessionDataJpmObjectTransformer $sessionDataJpmObjectTransformer,
        private TokenStorageInterface $tokenStorage,
        private string                $attrNameUrlCallback,
        private string                $appUrl,
        private string                $idpUrl,
    ){
    }

    public function process(Request $request):RedirectResponse|null
    {
        $hasSession = $this->tokenStorage?->getToken()?->getUser();

        if($hasSession){
           return null;
        }

        $remoteToken = $request->query->get('token',"");
        if (!$remoteToken) {
            return new RedirectResponse(
                url: $this->idpUrl
                .'?'. $this->attrNameUrlCallback . '='
                . base64_encode($this->appUrl . $request->getPathInfo())
            );
        }

        $jpmObject = $this->sessionDataJpmObjectTransformer->transform($remoteToken);
        $session = $request->getSession();
        $session->setId($jpmObject->data);
        $session->start();
        $session->save();

        return new RedirectResponse(
            url: $request->getPathInfo()
        );

    }

}