<?php

namespace App\Controller;

use App\Entity\Picture;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use App\Repository\PictureRepository;

class PictureController extends AbstractController
{
    #[Route('/picture', name: 'app_picture')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PictureController.php',
        ]);
    }
    #[Route('api/pictures/{idPicture}', name:"pictures.get", methods: "GET")]
    public function getPicture(int $idPicture, 
    SerializerInterface $serializer, 
    PictureRepository $pictureRepository, 
    UrlGeneratorInterface $urlGenerator,
    Request $request
    ): JsonResponse
    {
        $picture = $pictureRepository->find($idPicture);
        $relativePath = $picture->getPublicPath() . "/" . $picture->getRealPath();
        $location = $request->getUriForPath("/");
        $location = $location . str_replace("assets", "assets", $relativePath);
        if($picture){
            return new JsonResponse($serializer->serialize($picture, 'json', ["groups" => "getPicture"]), JsonResponse::HTTP_OK, ["Location" => $location], true);
        }
        return new JsonResponse(null, JsonResponse::HTTP_NOT_FOUND);
    }

    #[Route('api/pictures', name: 'picture.create',methods:['POST'])]
    public function createPicture(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse
    {
        
        $picture = new Picture();
        $files = $request->files->get('file');
        $picture->setFile($files)
        ->setMimeType($files->getClientMimeType())
        ->setRealName($files->getClientOriginalName())
        ->setPublicPath("assets/pictures/")
        ->setStatus(true);

        $entityManager->persist($picture);
        $entityManager->flush();
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PictureController.php',
        ]);

        $location = $urlGenerator->generate("pictures.get", ['idPicture'=>$picture->getId()]);
        $jsonPicture = $serializer->serialize($picture, "json", ['groups' =>'getPictures']);
        return new JsonResponse($jsonPicture, JsonResponse::HTTP_CREATED, ["location" => $location], true);
    }   

}
