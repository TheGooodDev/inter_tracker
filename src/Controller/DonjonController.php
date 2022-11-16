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
// use Symfony\Component\Serializer\SerializerInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\Validator\Validator\ValidatorInterface;
/**
* @OA\Tag(name="Donjons")
*/
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

    /**
    * Cette méthode permet de récupérer toutes les donjons.
    * @OA\Response(
    *      response=200,
    *      description="Récupérer toutes les donjons.",
    *      @Model(type=Donjon::class, groups={"getAllDonjons"})
    * )
    * 
    * @param DonjonRepository $repository
    * @param SerializerInterface $serializer
    * @param TagAwareCacheInterface $cache
    * @param Request $request
    * @return JsonResponse
    * 
    */
    #[Route('/api/donjons', name: 'donjon.getAll',methods:['GET'])]
    public function getAllDonjons(
        DonjonRepository $repository,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache,
        Request $request
    ): JsonResponse {
        $idCache = "getAllDonjons";
        $jsonDonjon = $cache->get($idCache, function(ItemInterface $item)use ($repository,$serializer){
            echo "MISE EN CACHE";
            $item->tag("donjonCache");
            
            $donjon = $repository->findAll();
            $context = SerializationContext::create()->setGroups("getAllDonjons");
            return $serializer->serialize($donjon, 'json', $context);
        });
        return new JsonResponse($jsonDonjon, Response::HTTP_OK, [], true);
    }

    /**
    * Retourne une réponse JSON contenant un donjon aléatoire.
    * @OA\Response(
    *      response=200,
    *      description="Récupère un donjon aléatoire.",
    *      @Model(type=Donjon::class, groups={"getDonjon"})
    * )
    * 
    * @param DonjonRepository $repository
    * @param SerializerInterface $serializer
    * @param TagAwareCacheInterface $cache
    * @return JsonResponse
    * 
    */
    #[Route('api/donjon/rand', name: 'donjonrand.get', methods: ['GET'])]
    public function getRandomDonjon( 
        DonjonRepository $repository,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache,
    ): JsonResponse {
        
        $donjon = $repository->getRandomDonjon();
        $context = SerializationContext::create()->setGroups("getDonjon");
        $jsonDonjon = $serializer->serialize($donjon, 'json', $context);
        return new JsonResponse($jsonDonjon, Response::HTTP_OK, [], true);
    }


    /**
    * Retourne un donjon via l'id en paramètre.
    * @OA\Response(
    *      response=200,
    *      description="Récupère un donjon via l'id en paramètre.",
    *      @Model(type=Donjon::class, groups={"getDonjon"})
    * )
    * 
    * @param Donjon $donjon
    * @param SerializerInterface $serializer
    * @return JsonResponse
    * 
    */
    #[Route('/api/donjon/{idDonjon}', name: 'donjon.getOne', methods: ['GET'])]
    #[ParamConverter("donjon", options: ["id" => "idDonjon"], class: "App\Entity\Donjon")]
    #[IsGranted('ROLE_ADMIN',message: 'Acces deny, you need an elevation')]
    public function getDonjon(
        Donjon $donjon,
        SerializerInterface $serializer
    ): JsonResponse {
        $context = SerializationContext::create()->setGroups("getDonjon");
        $jsondonjon = $serializer->serialize($donjon, 'json', $context);
        return new JsonResponse($jsondonjon, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**
    * Cette méthode permet de supprimer un donjon en renseignant son ID.
    * @OA\Response(
    *      response=204,
    *      description="Cette méthode permet de supprimer un donjon en renseignant son ID.",
    * )
    * 
    * @param Donjon $donjon
    * @param EntityManagerInterface $entityManager
    * @param TagAwareCacheInterface $cache
    * @return JsonResponse
    * 
    */
    #[Route('/api/donjon/{idDonjon}', name: 'donjon.delete', methods: ['DELETE'])]
    #[ParamConverter("donjon", options:["id"=>"idDonjon"], class:"App\Entity\Donjon")]
    public function deletedonjon(
        Donjon $donjon,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache,
    ): JsonResponse {
        $cache->invalidateTags(["donjonCache"]);
        $entityManager->remove($donjon);
        $entityManager->flush();
        return new JsonResponse(null,Response::HTTP_NO_CONTENT);
    }

    /**
    * Créer un donjon.
    * @OA\Response(
    *      response=201,
    *      description="Créer un donjon.",
    *      @Model(type=Donjon::class, groups={"getDonjon"})
    * )
    * 
    * @param Request $request
    * @param EntityManagerInterface $entityManager
    * @param ChallengeRepository $repository
    * @param UrlGeneratorInterface $urlGenerator
    * @param TagAwareCacheInterface $cache
    * @param SerializerInterface $serializer
    * @return JsonResponse
    * 
    */
    #[Route('/api/donjons', name: 'donjon.create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN',message: 'Acces deny, you need an elevation')]
    public function createDonjon(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ChallengeRepository $repository,
        UrlGeneratorInterface $urlGenerator,
        TagAwareCacheInterface $cache
    ): JsonResponse {
        $cache->invalidateTags(["donjonCache"]);

        $donjon = $serializer->deserialize($request->getContent(), Donjon::class, 'json');
        $donjon->setStatus(true);

        $content = $request->toArray();
        $challenge = $content["idChallenge"];

        $donjon->setChallenges($repository->find($challenge));

        $entityManager->persist($donjon);
        $entityManager->flush();

        $location = $urlGenerator->generate("donjon.getOne",['idDonjon' => $donjon->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $context = SerializationContext::create()->setGroups("getDonjon");
        $jsondonjon = $serializer->serialize($donjon, 'json', $context);
        return new JsonResponse($jsondonjon,Response::HTTP_CREATED,["Location"=>$location],true);
    }

    /**
    * Modifier un donjon en donnant un ID.
    * @OA\Response(
    *      response=201,
    *      description="Modifier un donjon en donnant un ID.",
    *      @Model(type=Donjon::class, groups={"getDonjon"})
    * )
    * 
    * @param Request $request
    * @param EntityManagerInterface $entityManager
    * @param ChallengeRepository $repository
    * @param UrlGeneratorInterface $urlGenerator
    * @param TagAwareCacheInterface $cache
    * @param SerializerInterface $serializer
    * @return JsonResponse
    * 
    */
    #[Route('/api/donjon/{idDonjon}', name: 'donjon.update', methods: ['PUT'])]
    #[ParamConverter("donjon", options:["id"=>"idDonjon"], class:"App\Entity\Donjon")]
    #[IsGranted('ROLE_ADMIN',message: 'Acces deny, you need an elevation')]
    public function updateDonjon(

        Donjon $donjon,
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ChallengeRepository $repository,
        UrlGeneratorInterface $urlGenerator,
        TagAwareCacheInterface $cache
    ): JsonResponse {
        $cache->invalidateTags(["donjonCache"]);

        $updateDonjon = $serializer->deserialize(
            $request->getContent(),
            Donjon::class,
            'json'
        );
        $donjon->setName($updateDonjon->getName() ? $updateDonjon->getName() : $donjon->getName());
        $donjon->setLevel($updateDonjon->getLevel() ? $updateDonjon->getLevel() : $donjon->getLevel());

        $donjon->setStatus(true);
        $content = $request->toArray();
        $challenge = $content["idChallenge"];

        $donjon->setChallenges($repository->find($challenge));

        $entityManager->persist($donjon);
        $entityManager->flush();
        
        $location = $urlGenerator->generate("donjon.getOne",['idDonjon' => $donjon->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        
        $context = SerializationContext::create()->setGroups("getDonjon");

        $jsondonjon = $serializer->serialize($donjon, 'json', $context);
        return new JsonResponse($jsondonjon,Response::HTTP_CREATED,["Location"=>$location],true);
    }
}
