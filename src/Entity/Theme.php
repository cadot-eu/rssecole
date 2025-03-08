<?php

namespace App\Entity;

use App\Repository\ThemeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ThemeRepository::class)]
class Theme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    /**
     * @var Collection<int, Flux>
     */
    #[ORM\OneToMany(targetEntity: Flux::class, mappedBy: 'theme', cascade: ['persist', 'remove'])]
    private Collection $fluxs;

    public function __construct()
    {
        $this->fluxs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection<int, Flux>
     */
    public function getFluxs(): Collection
    {
        //on range les fluxs par domaine
        $fluxsArray = $this->fluxs->toArray();
        usort($fluxsArray, function (Flux $a, Flux $b) {
            return $a->getDomaine() <=> $b->getDomaine();
        });
        $this->fluxs = new ArrayCollection($fluxsArray);
        return $this->fluxs;
    }

    public function addFlux(Flux $flux): static
    {
        if (!$this->fluxs->contains($flux)) {
            $this->fluxs->add($flux);
            $flux->setTheme($this);
        }

        return $this;
    }

    public function removeFlux(Flux $flux): static
    {
        if ($this->fluxs->removeElement($flux)) {
            // set the owning side to null (unless already changed)
            if ($flux->getTheme() === $this) {
                $flux->setTheme(null);
            }
        }

        return $this;
    }
    public function removeFluxs(Collection $fluxs): static
    {
        foreach ($fluxs as $flux) {
            $this->removeFlux($flux);
        }
        return $this;
    }
    public function countArticles(): int
    {
        $count = 0;
        foreach ($this->fluxs as $flux) {
            $count += count($flux->getArticlesEtatNonLu());
        }
        return $count;
    }
    public function getArticlesSansPriorite(): array
    {
        $articles = [];
        foreach ($this->fluxs as $flux) {
            foreach ($flux->getArticles() as $article) {
                if (!$article->getPriorite()) {
                    $articles[] = $article;
                }
            }
        }
        return $articles;
    }
    public function __toString(): string
    {
        //on récupère les propriétés de l'objet
        $props = get_object_vars($this);
        return json_encode($props);
    }
}
