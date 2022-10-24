<?php

namespace App\Controller;

use App\Entity\Donjon;
use App\Repository\ChallengeRepository;
use App\Repository\DonjonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class DonjonController extends AbstractController
{
    #[Route('/donjon', name: 'app_donjon')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/DonjonController.php',
        ]);
    }
    #[Route('/api/donjons', name: 'donjon.getAll',methods:['GET'])]
    public function getAllDonjons(
        DonjonRepository $repository,
        SerializerInterface $serializer
    ): JsonResponse {
        $donjon = $repository->findAll();
        $jsonDonjon = $serializer->serialize($donjon, 'json', ['groups' => 'getAllDonjons','getAllChallenge']);
        return new JsonResponse($jsonDonjon, Response::HTTP_OK, [], true);
    }


    #[Route('/api/donjon/{idDonjon}', name: 'donjon.getOne', methods: ['GET'])]
    #[ParamConverter("donjon", options: ["id" => "idDonjon"], class: "App\Entity\Donjon")]
    public function getDonjon(
        Donjon $donjon,
        SerializerInterface $serializer
    ): JsonResponse {
        $jsondonjon = $serializer->serialize($donjon, 'json', ['groups' => 'getAllDonjons','getAllChallenge']);
        return new JsonResponse($jsondonjon, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/api/donjon/{idDonjon}', name: 'donjon.delete', methods: ['DELETE'])]
    #[ParamConverter("donjon", options:["id"=>"idDonjon"], class:"App\Entity\Donjon")]
    public function deletedonjon(
        Donjon $donjon,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $entityManager->remove($donjon);
        $entityManager->flush();
        return new JsonResponse(null,Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/donjons', name: 'donjon.create', methods: ['POST'])]
    public function createDonjon(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ChallengeRepository $repository,
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse {
        $donjon = $serializer->deserialize($request->getContent(), Donjon::class, 'json');
        $donjon->setStatus(true);

        $content = $request->toArray();
        $challenge = $content["idChallenge"];

        $donjon->setChallenges($repository->find($challenge));

        $entityManager->persist($donjon);
        $entityManager->flush();

        $location = $urlGenerator->generate("donjon.getOne",['idDonjon' => $donjon->getId()], UrlGeneratorInterface::ABSOLUTE_URL);


        $jsondonjon = $serializer->serialize($donjon, 'json', ["groups"=>'getDonjon']);
        return new JsonResponse($jsondonjon,Response::HTTP_CREATED,["Location"=>$location],true);
    }

    #[Route('/api/donjon/{idDonjon}', name: 'donjon.update', methods: ['PUT'])]
    #[ParamConverter("donjon", options:["id"=>"idDonjon"], class:"App\Entity\Donjon")]
    public function updateDonjon(
        
        Donjon $donjon,
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ChallengeRepository $repository,
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse {
        $donjon = $serializer->deserialize(
            $request->getContent(),
            Donjon::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE=>$donjon]
        );
        $donjon->setStatus(true);
        $content = $request->toArray();
        $challenge = $content["idChallenge"];

        $donjon->setChallenges($repository->find($challenge));

        $entityManager->persist($donjon);
        $entityManager->flush();
        
        $location = $urlGenerator->generate("donjon.getOne",['idDonjon' => $donjon->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        
        $jsondonjon = $serializer->serialize($donjon, 'json', ['groups'=>'getDonjon']);
        return new JsonResponse($jsondonjon,Response::HTTP_CREATED,["Location"=>$location],true);
    }
}
