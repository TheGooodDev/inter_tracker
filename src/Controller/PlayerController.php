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

class PlayerController extends AbstractController
{
    #[Route('/player', name: 'app_player')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PlayerController.php',
        ]);
    }

    #[Route('/api/players', name: 'player.getAll',methods:['GET'])]
    public function getAllPlayers(
        PlayerRepository $repository,

        SerializerInterface $serializer,
        Request $request
    ): JsonResponse {
        $page = $request->get('page',1);
        $limit = $request->get('limit',5);
        $limit = $limit > 20 ? 20 : $limit;



        $player = $repository->findWithPagination($page,$limit);
        $context = SerializationContext::create()->setGroups("getAllPlayer");
        $jsonPlayers = $serializer->serialize($player, 'json',$context);
        return new JsonResponse($jsonPlayers, Response::HTTP_OK, [], true);
    }


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

    #[Route('/api/player/{idPlayer}', name: 'player.delete', methods: ['DELETE'])]
    #[ParamConverter("player", options:["id"=>"idPlayer"], class:"App\Entity\Player")]
    public function deletePlayer(
        Player $player,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $entityManager->remove($player);
        $entityManager->flush();
        return new JsonResponse(null,Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/players', name: 'player.create', methods: ['POST'])]
    public function createPlayer(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface $validators
    ): JsonResponse {
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

    #[Route('/api/player/{idPlayer}', name: 'player.update', methods: ['PUT'])]
    #[ParamConverter("player", options:["id"=>"idPlayer"], class:"App\Entity\player")]
    public function updateplayer(
        
        Player $player,
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse {
        $updatedPlayer = $serializer->deserialize(
            $request->getContent(),
            player::class,
            'json'
        );
        $player->setPseudo($updatedPlayer->getPseudo() ? $updatedPlayer->getPseudo() : $player->getPseudo());
        $player->setClasse($updatedPlayer->getClasse() ? $updatedPlayer->getClasse() : $player->getClasse());
        $player->setStatus(true);
        $content = $request->toArray();

        $entityManager->persist($player);
        $entityManager->flush();
        
        $location = $urlGenerator->generate("player.getOne",['idPlayer' => $player->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        
        $context = SerializationContext::create()->setGroups("getPlayer");
        $jsonplayer = $serializer->serialize($player, 'json', $context);
        return new JsonResponse($jsonplayer,Response::HTTP_CREATED,["Location"=>$location],true);
    }
}
