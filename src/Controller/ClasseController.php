<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ClasseRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Classe;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PictureRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class ClasseController extends AbstractController
{
    #[Route('/classe', name: 'app_classe',methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ClasseController.php',
        ]);
    }

    /**
     * Retourne une réponse JSON contenant une classe aléatoire.
     */
    #[Route('api/classe/rand', name: 'classerand.get', methods: ['GET'])]
    public function classe( 
        ClasseRepository $repository,
        SerializerInterface $serializer,
        Request $request
    ): JsonResponse {
        $randomClasse = $repository->getRandomClasse();
        $jsonClasse = $serializer->serialize($randomClasse, 'json');
        return new JsonResponse($jsonClasse, Response::HTTP_OK, [], true);
    }

    #[Route('api/classe', name: 'classe.create', methods: ['POST'])]
    #[IsGranted('ROLE_USER', message:'Autorisation insuffisante.')]
    public function createDonjon(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ClasseRepository $repository,
        UrlGeneratorInterface $urlGenerator,
        PictureRepository $pictureRepository
    ): JsonResponse {
        $classe = $serializer->deserialize($request->getContent(), Classe::class, 'json');
        $classe->setStatus(true);

        $content = $request->toArray();

        $classe->setPicture($pictureRepository->find(3));

        $entityManager->persist($classe);
        $entityManager->flush();

        $jsonClasse = $serializer->serialize($classe, 'json', ["groups"=>'getClasse']);
        return new JsonResponse($jsonClasse,Response::HTTP_CREATED,[],true);
    }
}
