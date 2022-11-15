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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\Json;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
/**
* @OA\Tag(name="Pictures")
*/
class PictureController extends AbstractController
{
    #[Route('api/pictures/{idPicture}', name: 'picture.getOne',methods:['GET'])]
    #[IsGranted('ROLE_ADMIN',message: 'Acces deny, you need an elevation')]
    public function getPicture(
        int $idPicture,
        SerializerInterface $serializer,
        PictureRepository $pictureRepository,
        UrlGeneratorInterface $urlGenerator,
        Request $request
        ): JsonResponse
    {
        $picture = $pictureRepository->find($idPicture);        
        $relativePath = $picture->getPublicPath() . "/" . $picture->getRealPath();
        $location = $request->getUriForPath('/');
        $location = $location . str_replace("/assets/pictures/", $picture->getPublicPath(),$relativePath);
        dd($location);
        if($picture){
            return new JsonResponse($serializer->serialize($picture, 'json',["groups"=>'getPicture']), JsonResponse::HTTP_OK,["Location"=>$location],true);
        }
        return new JsonResponse(null,JsonResponse::HTTP_NOT_FOUND);
    }
    
    #[Route('api/pictures', name: 'picture.create',methods:['POST'])]
    #[IsGranted('ROLE_ADMIN',message: 'Acces deny, you need an elevation')]
    public function createPicture(
        Request $request,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        SerializerInterface $serializer,
    ): JsonResponse
    {
        

        $files = $request->files->get("file");
        foreach ($files as $file){
            $picture = new Picture();
            $picture->setFile($file)
            ->setMimeType($file->getClientMimeType())
            ->setRealName($file->getClientOriginalName())
            ->setPublicPath("assets/pictures/")
            ->setStatus(true);
            $entityManager->persist($picture);
            $entityManager->flush();
            $location = $urlGenerator->generate("picture.getOne",['idPicture' => $picture->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            $jsonpicture = $serializer->serialize($picture, 'json', ["groups"=>'getPicture']);
        }




        return new JsonResponse($jsonpicture,Response::HTTP_CREATED,["Location"=>$location],false);
    }

}
