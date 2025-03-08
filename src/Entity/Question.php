<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $texte = null;

    #[ORM\Column(length: 255)]
    private ?string $question = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reponse = null;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Article $article = null;

    #[ORM\Column(nullable: true)]
    private ?bool $etat = false;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTexte(): ?string
    {
        return $this->texte;
    }

    public function setTexte(?string $texte): static
    {
        $this->texte = $texte;

        return $this;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): static
    {
        $this->question = $question;

        return $this;
    }

    public function getReponse(): ?string
    {
        return $this->reponse;
    }

    public function setReponse(?string $reponse): static
    {
        $this->reponse = $reponse;

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
        return $this->question;
    }
}
