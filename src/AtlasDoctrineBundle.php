<?php

require_once "Command.php";

use Doctrine\ORM\Mapping\NamingStrategy;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;


class AtlasDoctrineBundle extends Bundle
{

    public function registerCommands(Application $application): void
    {
        $namingStrategy = $this->container->get('doctrine.orm.naming_strategy', ContainerInterface::NULL_ON_INVALID_REFERENCE);
        if ($namingStrategy === null) {
            $application->add(new AtlasCommand());
            return;
        }
        assert($namingStrategy instanceof NamingStrategy);
        $application->add(new AtlasCommand($namingStrategy));
    }
}