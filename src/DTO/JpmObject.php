<?php

namespace JPM\SessionSharingBundle\DTO;

class JpmObject
{
    public function __construct(
        public string $origin,
        public string $data,
        public string $checksum
    ){
    }
}