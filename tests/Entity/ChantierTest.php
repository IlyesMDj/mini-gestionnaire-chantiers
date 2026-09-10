<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Chantier;
use App\Enum\StatutChantier;
use App\Exception\ChantierDejaTermineException;
use PHPUnit\Framework\TestCase;

final class ChantierTest extends TestCase
{
    public function testTerminerFaitPasserLeStatutATermine(): void
    {
        $chantier = (new Chantier())->setStatut(StatutChantier::EnCours);

        $chantier->terminer();

        $this->assertSame(StatutChantier::Termine, $chantier->getStatut());
    }

    public function testTerminerUnChantierDejaTermineLeveUneException(): void
    {
        $chantier = (new Chantier())->setStatut(StatutChantier::Termine);

        $this->expectException(ChantierDejaTermineException::class);

        $chantier->terminer();
    }
}
