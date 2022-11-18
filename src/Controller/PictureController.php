<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Repository\PictureRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Validator\Constraints\Json;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
* @OA\Tag(name="Pictures")
*/
class PictureController extends AbstractController
{

    /**
    * Cette méthode permet de récupérer toute les Picture.
    * @OA\Response(
    *      response=200,
    *      description="Retourne toute les Picture",
    *      @Model(type=Picture::class,groups={"getAllPicture"})
    * )
    * 
    * @param PictureRepository $repository
    * @param SerializerInterface $serializer
    * @param TagAwareCacheInterface $cache
    * @return JsonResponse
    * 
    */
    #[Route('api/pictures', name: 'picture.getAll',methods:['GET'])]
    #[IsGranted('ROLE_ADMIN',message: 'Acces deny, you need an elevation')]
    public function getAllPicture(
        PictureRepository $repository,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache,
        ): JsonResponse
    {
        $idCache = "getAllPicture";
        $jsonPicture = $cache->get($idCache, function(ItemInterface $item)use ($repository,$serializer){
            echo "MISE EN CACHE";
            $item->tag("pictureCache");
            $picture = $repository->findAll();
            $context = SerializationContext::create()->setGroups("getAllPicture");
            return $serializer->serialize($picture, 'json', $context);
        });
        return new JsonResponse($jsonPicture, Response::HTTP_OK, [], true);
    }

    /**
    * Cette méthode permet de récupérer une picture grace à son ID.
    * @OA\Response(
    *      response=200,
    *      description="Retourne une picture",
    *      @Model(type=Picture::class,groups={"getPicture"})
    * )
    * 
    * @param Picture $picture
    * @param SerializerInterface $serializer
    * @return JsonResponse
    * 
    */
    #[Route('api/pictures/{idPicture}', name: 'picture.getOne',methods:['GET'])]
    #[IsGranted('ROLE_ADMIN',message: 'Acces deny, you need an elevation')]
    public function getPicture(
        int $idPicture,
        SerializerInterface $serializer,
        PictureRepository $pictureRepository,
        Request $request
        ): JsonResponse
    {
        $picture = $pictureRepository->find($idPicture);        
        $relativePath = $picture->getPublicPath() . "/" . $picture->getRealPath();
        $location = $request->getUriForPath('/');
        $location = $location . str_replace("/assets/pictures/", $picture->getPublicPath(),$relativePath);
        if($picture){
            $context = SerializationContext::create()->setGroups("getPicture");
            return new JsonResponse($serializer->serialize($picture, 'json',$context), JsonResponse::HTTP_OK,["Location"=>$location],true);
        }
        return new JsonResponse(null,JsonResponse::HTTP_NOT_FOUND);
    }
    
    /**
    * Créer une picture en renseignant ses propriétés.
    * @OA\Response(
    *      response=201,
    *      description="Créer une picture en renseignant ses propriétés.",
    *      @Model(type=Picture::class,groups={"getPicture"})
    * )
    * @OA\RequestBody(
    *         @OA\MediaType(
    *           mediaType="multipart/form-data",
    *           @OA\Schema(ref="#/components/schemas/FileUpload")
    *         )
    *   )
    * @param Request $request
    * @param EntityManagerInterface $entityManager
    * @param TagAwareCacheInterface $cache
    * @param UrlGeneratorInterface $urlGenerator
    * @param ValidatorInterface $validators
    * @return JsonResponse
    */
    #[Route('api/pictures', name: 'picture.create',methods:['POST'])]
    #[IsGranted('ROLE_ADMIN',message: 'Acces deny, you need an elevation')]
    public function createPicture(
        Request $request,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache
    ): JsonResponse
    {
        $cache->invalidateTags(["pictureCache"]);
        $file = $request->files->get("file");

        $picture = new Picture();
        $picture->setFile($file)
        ->setMimeType($file->getClientMimeType())
        ->setRealName($file->getClientOriginalName())
        ->setPublicPath("assets/pictures/")
        ->setStatus(true);
        $entityManager->persist($picture);
        $entityManager->flush();
        $location = $urlGenerator->generate("picture.getOne",['idPicture' => $picture->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $context = SerializationContext::create()->setGroups("getPicture");
        $jsonpicture = $serializer->serialize($picture, 'json', $context);
        
        return new JsonResponse($jsonpicture,Response::HTTP_CREATED,["Location"=>$location],false);
    }

    /**
    * Cette méthode permet de supprimer une picture en renseignant son ID.
    * @OA\Response(
    *      response=204,
    *      description="Cette méthode permet de supprimer une picture en renseignant son ID.",
    * )
    * 
    * @param Picture $picture
    * @param EntityManagerInterface $entityManager
    * @param TagAwareCacheInterface $cache
    * @return JsonResponse
    * 
    */
    #[Route('/api/picture/{idPicture}', name: 'picture.delete', methods: ['DELETE'])]
    #[ParamConverter("picture", options:["id"=>"idPicture"], class:"App\Entity\Picture")]
    public function deletePicture(
        Picture $picture,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache,
    ): JsonResponse {
        $cache->invalidateTags(["pictureCache"]);
        $entityManager->remove($picture);
        $entityManager->flush();
        return new JsonResponse(null,Response::HTTP_NO_CONTENT);
    }

}
