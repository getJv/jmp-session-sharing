<?php

namespace JPM\SessionSharingBundle\Service;

use Symfony\Component\HttpKernel\Controller\ErrorController;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Helps to encrypt and decrypt from known hosts request
 */
class JpmSessionClient
{
    public function __construct(
        private RemoteSessionRequestProcessor $remoteSessionRequestProcessor,
        private SessionRefreshProcessor $sessionRefreshProcessor
    ){

    }

    /**
     * Used by IDP watch to handle remote auth requests
     * @param ResponseEvent $event
     * @return void
     */
    public function watchRemoteSessionRequest(ResponseEvent $event):void{

        $request = $event->getRequest();
        $redirectResponse = $this->remoteSessionRequestProcessor->process($request);
        $redirectResponse?->send();
    }

    /**
     * Used by remote App to create/refresh refresh a session
     * @param ControllerEvent $event
     * @return void
     */
    public function watchSessionRefresh(ControllerEvent $event){

        $unknownController = $event->getController() instanceof ErrorController;
        if($unknownController){
            return;
        }

        $request = $event->getRequest();
        $response = $this->sessionRefreshProcessor->process($request);
        $response?->send();
    }
}