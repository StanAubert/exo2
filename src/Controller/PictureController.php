<?php

namespace App\Controller;

use App\Entity\Place;
use App\Entity\Picture;
use App\Form\PictureType;
use App\Repository\PictureRepository;
use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

#[Route('/place/picture')]
class PictureController extends AbstractController
{
    #[Route('/{place_id}', name: 'app_picture_index', methods: ['GET'])]
    #[Entity('place', options: ['id' => 'place_id'])]
    public function index(Place $place): Response
    {
        return $this->render('picture/index.html.twig', [
            'place'=> $place
        ]);
    }

    #[Route('/{place_id}/new', name: 'app_picture_new', methods: ['GET', 'POST'])]
    #[Entity('place', options: ['id' => 'place_id'])]
    public function new(Place $place = null, Request $request, FileUploader $fileUploader,  PictureRepository $pictureRepository): Response
    {
        if($place == null)
            throw new NotFoundResourceException();

        $picture = new Picture();
        $picture->setPlace($place);

        $form = $this->createForm(PictureType::class, $picture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $pictureFile = $form->get('pictureFile')->getData();


            if ($pictureFile) {
                $file = $fileUploader->upload($pictureFile, 'place');
                $picture->setFile($file);
            }

            $picture->setCreatedAt(new \DateTimeImmutable());

            $pictureRepository->save($picture, true);

            return $this->redirectToRoute('app_picture_index', ['place_id'=> $place->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('picture/new.html.twig', [
            'picture' => $picture,
            'place' => $place,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_picture_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Picture $picture, FileUploader $fileUploader, PictureRepository $pictureRepository): Response
    {
        $form = $this->createForm(PictureType::class, $picture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pictureFile = $form->get('pictureFile')->getData();
            if ($pictureFile) {
                /* Si la catégorie a déjà une image on supprime l'ancienne ! */
                if ($picture->getFile() != null)
                    $fileUploader->remove($picture->getFile(), 'place');

                $file = $fileUploader->upload($pictureFile, 'place');
                $picture->setFile($file);
            }

            $picture->setModifiedAt(new \DateTimeImmutable());

            $pictureRepository->save($picture, true);

            return $this->redirectToRoute('app_picture_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('picture/edit.html.twig', [
            'picture' => $picture,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_picture_delete', methods: ['POST'])]
    public function delete(Request $request, Picture $picture, PictureRepository $pictureRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$picture->getId(), $request->request->get('_token'))) {
            $pictureRepository->remove($picture, true);
        }

        return $this->redirectToRoute('app_picture_index', [], Response::HTTP_SEE_OTHER);
    }
}