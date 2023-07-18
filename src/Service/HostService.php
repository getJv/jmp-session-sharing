<?php

namespace JPM\SessionSharingBundle\Service;

use Symfony\Component\HttpFoundation\Request;

class HostService
{
    public function __construct(
        private string $knownRemoteHosts,
        private string $hostSeparator,
        private string $hostRegexFinder,
        private string $attrNameUrlCallback
    ){
    }
    public function isFromKnownHost(Request $request): bool{
        $hosts = $this->getKnownHostList();
        $callbackParam = $request->get($this->attrNameUrlCallback);
        if(!$callbackParam || !$hosts ){return false;}
        $callbackUrl = base64_decode($callbackParam);

        foreach ($hosts as $host){
            $isKnown = preg_match('/' . $this->hostRegexFinder . $host .'/i' ,$callbackUrl);
            if($isKnown) {
                return $isKnown;
            }
        }
        return false;
    }

    private function getKnownHostList():array{
        return explode($this->hostSeparator,$this->knownRemoteHosts);
    }

}