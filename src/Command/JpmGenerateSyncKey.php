<?php

namespace JPM\SessionSharingBundle\Command;

use Defuse\Crypto\Key;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'jpm:generate-sync-key',description: 'Generates a random key for Synchronous Crypt by Defuse lib')]
class JpmGenerateSyncKey extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $key = Key::createNewRandomKey();
        echo $key->saveToAsciiSafeString(), "\n";
        return Command::SUCCESS;
    }
}