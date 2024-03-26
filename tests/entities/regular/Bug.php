<?php

namespace entities\regular;

use BugRepository;
use Doctrine\ORM\Mapping as ORM;
use entities\DateTime;
use User;

#[ORM\Entity(repositoryClass: BugRepository::class)]
#[ORM\Table(name: 'bugs')]
class Bug
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int|null $id = null;

    #[ORM\Column(type: 'string')]
    private string $description;

    #[ORM\Column(type: 'datetime')]
    private DateTime $created;

    #[ORM\Column(type: 'string')]
    private string $status;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'assignedBugs')]
    private User|null $engineer = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reportedBugs')]
    private User|null $reporter;
}
