<?php

namespace App\Controller;

use App\Entity\Donjon;
use App\Repository\ChallengeRepository;
use App\Repository\DonjonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
        SerializerInterface $serializer,
        Request $request
    ): JsonResponse {
        $page = $request->get('page',1);
        $limit = $request->get('limit',5);
        $limit = $limit > 20 ? 20 : $limit;
        $donjon = $repository->findWithPagination($page,$limit);
        $jsonDonjon = $serializer->serialize($donjon, 'json', ['groups' => 'getAllDonjons']);
        return new JsonResponse($jsonDonjon, Response::HTTP_OK, [], true);
    }

    /**
     * Retourne une réponse JSON contenant un donjon aléatoire.
     */
    #[Route('api/donjon/rand', name: 'donjonrand.get', methods: ['GET'])]
    public function getRandomDonjon( 
        DonjonRepository $repository,
        SerializerInterface $serializer,
        Request $request
    ): JsonResponse {
        $randomDonjon = $repository->getRandomDonjon();
        $jsonClasse = $serializer->serialize($randomDonjon, 'json');
        return new JsonResponse($jsonClasse, Response::HTTP_OK, [], true);
    }


    #[Route('/api/donjon/{idDonjon}', name: 'donjon.getOne', methods: ['GET'])]
    #[ParamConverter("donjon", options: ["id" => "idDonjon"], class: "App\Entity\Donjon")]
    public function getDonjon(
        Donjon $donjon,
        SerializerInterface $serializer
    ): JsonResponse {
        $jsondonjon = $serializer->serialize($donjon, 'json', ['groups' => 'getDonjon']);
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
    #[IsGranted('ROLE_ADMIN',message: 'Noooooooooooooooooo')]
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
