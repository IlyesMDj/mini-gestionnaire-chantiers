<?php

namespace App\DataFixtures;

use App\Entity\Chantier;
use App\Entity\Equipement;
use App\Enum\StatutChantier;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class AppFixtures extends Fixture
{
    private const CATALOGUE_EQUIPEMENTS = [
        'echafaudage' => ['nom' => 'Échafaudage 10m', 'quantite' => 12],
        'betonniere' => ['nom' => 'Bétonnière 160L', 'quantite' => 4],
        'nacelle' => ['nom' => 'Nacelle élévatrice', 'quantite' => 2],
        'marteauPiqueur' => ['nom' => 'Marteau-piqueur', 'quantite' => 6],
        'compresseur' => ['nom' => 'Compresseur thermique', 'quantite' => 3],
        'benne' => ['nom' => 'Benne à gravats 8m³', 'quantite' => 5],
    ];

    private const CHANTIERS = [
        [
            'nom' => 'Démolition Ancien Entrepôt',
            'adresse' => '3 boulevard Solférino, 35000 Rennes',
            'dateDebut' => '2026-06-02',
            'statut' => StatutChantier::Termine,
            'equipements' => ['marteauPiqueur', 'benne', 'compresseur'],
        ],
        [
            'nom' => 'Rénovation Façade Haussmannienne',
            'adresse' => '14 rue de Rennes, 35000 Rennes',
            'dateDebut' => '2026-08-18',
            'statut' => StatutChantier::EnCours,
            'equipements' => ['echafaudage', 'nacelle', 'compresseur'],
        ],
        [
            'nom' => 'Construction Hangar Agricole',
            'adresse' => 'ZA des Landes, 35130 La Guerche-de-Bretagne',
            'dateDebut' => '2026-09-15',
            'statut' => StatutChantier::EnAttente,
            'equipements' => ['betonniere', 'benne'],
        ],
        [
            'nom' => 'Réfection Toiture École Primaire',
            'adresse' => '27 avenue des Lilas, 35700 Rennes',
            'dateDebut' => '2026-09-22',
            'statut' => StatutChantier::EnAttente,
            'equipements' => ['echafaudage', 'nacelle'],
        ],
    ];

    public function load(ObjectManager $objectManager): void
    {
        $equipements = $this->creerEquipements($objectManager);
        $this->creerChantiers($objectManager, $equipements);

        $objectManager->flush();
    }

    private function creerEquipements(ObjectManager $objectManager): array
    {
        $equipements = [];

        foreach (self::CATALOGUE_EQUIPEMENTS as $reference => $definition) {
            $equipement = (new Equipement())
                ->setNom($definition['nom'])
                ->setQuantite($definition['quantite']);

            $objectManager->persist($equipement);
            $equipements[$reference] = $equipement;
        }

        return $equipements;
    }

    private function creerChantiers(ObjectManager $objectManager, array $equipements): void
    {
        foreach (self::CHANTIERS as $definition) {
            $chantier = (new Chantier())
                ->setNom($definition['nom'])
                ->setAdresse($definition['adresse'])
                ->setDateDebut(new \DateTimeImmutable($definition['dateDebut']))
                ->setStatut($definition['statut']);

            foreach ($definition['equipements'] as $reference) {
                $chantier->addEquipement($equipements[$reference]);
            }

            $objectManager->persist($chantier);
        }
    }
}
