<?php

namespace App\Controller;

use App\Entity\Classe;
use App\Repository\ClasseRepository;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ClasseController extends AbstractController
{

    #[Route('/api/classes', name: 'classe.getAll',methods:['GET'])]
    public function getAllPlayers(
        ClasseRepository $repository,
        SerializerInterface $serializer,
        Request $request
    ): JsonResponse {
        $page = $request->get('page',1);
        $limit = $request->get('limit',18);
        $limit = $limit > 20 ? 20 : $limit;
        $classe = $repository->findAll();
        $jsonPlayers = $serializer->serialize($classe, 'json');
        return new JsonResponse($jsonPlayers, Response::HTTP_OK, [], true);
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
        EntityManagerInterface $entityManager
    ): JsonResponse {
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
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface $validators
    ): JsonResponse {
        $classe = $serializer->deserialize($request->getContent(), classe::class, 'json');
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
        
        classe $classe,
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse {
        $classe = $serializer->deserialize(
            $request->getContent(),
            classe::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE=>$classe]
        );
        $classe->setStatus(true);
        $content = $request->toArray();

        $entityManager->persist($classe);
        $entityManager->flush();
        
        $location = $urlGenerator->generate("classe.getOne",['idClasse' => $classe->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        
        $jsonplayer = $serializer->serialize($classe, 'json', ['groups'=>'getClasse']);
        return new JsonResponse($jsonplayer,Response::HTTP_CREATED,["Location"=>$location],true);
    }
}
