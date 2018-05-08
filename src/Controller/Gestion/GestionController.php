<?php

namespace App\Controller\Gestion;


use App\Entity\Album;
use App\Entity\Photo;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GestionController extends Controller
{

    /**
     * @Route("/gestion-site", name="gestion-site")
     */
    public function albums(Request $request) {

        $albums = $this->getDoctrine()
            ->getRepository(Album::class)
            ->findAll();

        $album = new Album();
        $album->setAlbumDate(new \DateTime());

        $form = $this->createFormBuilder($album)
            ->add('albumName', TextType::class)
            ->add('save', SubmitType::class, array('label' => 'Ajouter'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $album = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($album);
            $entityManager->flush();

            return $this->redirectToRoute('gestion-site');
        }

        return $this->render('gestion/album.html.twig',array(
            'form' => $form->createView(),
            'albums' => $albums
        ));
    }

    /**
     * @Route("/gestion-site/delete-album/{id}", name="deleteAlbum")
     */
    public function deleteAlbum(Album $album) {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($album);
        $entityManager->flush();
        return $this->redirectToRoute('gestion-site');
    }

    /**
     * @Route("/gestion-site/album/{albumId}", name="gererAlbum")
     */
    public function gererAlbum(Request $request,$albumId) {
        $album = $this->getDoctrine()->getManager()->getRepository(Album::class)->find($albumId);
        $photo = new Photo();
        $photo->setPhotoDate(new \DateTime());
        $photo->setAlbum($album);
        $form = $this->createFormBuilder($photo)
            ->add('photoName', TextType::class)
            ->add('photo', FileType::class, array('label' => 'Image'))
            ->add('save', SubmitType::class, array('label' => 'Ajouter'))
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photo = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $photo->getPhoto();
            $fileName = md5(uniqid()).'.'.$file->guessExtension();
            $file->move(
                $this->getParameter('photos_directory'),
                $fileName
            );
            $photo->setPhoto($fileName);
            $entityManager->persist($photo);
            $entityManager->flush();
            return new RedirectResponse($this->generateUrl('gererAlbum',array('albumId' => $albumId)));
        }
        return $this->render('gestion/photo.html.twig',array(
            'form' => $form->createView(),
            'album' => $album
        ));
    }

    /**
     * @Route("/gestion-site/album/{albumId}/delete-photo/{photoId}", name="deletePhoto")
     */
    public function deletePhoto($albumId, $photoId) {
        $photo = $this->getDoctrine()->getRepository(Photo::class)->find($photoId);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($photo);
        $filePath = $this->getParameter("photos_directory") . DIRECTORY_SEPARATOR . $photo->getPhoto();
        if(file_exists($filePath)) {
            unlink($filePath);
        }
        $entityManager->flush();
        return new RedirectResponse($this->generateUrl('gererAlbum',array('albumId' => $albumId)));
    }
}
