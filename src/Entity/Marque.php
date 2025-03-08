<?php

namespace App\Entity;

use App\Repository\MarqueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MarqueRepository::class)]
class Marque
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $style = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $selection = null;

    #[ORM\ManyToOne(inversedBy: 'marques', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Article $article = null;

    #[ORM\Column(nullable: true)]
    private ?bool $etat = false;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStyle(): ?string
    {
        return \strtolower($this->style);
    }

    public function setStyle(string $style): static
    {
        $this->style = $style;

        return $this;
    }

    public function getSelection(): ?string
    {
        return $this->selection;
    }

    public function setSelection(?string $selection): static
    {
        $this->selection = $selection;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): static
    {
        $this->article = $article;

        return $this;
    }

    public function isEtat(): ?bool
    {
        return $this->etat;
    }

    public function setEtat(?bool $etat): static
    {
        $this->etat = $etat;

        return $this;
    }
    public function __toString(): string
    {
        //on récupère les propriétés de l'objet
        $props = get_object_vars($this);
        return json_encode($props);
    }
}
