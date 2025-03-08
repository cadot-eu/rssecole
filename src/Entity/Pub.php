<?php

namespace App\Entity;

use App\Repository\PubRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PubRepository::class)]
class Pub
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $html = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $chaine = null;

    #[ORM\ManyToOne(inversedBy: 'pubs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Flux $flux = null;



    public function __construct() {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHtml(): ?string
    {
        return $this->html;
    }

    public function setHtml(string $html): static
    {
        $this->html = $html;

        return $this;
    }



    public function getChaine(): ?array
    {
        return $this->chaine;
    }

    public function setChaine(?array $chaine): static
    {
        $this->chaine = $chaine;

        return $this;
    }

    public function getFlux(): ?Flux
    {
        return $this->flux;
    }

    public function setFlux(?Flux $flux): static
    {
        $this->flux = $flux;

        return $this;
    }
}
