<?php

// Dans votre entité Reponse

namespace App\Entity;

use App\Repository\ReponseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReponseRepository::class)]
class Reponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $reponse = null;

    #[ORM\ManyToOne(inversedBy: 'reponses')]
    private ?Question $idQuestion = null;

    #[ORM\Column(type: 'boolean')]
    private bool $correct; // Ajoutez cette ligne pour la propriété "correct"

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReponse(): ?string
    {
        return $this->reponse;
    }

    public function setReponse(string $reponse): static
    {
        $this->reponse = $reponse;

        return $this;
    }

    public function getIdQuestion(): ?Question
    {
        return $this->idQuestion;
    }

    public function __toString()
    {
        return $this->idQuestion;
    }

    public function setIdQuestion(?Question $idQuestion): static
    {
        $this->idQuestion = $idQuestion;

        return $this;
    }

    public function isCorrect(): bool 
    {
        return $this->correct;
    }

    public function setCorrect(bool $correct): self 
    {
        $this->correct = $correct;

        return $this;
    }
}
