<?php

namespace App\Entity;

use App\Repository\FluxRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FluxRepository::class)]
class Flux
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    /**
     * @var Collection<int, Article>
     */
    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'flux', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $articles;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\ManyToOne(inversedBy: 'fluxs', cascade: ['persist', 'remove'])]
    private ?Theme $theme = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $domaine = null;

    /**
     * @var Collection<int, Pub>
     */
    #[ORM\OneToMany(targetEntity: Pub::class, mappedBy: 'flux', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $pubs;

    #[ORM\Column(length: 512, nullable: true)]
    private ?string $basPub = null;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->pubs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $article->setFlux($this);
        }

        return $this;
    }
    public function setArticles(array $articles): static
    {
        foreach ($articles as $article) {
            $this->addArticle($article);
        }
        return $this;
    }

    public function removeArticle(Article $article): static
    {
        if ($this->articles->removeElement($article)) {
            // set the owning side to null (unless already changed)
            if ($article->getFlux() === $this) {
                $article->setFlux(null);
            }
        }

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme(?Theme $theme): static
    {
        $this->theme = $theme;

        return $this;
    }
    public function getDomaine(): ?string
    {
        if (!$this->url) {
            $this->domaine = null;
        }
        $parsedUrl = parse_url($this->url);
        $hosts = $parsedUrl['host'] ?? null;
        //on supprimes les www.
        $this->domaine = str_replace('www.', '', $hosts);
        if (!$this->domaine) {
            $this->domaine = null;
        }
        return $this->domaine;
    }

    public function setDomaine(?string $domaine): static
    {
        $this->domaine = $domaine;

        return $this;
    }

    public function removeArticles(): static
    {
        foreach ($this->articles as $article) {
            $this->removeArticle($article);
        }
        return $this;
    }
    public function removeArticlesSansPriorite(): static
    {
        foreach ($this->articles as $article) {
            if (!$article->getPriorite()) {
                $this->removeArticle($article);
            }
        }
        return $this;
    }

    public function getArticlesByEtat(string $etat): array
    {
        $articles = [];
        foreach ($this->articles as $article) {
            if ($article->getEtat() == $etat) {
                $articles[] = $article;
            }
        }
        return $articles;
    }
    public function getArticlesByPriorite($priorite = 0): array
    {
        $articles = [];
        foreach ($this->articles as $article) {
            if ($article->getPriorite() == $priorite) {
                $articles[] = $article;
            }
        }
        return $articles;
    }
    public function getArticlesByNonPriorite(): array
    {
        $articles = [];
        foreach ($this->articles as $article) {
            if ($article->getPriorite()) {
                $articles[] = $article;
            }
        }
        return $articles;
    }
    public function getArticlesEtatNonLu(): array
    {
        return $this->getArticlesByEtat('non lu');
    }


    /**
     * @return Collection<int, Pub>
     */
    public function getPubs(): Collection
    {
        return $this->pubs;
    }

    public function addPub(Pub $pub): static
    {
        if (!$this->pubs->contains($pub)) {
            $this->pubs->add($pub);
            $pub->setFlux($this);
        }

        return $this;
    }

    public function removePub(Pub $pub): static
    {
        if ($this->pubs->removeElement($pub)) {
            // set the owning side to null (unless already changed)
            if ($pub->getFlux() === $this) {
                $pub->setFlux(null);
            }
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
    public function removeLastPub(): static
    {
        $lastPub = $this->pubs->last();
        if ($lastPub) {
            $this->removePub($lastPub);
        }
        return $this;
    }

    public function getBasPub(): ?string
    {
        return $this->basPub;
    }

    public function setBasPub(?string $basPub): static
    {
        $this->basPub = $basPub;

        return $this;
    }
    public function __toString(): string
    {
        //on récupère les propriétés de l'objet
        $props = get_object_vars($this);
        return json_encode($props);
    }
}
