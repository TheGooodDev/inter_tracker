<?php

namespace App\Controller;

use App\Repository\ClasseRepository;
use App\Repository\ChallengeRepository;
use App\Repository\DonjonRepository;
use App\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

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

    /**
     * Retourne une réponse JSON contenant une party aléatoire, qui contient un donjon aléatoire, un nombre défini en paramètres de classes aléatoires, et un nombre définis en paramètres de challenges aléatoires.
     */
    #[Route('api/party/rand/', name: 'partyrand.get', methods: ['GET'])]
    public function getRandomParty( 
        DonjonRepository $donjonRepository,
        ChallengeRepository $challengeRepository,
        ClasseRepository $classeRepository,
        SerializerInterface $serializer,
        Request $request
    ): JsonResponse {
        $numClasses = $request->get('numClasses',1);
        $numChallenges = $request->get('numChallenges',1);
        $randomDonjon = $donjonRepository->getRandomDonjon();
        $randomClasses = [];
        $randomChallenges = [];
        for ($i = 0; $i < $numClasses; $i++) {
            $randomClasse= $classeRepository->getRandomClasse();
            array_push($randomClasses,$randomClasse);
        }

        for ($i = 0; $i < $numChallenges; $i++) {
            $randomChallenge= $challengeRepository->getRandomChallenge();
            array_push($randomChallenges,$randomChallenge);
        }

        $randomParty = array($randomDonjon, $randomChallenges, $randomClasses);
        $jsonParty = $serializer->serialize($randomParty, 'json',['groups' => 'getParty']);

        return new JsonResponse($jsonParty, Response::HTTP_OK, [], true);
    }
}
