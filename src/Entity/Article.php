<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Marque;
use App\Entity\Pub;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 512)]
    private ?string $url = null;

    #[ORM\ManyToOne(inversedBy: 'articles', cascade: ['persist'])]
    private ?Flux $flux = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $etat = "non lu";

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $infos = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sitename = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $author = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\Column(length: 512, nullable: true)]
    private ?string $image = null;

    /**
     * @var Collection<int, Marque>
     */
    #[ORM\OneToMany(targetEntity: Marque::class, mappedBy: 'article', orphanRemoval: true, cascade: ['persist'])]
    #[ORM\OrderBy(['style' => 'ASC'])]
    private Collection $marques;

    /**
     * @var Collection<int, Question>
     */
    #[ORM\OneToMany(targetEntity: Question::class, mappedBy: 'article', orphanRemoval: true, cascade: ['persist'])]
    private Collection $questions;

    #[ORM\Column(nullable: true)]
    private ?int $priorite = 0;

    #[ORM\Column(nullable: true)]
    private ?int $lecturemn = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;



    public function __construct()
    {
        $this->marques = new ArrayCollection();
        $this->questions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return trim($this->url);
    }

    public function setUrl(string $url): static
    {
        $this->url = trim($url);

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

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(?string $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getInfos(): ?array
    {
        return $this->infos;
    }

    public function setInfos(?array $infos): static
    {
        $this->infos = $infos;

        return $this;
    }
    public function getBaseUrl(): ?string
    {
        $parsed_url = parse_url(trim($this->url));
        $host = $parsed_url['host'];
        $base_url = $parsed_url['scheme'] . '://' . $host;
        return $base_url;
    }

    public function getSitename(): ?string
    {
        return $this->sitename;
    }

    public function setSitename(?string $sitename): static
    {
        $this->sitename = $sitename;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int, Marque>
     */
    public function getMarques(): Collection
    {
        return $this->marques;
    }

    public function addMarque(Marque $marque): static
    {
        if (!$this->marques->contains($marque)) {
            $this->marques->add($marque);
            $marque->setArticle($this);
        }

        return $this;
    }

    public function removeMarque(Marque $marque): static
    {
        if ($this->marques->removeElement($marque)) {
            // set the owning side to null (unless already changed)
            if ($marque->getArticle() === $this) {
                $marque->setArticle(null);
            }
        }

        return $this;
    }
    public function removeMarques(): static
    {
        foreach ($this->marques as $marque) {
            $this->removeMarque($marque);
        }
        return $this;
    }
    public function removePubs(): static
    {
        foreach ($this->pubs as $pub) {
            $this->removePub($pub);
        }
        return $this;
    }

    /**
     * @return Collection<int, Question>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): static
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setArticle($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): static
    {
        if ($this->questions->removeElement($question)) {
            // set the owning side to null (unless already changed)
            if ($question->getArticle() === $this) {
                $question->setArticle(null);
            }
        }

        return $this;
    }
    public function removeQuestions(): static
    {
        foreach ($this->questions as $question) {
            $this->removeQuestion($question);
        }
        return $this;
    }

    public function getPriorite(): ?int
    {
        return $this->priorite;
    }

    public function setPriorite(?int $priorite): static
    {
        $this->priorite = $priorite;

        return $this;
    }

    public function getLecturemn(): ?int
    {
        return $this->lecturemn;
    }

    public function setLecturemn(?int $lecturemn): static
    {
        $this->lecturemn = $lecturemn;

        return $this;
    }

    public function getNumberOfMarques(string $mark): int
    {
        $count = 0;
        foreach ($this->marques as $marque) {
            if ($marque->getStyle() === $mark) {
                $count++;
            }
        }
        return $count;
    }
    public function getNumberOfMarquesNonfaites(string $mark): int
    {
        $count = 0;
        foreach ($this->marques as $marque) {
            if ($marque->getStyle() === $mark && $marque->isEtat() !== true) {
                $count++;
            }
        }
        return $count;
    }
    public function getQuestionsCountNonfaites(): int
    {
        $count = 0;
        foreach ($this->questions as $question) {
            if ($question->isEtat() !== true) {
                $count++;
            }
        }
        return $count;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }
    public function __toString(): string
    {
        //on récupère les propriétés de l'objet
        $props = get_object_vars($this);
        $retour = "";
        foreach ($props as $key => $value) {
            if (is_string($value)) {
                $retour .= $key . " : " . strlen($value) . " ; ";
            } elseif (is_object($value)) {
                $retour .= $key . " : " . get_class($value) . " ; ";
            } else {
                $retour .= $key . " : " . json_encode($value) . " ; ";
            }
        }
        return $retour;
    }
}
