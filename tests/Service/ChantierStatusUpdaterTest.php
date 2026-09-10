<?php

namespace App\Tests\Service;

use App\Entity\Chantier;
use App\Enum\StatutChantier;
use App\Service\ChantierStatusUpdater;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class ChantierStatusUpdaterTest extends TestCase
{
    public function testTerminerAppliqueLaTransitionPuisPersisteUneSeuleFois(): void
    {
        $chantier = (new Chantier())->setStatut(StatutChantier::EnCours);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('flush');

        (new ChantierStatusUpdater($entityManager))->terminer($chantier);

        $this->assertSame(StatutChantier::Termine, $chantier->getStatut());
    }
}
