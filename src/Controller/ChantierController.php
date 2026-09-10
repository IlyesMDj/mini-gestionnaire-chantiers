<?php

namespace App\Controller;

use App\Entity\Chantier;
use App\Exception\ChantierDejaTermineException;
use App\Repository\ChantierRepository;
use App\Service\ChantierStatusUpdater;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ChantierController extends AbstractController
{
    #[Route('/', name: 'app_chantier_index', methods: ['GET'])]
    public function index(ChantierRepository $chantiers): Response
    {
        $tousLesChantiers = $chantiers->findAllWithEquipements();
        $termines = array_filter($tousLesChantiers, static fn (Chantier $chantier) => $chantier->estTermine());

        return $this->render('chantier/index.html.twig', [
            'chantiers' => $tousLesChantiers,
            'chantierDemonstration' => reset($termines) ?: null,
        ]);
    }

    #[Route('/chantiers/{id}/terminer', name: 'app_chantier_terminer', methods: ['POST'])]
    public function terminer(Chantier $chantier, Request $request, ChantierStatusUpdater $chantierStatusUpdater): JsonResponse
    {
        // Sans jeton, un site tiers pourrait déclencher ce changement d'état depuis le navigateur de l'utilisateur.
        if (!$this->isCsrfTokenValid('terminer_chantier_' . $chantier->getId(), $request->headers->get('X-CSRF-Token'))) {
            return $this->json(['success' => false, 'message' => 'Session expirée, rechargez la page.'], Response::HTTP_FORBIDDEN);
        }

        try {
            $chantierStatusUpdater->terminer($chantier);
        } catch (ChantierDejaTermineException) {
            // 409 et non 400 : la requête est valide, c'est l'état de la ressource qui interdit l'opération.
            return $this->json(['success' => false, 'message' => 'Ce chantier est déjà terminé.'], Response::HTTP_CONFLICT);
        }

        return $this->json([
            'success' => true,
            'id' => $chantier->getId(),
            'statut' => $chantier->getStatut()->value,
            'statutLabel' => $chantier->getStatut()->label(),
            'statutClasse' => $chantier->getStatut()->badgeClass(),
        ]);
    }
}
