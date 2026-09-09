<?php

namespace App\Entity;

use App\Enum\StatutChantier;
use App\Exception\ChantierDejaTermineException;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Chantier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom du chantier est obligatoire.')]
    private string $nom = '';

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'adresse du chantier est obligatoire.")]
    private string $adresse = '';

    #[ORM\Column(type: 'date_immutable')]
    #[Assert\NotNull(message: 'La date de début est obligatoire.')]
    private ?\DateTimeImmutable $dateDebut = null;

    #[ORM\Column(length: 20, enumType: StatutChantier::class)]
    private StatutChantier $statut = StatutChantier::EnAttente;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getAdresse(): string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeImmutable
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeImmutable $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getStatut(): StatutChantier
    {
        return $this->statut;
    }

    public function setStatut(StatutChantier $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function terminer(): void
    {
        if ($this->statut === StatutChantier::Termine) {
            throw new ChantierDejaTermineException();
        }

        $this->statut = StatutChantier::Termine;
    }
}
