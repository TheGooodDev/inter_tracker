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
use Symfony\Component\Serializer\SerializerInterface;

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

    #[Route('/api/challenges', name: 'challenge.getAll',methods:['GET'])]
    public function getAllChallenges(
        ChallengeRepository $repository,
        SerializerInterface $serializer,
        Request $request
    ): JsonResponse {
        $page = $request->get('page',1);
        $limit = $request->get('limit',5);
        $limit = $limit > 20 ? 20 : $limit;
        $challenge = $repository->findWithPagination($page,$limit);
        $jsonChallenges = $serializer->serialize($challenge, 'json', ['groups' => 'getAllChallenges']);
        return new JsonResponse($jsonChallenges, Response::HTTP_OK, [], true);
    }


    #[Route('/api/challenge/{idChallenge}', name: 'challenge.getOne', methods: ['GET'])]
    #[ParamConverter("challenge", options: ["id" => "idChallenge"], class: "App\Entity\Challenge")]
    public function getChallenge(
        Challenge $challenge,
        SerializerInterface $serializer
    ): JsonResponse {
        $jsonChallenge = $serializer->serialize($challenge, 'json', ['groups' => 'getChallenge']);
        return new JsonResponse($jsonChallenge, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/api/challenge/{idChallenge}', name: 'challenge.delete', methods: ['DELETE'])]
    #[ParamConverter("challenge", options:["id"=>"idChallenge"], class:"App\Entity\Challenge")]
    #[IsGranted('ROLE_ADMIN',message: 'Acces deny, you need an elevation')]
    public function deleteChallenge(
        Challenge $challenge,
        EntityManagerInterface $entityManager
    ): JsonResponse {
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
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse {
        $challenge = $serializer->deserialize($request->getContent(), Challenge::class, 'json');

        $entityManager->persist($challenge);
        $entityManager->flush();

        $location = $urlGenerator->generate("challenge.getOne",['idChallenge' => $challenge->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $jsonchallenge = $serializer->serialize($challenge, 'json', ['getChallenge']);
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
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse {
        $challenge = $serializer->deserialize(
            $request->getContent(),
            challenge::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE=>$challenge]
        );
        $content = $request->toArray();

        $entityManager->persist($challenge);
        $entityManager->flush();
        
        $location = $urlGenerator->generate("challenge.getOne",['idChallenge' => $challenge->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        
        $jsonchallenge = $serializer->serialize($challenge, 'json', ['groups'=>'getChallenge']);
        return new JsonResponse($jsonchallenge,Response::HTTP_CREATED,["Location"=>$location],true);
    }
}
