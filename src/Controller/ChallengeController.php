<?php

namespace App\Controller;

use App\Entity\Challenge;
use App\Repository\ChallengeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
// use Symfony\Component\Serializer\SerializerInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;


/**
* @OA\Tag(name="Challenges")
*/
class ChallengeController extends AbstractController
{
    #[Route('/challenge', name: 'app_challenge')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ChallengeController.php',
        ]);
    }


    /**
     * Cette méthode permet de récupérer tout l'ensemble des challenges.
     * @OA\Response(
     *      response=200,
     *      description="Retourne la liste des challenges",
     *      @Model(type=Challenge::class)
     * )
     * 
     * 
     * @param ChallengeRepository $challengeRepository
     * @param SerializerInterface $serializer
     * @param TagAware  CacheInterface $cache
     * @return JsonResponse
     * 
     */
    #[Route('/api/challenges', name: 'challenge.getAll',methods:['GET'])]
    public function getAllChallenges(
        ChallengeRepository $repository,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache
    ): JsonResponse {
        $idCache = "getAllChallenges";
        $jsonChallenges = $cache->get($idCache, function(ItemInterface $item)use ($repository,$serializer){
            $item->tag("challengeCache");
            
            $challenge = $repository->findAll();
            $context = SerializationContext::create()->setGroups("getAllChallenges");
            return $serializer->serialize($challenge, 'json',$context);
        });
 
        return new JsonResponse($jsonChallenges, Response::HTTP_OK, [], true);
    }


  
    #[Route('api/challenge/rand', name: 'challengerand.get', methods: ['GET'])]
    public function getRandomChallenge( 
        ChallengeRepository $repository,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache,
        Request $request
    ): JsonResponse {
        $challenge = $repository->getRandomChallenge();
        $context = SerializationContext::create()->setGroups("getChallenge");
        $jsonChallenges = $serializer->serialize($challenge, 'json', $context);
        return new JsonResponse($jsonChallenges, Response::HTTP_OK, [], true);
    }


    #[Route('/api/challenge/{idChallenge}', name: 'challenge.getOne', methods: ['GET'])]
    #[ParamConverter("challenge", options: ["id" => "idChallenge"], class: "App\Entity\Challenge")]
    public function getChallenge(
        Challenge $challenge,
        SerializerInterface $serializer
    ): JsonResponse {
        $context = SerializationContext::create()->setGroups("getChallenge");
        $jsonChallenge = $serializer->serialize($challenge, 'json', $context);
        return new JsonResponse($jsonChallenge, Response::HTTP_OK, ['accept' => 'json'], true);
    }

  
    #[Route('/api/challenge/{idChallenge}', name: 'challenge.delete', methods: ['DELETE'])]
    #[ParamConverter("challenge", options:["id"=>"idChallenge"], class:"App\Entity\Challenge")]
    #[IsGranted('ROLE_ADMIN',message: 'Acces deny, you need an elevation')]
    public function deleteChallenge(
        Challenge $challenge,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache,
    ): JsonResponse {
        $cache->invalidateTags(["challengeCache"]);
        $entityManager->remove($challenge);
        $entityManager->flush();
        return new JsonResponse(null,Response::HTTP_NO_CONTENT);
    }


    #[Route('/api/challenges', name: 'challenge.create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN',message: 'Acces deny, you need an elevation')]
    public function createChallenge(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
        TagAwareCacheInterface $cache
    ): JsonResponse {
        $cache->invalidateTags(["challengeCache"]);
        $challenge = $serializer->deserialize($request->getContent(), Challenge::class, 'json');

        $entityManager->persist($challenge);
        $entityManager->flush();

        $location = $urlGenerator->generate("challenge.getOne",['idChallenge' => $challenge->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $context = SerializationContext::create()->setGroups("getChallenge");
        $jsonchallenge = $serializer->serialize($challenge, 'json', $context);
        return new JsonResponse($jsonchallenge,Response::HTTP_CREATED,["Location"=>$location],false);
    }

    #[Route('/api/challenge/{idChallenge}', name: 'challenge.update', methods: ['PUT'])]
    #[ParamConverter("challenge", options:["id"=>"idChallenge"], class:"App\Entity\Challenge")]
    #[IsGranted('ROLE_ADMIN',message: 'Acces deny, you need an elevation')]
    public function updateChallenge(
        challenge $challenge,
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
        TagAwareCacheInterface $cache
    ): JsonResponse {
        $cache->invalidateTags(["challengeCache"]);
        // $challenge = $serializer->deserialize(
        //     $request->getContent(),
        //     challenge::class,
        //     'json',
        //     [AbstractNormalizer::OBJECT_TO_POPULATE=>$challenge]
        // );

        $updatedChallenge = $serializer->deserialize(
            $request->getContent(),
            Challenge::class,
            'json'
        );

        $challenge->setChallengeName($updatedChallenge->getChallengeName() ? $updatedChallenge->getChallengeName() : $challenge->getChallengeName());
        $challenge->setDescription($updatedChallenge->getDescription() ? $updatedChallenge->getDescription() : $challenge->getDescription());
    
        $entityManager->persist($challenge);
        $entityManager->flush();
        
        $location = $urlGenerator->generate("challenge.getOne",['idChallenge' => $challenge->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        
        $context = SerializationContext::create()->setGroups("getChallenge");
        $jsonchallenge = $serializer->serialize($challenge, 'json', $context);
        return new JsonResponse($jsonchallenge,Response::HTTP_CREATED,["Location"=>$location],true);
    }
}
