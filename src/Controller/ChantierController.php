<?php

namespace App\Controller;

use App\Repository\ChantierRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ChantierController extends AbstractController
{
    #[Route('/', name: 'app_chantier_index', methods: ['GET'])]
    public function index(ChantierRepository $chantiers): Response
    {
        return $this->render('chantier/index.html.twig', [
            'chantiers' => $chantiers->findAllWithEquipements(),
        ]);
    }
}
