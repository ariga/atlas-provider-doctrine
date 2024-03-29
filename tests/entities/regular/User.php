<?php

namespace entities\regular;

use Bug;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int|null $id = null;

    #[ORM\Column(type: 'string')]
    private string $name;

    /** @var Collection<int, Bug> An ArrayCollection of Bug objects. */
    #[ORM\OneToMany(targetEntity: Bug::class, mappedBy: 'reporter')]
    private Collection $reportedBugs;

    /** @var Collection<int,Bug> An ArrayCollection of Bug objects. */
    #[ORM\OneToMany(targetEntity: Bug::class, mappedBy: 'engineer')]
    private $assignedBugs;
}
