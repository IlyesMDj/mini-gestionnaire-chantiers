<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Chantier;
use App\Enum\StatutChantier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class ChantierStatusControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private int $chantierId;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $chantier = (new Chantier())
            ->setNom('Chantier de test')
            ->setAdresse('1 rue du Test, 35000 Rennes')
            ->setDateDebut(new \DateTimeImmutable('2026-09-01'))
            ->setStatut(StatutChantier::EnCours);

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($chantier);
        $entityManager->flush();

        $this->chantierId = $chantier->getId();
    }

    protected function tearDown(): void
    {
        static::getContainer()->get(EntityManagerInterface::class)
            ->createQuery('DELETE FROM App\Entity\Chantier chantier WHERE chantier.id = :id')
            ->setParameter('id', $this->chantierId)
            ->execute();

        parent::tearDown();
    }

    public function testTerminerRepond200EtRenvoieLeNouveauStatut(): void
    {
        $this->envoyerTerminaison($this->jetonDeLaPage());

        $this->assertResponseIsSuccessful();
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertSame('termine', $this->reponseDecodee()['statut']);
    }

    public function testSecondeTerminaisonRepond409(): void
    {
        $jeton = $this->jetonDeLaPage();
        $this->envoyerTerminaison($jeton);

        $this->envoyerTerminaison($jeton);

        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        $this->assertSame('Ce chantier est déjà terminé.', $this->reponseDecodee()['message']);
    }

    public function testTerminaisonSansJetonCsrfRepond403(): void
    {
        $this->client->request('POST', $this->urlDeTerminaison());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testTerminaisonEnGetRepond405(): void
    {
        $this->client->request('GET', $this->urlDeTerminaison());

        $this->assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
    }

    private function urlDeTerminaison(): string
    {
        return sprintf('/chantiers/%d/terminer', $this->chantierId);
    }

    private function jetonDeLaPage(): string
    {
        return $this->client->request('GET', '/')
            ->filter(sprintf('[data-chantier-id="%d"] [data-terminer-btn]', $this->chantierId))
            ->attr('data-csrf');
    }

    private function envoyerTerminaison(string $jetonCsrf): void
    {
        $this->client->request('POST', $this->urlDeTerminaison(), [], [], [
            'HTTP_X_CSRF_TOKEN' => $jetonCsrf,
        ]);
    }

    private function reponseDecodee(): array
    {
        return json_decode($this->client->getResponse()->getContent(), true);
    }
}
