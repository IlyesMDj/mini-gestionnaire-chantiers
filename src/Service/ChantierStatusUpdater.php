<?php

namespace App\Service;

use App\Entity\Chantier;
use Doctrine\ORM\EntityManagerInterface;

final class ChantierStatusUpdater
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function terminer(Chantier $chantier): void
    {
        $chantier->terminer();
        $this->entityManager->flush();
    }
}
