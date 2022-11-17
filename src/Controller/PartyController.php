<?php

namespace App\Controller;

use App\Entity\Donjon;
use App\Entity\Challenge;
use App\Entity\Classe;
use App\Repository\ClasseRepository;
use App\Repository\ChallengeRepository;
use App\Repository\DonjonRepository;
use App\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
// use Symfony\Component\Serializer\SerializerInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Items;
/**
* @OA\Tag(name="Party")
*/
class PartyController extends AbstractController
{
    /**
     * Retourne une réponse JSON contenant une party aléatoire, qui contient un donjon aléatoire, un nombre défini en paramètres de classes aléatoires, et un nombre définis en paramètres de challenges aléatoires.
     * @OA\Response(
     *      response=200,
     *      description="OK",
     *      @OA\JsonContent(
     *          @OA\Property(
     *              property="donjon",
     *              type="array",
     *              @OA\Items(ref=@Model(type=Donjon::class, groups={"getParty"}))
     *              
     *          ),
     *          @OA\Property(
     *              property="challenge",
     *              type="array",
     *              @OA\Items(ref=@Model(type=Challenge::class, groups={"getParty"}))
     *          ),
     *          @OA\Property(
     *              property="classe",
     *              type="array",
     *              @OA\Items(ref=@Model(type=Classe::class, groups={"getParty"}))
     *          ),
     *      )
     * )
     * @param int $numClasses
     * @param int $numChallenges
     * @return JsonResponse
     */
    #[Route('api/party/rand/{numClasses}/{numChallenges}', name: 'partyrand.get', methods: ['GET'])]
    public function getRandomParty( 
        DonjonRepository $donjonRepository,
        ChallengeRepository $challengeRepository,
        ClasseRepository $classeRepository,
        SerializerInterface $serializer,
        int $numClasses,
        int $numChallenges
    ): JsonResponse {
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
        $context = SerializationContext::create()->setGroups("getParty");
        $jsonParty = $serializer->serialize($randomParty, 'json',$context);

        return new JsonResponse($jsonParty, Response::HTTP_OK, [], true);
    }
}
