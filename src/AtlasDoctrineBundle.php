<?php

namespace Ariga;

require_once "Command.php";

use AtlasCommand;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerInterface;


class AtlasDoctrineBundle extends DoctrineBundle
{

    public function registerCommands(Application $application): void
    {
        $doctrine = $this->container->get('doctrine', ContainerInterface::NULL_ON_INVALID_REFERENCE);
        if ($doctrine === null) {
            $application->add(new AtlasCommand());
            return;
        }
        assert($doctrine instanceof Registry);
        $em = $doctrine->getManager();
        $config= $em->getConfiguration();
        $application->add(new AtlasCommand($config));
    }
}