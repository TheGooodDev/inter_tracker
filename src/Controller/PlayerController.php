<?php

namespace App\Controller;

use App\Entity\Player;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Contracts\Cache\ItemInterface;

/**
* @OA\Tag(name="Players")
*/
class PlayerController extends AbstractController
{
    /**
    * Cette méthode permet de récupérer tout les players.
    * @OA\Response(
    *      response=200,
    *      description="Retourne la liste des players",
    *      @OA\JsonContent(
    *        type="array",
    *        @OA\Items(ref=@Model(type=Player::class, groups={"getAllPlayer"})))
    *      )
    * )
    * 
    * 
    * @param PlayerRepository $repository
    * @param SerializerInterface $serializer
    * @return JsonResponse
    * 
    */
    #[Route('/api/players/', name: 'player.getAll',methods:['GET'])]
    public function getAllPlayers(
        PlayerRepository $repository,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache
    ): JsonResponse {
        $idCache = "getAllPlayer";
        $jsonPlayers = $cache->get($idCache, function(ItemInterface $item)use ($repository,$serializer){
            echo "MISE EN CACHE";
            $item->tag("playerCache");
            
            $player = $repository->findAll();
            $context = SerializationContext::create()->setGroups("getAllPlayer");
            return $serializer->serialize($player, 'json', $context);
        });
        return new JsonResponse($jsonPlayers, Response::HTTP_OK, [], true);
    }

    /**
    * Cette méthode permet de récupérer un player grace à son ID.
    * @OA\Response(
    *      response=200,
    *      description="Retourne un player",
    *      @Model(type=Player::class,groups={"getPlayer"})
    * )
    * 
    * 
    * @param Player $player
    * @param SerializerInterface $serializer
    * @return JsonResponse
    * 
    */
    #[Route('/api/player/{idPlayer}', name: 'player.getOne', methods: ['GET'])]
    #[ParamConverter("player", options:["id"=>"idPlayer"], class:"App\Entity\Player")]
    public function getPlayer(
        Player $player,
        SerializerInterface $serializer
    ): JsonResponse {
        $context = SerializationContext::create()->setGroups("getPlayer");
        $jsonPlayer = $serializer->serialize($player, 'json',$context);
        return new JsonResponse($jsonPlayer, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**
    * Supprime un player en renseignant son ID.
    * @OA\Response(
    *      response=204,
    *      description="Supprime un player en renseignant son ID.",
    *      @Model(type=Player::class)
    * )
    * @param Player $classe
    * @param EntityManagerInterface $entityManager
    * @return JsonResponse
    * 
    */
    #[Route('/api/player/{idPlayer}', name: 'player.delete', methods: ['DELETE'])]
    #[ParamConverter("player", options:["id"=>"idPlayer"], class:"App\Entity\Player")]
    public function deletePlayer(
        Player $player,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache
    ): JsonResponse {
        $cache->invalidateTags(["playerCache"]);
        $entityManager->remove($player);
        $entityManager->flush();
        return new JsonResponse(null,Response::HTTP_NO_CONTENT);
    }


    /**
    * Créer un player en renseignant ses propriétés.
    * @OA\Response(
    *      response=201,
    *      description="Créer un player en renseignant ses propriétés.",
    *      @Model(type=Player::class,groups={"getPlayer"})
    * )
    * @OA\RequestBody(@Model(type=Player::class))
    * @param Request $request
    * @param EntityManagerInterface $entityManager
    * @param TagAwareCacheInterface $cache
    * @param UrlGeneratorInterface $urlGenerator
    * @param ValidatorInterface $validators
    * @return JsonResponse
    * 
    */
    #[Route('/api/players', name: 'player.create', methods: ['POST'])]
    public function createPlayer(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface $validators,
        TagAwareCacheInterface $cache
    ): JsonResponse {
        $cache->invalidateTags(["playerCache"]);
        $player = $serializer->deserialize($request->getContent(), Player::class, 'json');
        $player->setStatus(true);

        $errors = $validators->validate($player);
        if($errors->count() > 0){
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST,[],true);
        }
        $entityManager->persist($player);
        $entityManager->flush();

        $location = $urlGenerator->generate("player.getOne",['idPlayer' => $player->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $context = SerializationContext::create()->setGroups("getPlayer");
        $jsonPlayer = $serializer->serialize($player, 'json', $context);
        return new JsonResponse($jsonPlayer,Response::HTTP_CREATED,["location"=>$location],false);
    }

    /**
    * Modifie un player en renseignant son id, et ses propriétés.
    * @OA\Response(
    *      response=201,
    *      description="Modifie un player en renseignant son id, et ses propriétés.",
    *      @Model(type=Player::class, groups={"getPlayer"})
    * )
    * @OA\RequestBody(@Model(type=Player::class))
    * @param Player $classe
    * @param EntityManagerInterface $entityManager
    * @param TagAwareCacheInterface $cache
    * @param Request $request
    * @param UrlGeneratorInterface $urlGenerator
    * @param ValidatorInterface $validators
    * @return JsonResponse
    * 
    */
    #[Route('/api/player/{idPlayer}', name: 'player.update', methods: ['PUT'])]
    #[ParamConverter("player", options:["id"=>"idPlayer"], class:"App\Entity\player")]
    public function updateplayer(
        
        Player $player,
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
        TagAwareCacheInterface $cache
        ): JsonResponse {
        $cache->invalidateTags(["classeCache"]);
        $updatedPlayer = $serializer->deserialize(
            $request->getContent(),
            player::class,
            'json'
        );
        $player->setPseudo($updatedPlayer->getPseudo() ? $updatedPlayer->getPseudo() : $player->getPseudo());
        $player->setClasse($updatedPlayer->getClasse() ? $updatedPlayer->getClasse() : $player->getClasse());
        $player->setStatus(true);

        $entityManager->persist($player);
        $entityManager->flush();
        
        $location = $urlGenerator->generate("player.getOne",['idPlayer' => $player->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        
        $context = SerializationContext::create()->setGroups("getPlayer");
        $jsonplayer = $serializer->serialize($player, 'json', $context);
        return new JsonResponse($jsonplayer,Response::HTTP_CREATED,["Location"=>$location],true);
    }
}
