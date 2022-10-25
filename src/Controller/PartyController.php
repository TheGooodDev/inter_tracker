<?php

namespace App\Controller;

use App\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class PartyController extends AbstractController
{
    #[Route('api/party', name: 'app_party')]
    public function index(
        PlayerRepository $playerRepository
    ): JsonResponse
    {
        dd($playerRepository->findRandomPlayers(5));
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PartyController.php',
        ]);
    }
}
