<?php

namespace App\Controller;

use App\Entity\Classe;
use App\Repository\ClasseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
// use Symfony\Component\Serializer\SerializerInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\PictureRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
/**
* @OA\Tag(name="Classes")
*/
class ClasseController extends AbstractController
{

    /**
    * Cette méthode permet de récupérer toutes les classes.
    * @OA\Response(
    *      response=200,
    *      description="Récupérer toutes les classes.",
    *      @Model(type=Classe::class)
    * )
    * 
    * @param ClasseRepository $repository
    * @param SerializerInterface $serializer
    * @param TagAwareCacheInterface $cache
    * @param Request $request
    * @return JsonResponse
    * 
    */
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
            $context = SerializationContext::create()->setGroups("getAllClasse");
            return $serializer->serialize($challenge, 'json',$context);
        });

        return new JsonResponse($jsonClasses, Response::HTTP_OK, [], true);
    }

    /**
    * Retourne une classe aléatoire.
    * @OA\Response(
    *      response=200,
    *      description="Récupérer une classe aléatoire.",
    *      @Model(type=Classe::class)
    * )
    * 
    * @param ClasseRepository $repository
    * @param SerializerInterface $serializer
    * @param TagAwareCacheInterface $cache
    * @param Request $request
    * @return JsonResponse
    * 
    */
    #[Route('api/classe/rand', name: 'classerand.get', methods: ['GET'])]
    public function classe( 
        ClasseRepository $repository,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache,
        Request $request
    ): JsonResponse {

        $challenge = $repository->getRandomClasse();
        $context = SerializationContext::create()->setGroups("getClasse");
        $jsonClasses = $serializer->serialize($challenge, 'json', $context);

        return new JsonResponse($jsonClasses, Response::HTTP_OK, [], true);
    }

    /**
    * Retourne une classe en renseignant son ID.
    * @OA\Response(
    *      response=200,
    *      description="Récupérer une classe en renseignant son ID.",
    *      @Model(type=Classe::class)
    * )
    * 
    * @param Classe $classe
    * @param SerializerInterface $serializer
    * @return JsonResponse
    * 
    */
    #[Route('/api/classe/{idClasse}', name: 'classe.getOne', methods: ['GET'])]
    #[ParamConverter("classe", options:["id"=>"idClasse"], class:"App\Entity\Classe")]
    public function getClasse(
        Classe $classe,
        SerializerInterface $serializer
    ): JsonResponse {
        $context = SerializationContext::create()->setGroups("getClasse");
        $jsonClasse = $serializer->serialize($classe, 'json',$context);
        return new JsonResponse($jsonClasse, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**
    * Supprime une classe en renseignant son ID.
    * @OA\Response(
    *      response=200,
    *      description="Supprime une classe en renseignant son ID.",
    *      @Model(type=Classe::class)
    * )
    * @param Classe $classe
    * @param EntityManagerInterface $entityManager
    * @param TagAwareCacheInterface $cache
    * @return JsonResponse
    * 
    */
    #[Route('/api/classe/{idClasse}', name: 'classe.delete', methods: ['DELETE'])]
    #[ParamConverter("classe", options:["id"=>"idClasse"], class:"App\Entity\Classe")]
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

    /**
    * Créer une classe en renseignant ses propriétés.
    * @OA\Response(
    *      response=200,
    *      description="Créer une classe en renseignant ses propriétés.",
    *      @Model(type=Classe::class)
    * )
    * @OA\RequestBody(@Model(type=Classe::class))
    * @param Classe $classe
    * @param EntityManagerInterface $entityManager
    * @param TagAwareCacheInterface $cache
    * @param Request $request
    * @param UrlGeneratorInterface $urlGenerator
    * @param ValidatorInterface $validators
    * @return JsonResponse
    * 
    */
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


        $context = SerializationContext::create()->setGroups("getClasse");
        $jsonClasse = $serializer->serialize($classe, 'json', $context);
        return new JsonResponse($jsonClasse,Response::HTTP_CREATED,["location"=>$location],false);
    }

    /**
    * Modifie une classe en renseignant son id, et ses propriétés.
    * @OA\Response(
    *      response=200,
    *      description="Modifie une classe en renseignant son id, et ses propriétés.",
    *      @Model(type=Classe::class)
    * )
    * @OA\RequestBody(@Model(type=Classe::class))
    * @param Classe $classe
    * @param EntityManagerInterface $entityManager
    * @param TagAwareCacheInterface $cache
    * @param Request $request
    * @param UrlGeneratorInterface $urlGenerator
    * @param ValidatorInterface $validators
    * @return JsonResponse
    * 
    */
    #[Route('/api/classe/{idClasse}', name: 'classe.update', methods: ['PUT'])]
    #[ParamConverter("classe", options:["id"=>"idClasse"], class:"App\Entity\Classe")]
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
        $updateClasse = $serializer->deserialize(
            $request->getContent(),
            classe::class,
            'json'
        );
        $classe->setName($updateClasse->getName() ? $updateClasse->getName() : $classe->getName());

        $classe->setStatus(true);
        $content = $request->toArray();

        $classe->setPicture($pictureRepository->find(3));

        $entityManager->persist($classe);
        $entityManager->flush();

        $context = SerializationContext::create()->setGroups("getClasse");
        $jsonClasse = $serializer->serialize($classe, 'json', $context);
        return new JsonResponse($jsonClasse,Response::HTTP_CREATED,[],true);
    }
}
