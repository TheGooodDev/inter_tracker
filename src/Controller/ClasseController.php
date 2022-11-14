<?php

namespace App\Controller;

use App\Entity\Classe;
use App\Repository\ClasseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\PictureRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;


class ClasseController extends AbstractController
{
    #[Route('/api/classes', name: 'classe.getAll',methods:['GET'])]
    public function getAllClasse(
        ClasseRepository $repository,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache,
        Request $request
    ): JsonResponse {
        $idCache = "getAllClasse";
        $jsonClasses = $cache->get($idCache, function(ItemInterface $item)use ($repository,$serializer){
            echo "MISE EN CACHE";
            $item->tag("classeCache");
            
            $challenge = $repository->findAll();
            return $serializer->serialize($challenge, 'json', ['groups' => 'getAllClasse']);
        });

        return new JsonResponse($jsonClasses, Response::HTTP_OK, [], true);
    }

    /**
     * Retourne une réponse JSON contenant une classe aléatoire.
     */
    #[Route('api/classe/rand', name: 'classerand.get', methods: ['GET'])]
    public function classe( 
        ClasseRepository $repository,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache,
        Request $request
    ): JsonResponse {

        $challenge = $repository->getRandomClasse();
        $jsonClasses = $serializer->serialize($challenge, 'json', ['groups' => 'getClasse']);

        return new JsonResponse($jsonClasses, Response::HTTP_OK, [], true);
    }


    #[Route('/api/classe/{idClasse}', name: 'classe.getOne', methods: ['GET'])]
    #[ParamConverter("classe", options:["id"=>"idClasse"], class:"App\Entity\classe")]
    public function getClasse(
        Classe $classe,
        SerializerInterface $serializer
    ): JsonResponse {
        $jsonPlayer = $serializer->serialize($classe, 'json');
        return new JsonResponse($jsonPlayer, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/api/classe/{idClasse}', name: 'classe.delete', methods: ['DELETE'])]
    #[ParamConverter("classe", options:["id"=>"idClasse"], class:"App\Entity\classe")]
    #[IsGranted('ROLE_ADMIN',message: 'Acces deny, you need an elevation')]
    public function deletePlayer(
        Classe $classe,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache
    ): JsonResponse {
        $cache->invalidateTags(["classeCache"]);
        $entityManager->remove($classe);
        $entityManager->flush();
        return new JsonResponse(null,Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/classes', name: 'classe.create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN',message: 'Acces deny, you need an elevation')]
    public function createPlayer(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validators,
        UrlGeneratorInterface $urlGenerator,
        TagAwareCacheInterface $cache
    ): JsonResponse {
        $cache->invalidateTags(["classeCache"]);
        $classe = $serializer->deserialize($request->getContent(), Classe::class, 'json');
        $classe->setStatus(true);

        $errors = $validators->validate($classe);
        if($errors->count() > 0){
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST,[],true);
        }
        $entityManager->persist($classe);
        $entityManager->flush();

        $location = $urlGenerator->generate("classe.getOne",['idClasse' => $classe->getId()], UrlGeneratorInterface::ABSOLUTE_URL);


        $jsonPlayer = $serializer->serialize($classe, 'json', ['getClasse']);
        return new JsonResponse($jsonPlayer,Response::HTTP_CREATED,["location"=>$location],false);
    }

    #[Route('/api/classe/{idClasse}', name: 'classe.update', methods: ['PUT'])]
    #[ParamConverter("classe", options:["id"=>"idClasse"], class:"App\Entity\classe")]
    #[IsGranted('ROLE_ADMIN',message: 'Acces deny, you need an elevation')]
    public function updateplayer(
        PictureRepository $pictureRepository,
        classe $classe,
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache
    ): JsonResponse {
        $cache->invalidateTags(["classeCache"]);
        $classe = $serializer->deserialize(
            $request->getContent(),
            classe::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE=>$classe]
        );
        $classe->setStatus(true);
        $content = $request->toArray();

        $classe->setPicture($pictureRepository->find(3));

        $entityManager->persist($classe);
        $entityManager->flush();

        $jsonClasse = $serializer->serialize($classe, 'json', ["groups"=>'getClasse']);
        return new JsonResponse($jsonClasse,Response::HTTP_CREATED,[],true);
    }
}
