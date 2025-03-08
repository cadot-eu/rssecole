<?php

namespace App\Message;

class LoadArticlesMessage
{
    private int $themeId;
    private ?\DateTimeInterface $completedAt = null;  // Ajout d'une propriété pour marquer la fin

    public function __construct(int $themeId)
    {
        $this->themeId = $themeId;
    }

    public function getThemeId(): int
    {
        return $this->themeId;
    }

    // Nouvelle méthode pour définir la date de fin
    public function setCompletedAt(?\DateTimeInterface $completedAt): self
    {
        $this->completedAt = $completedAt;
        return $this;
    }

    // Nouvelle méthode pour obtenir la date de fin
    public function getCompletedAt(): ?\DateTimeInterface
    {
        return $this->completedAt;
    }
}
