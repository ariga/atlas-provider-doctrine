<?php

namespace entities\uppercase;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class UserCommands
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int|null $id = null;

    #[ORM\Column(type: 'string')]
    private string $command;
}
