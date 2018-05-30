<?php

namespace App\Controller\Gallery;

use App\Entity\Album;
use App\Entity\Format;
use App\Entity\Photo;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GalleryController extends Controller
{

    /**
     * @Route("/galerie/{category}", name="gallerie")
     */
    public function gallerie($category)
    {
        if (isset($_POST["dateGallery"]) && !empty($_POST["dateGallery"])){
            $albums = $this->getDoctrine()
                ->getRepository(Album::class)
                ->findByDate($_POST["dateGallery"]);
        } else {
            $albums = $this->getDoctrine()
                ->getRepository(Album::class)
                ->findAll();
        }
        $miniatures = array();
        foreach ($albums as $album) {
            if (strpos($album->getCategory(),'payant') !== false) {
                $miniatures[$album->getId()] = count($album->getPhotos()) > 0 ? $album->getPhotos()[0]->getWatermark() : "pas-apercu.png";
            } else {
                $miniatures[$album->getId()] = count($album->getPhotos()) > 0 ? $album->getPhotos()[0]->getPhoto() : "pas-apercu.png";
            }
        }
        return $this->render('gallery/gallery.html.twig', [
            'controller_name' => 'GalleryController',
            'category' => $category,
            'albums' => $albums,
            'miniatures' => $miniatures
        ]);
    }


    /**
     * @Route("/galerie/{category}/password/{id}", name="passwordAlbum")
     */
    public function passwordAlbum(Request $request,$category,Album $album)
    {
        $form = $this->createFormBuilder()
            ->add('password', TextType::class, array("label" => false,'attr' => array('class' => 'emailInput browser-default','placeholder' => 'Mot de passe')))
            ->add('save', SubmitType::class, array('label' => 'Valider','attr' => array('class' => 'noInputStyle button buttonGreen center-align')))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $datas = $form->getData();
            $password = $datas['password'];
            return $this->redirectToRoute('album', array('id' => $album->getId(), 'category' => $category,'password' => $password));
        }

        return $this->render('gallery/password.html.twig', [
            'form' => $form->createView(),
            'album' =>$album,
            'category' => $category
        ]);
    }

    /**
     * @Route("/galerie/{category}/album/{id}", name="album")
     */
    public function album($category,Album $album)
    {
        if (strpos($album->getCategory(),'protected') !== false) {
            if (isset($_GET["password"]) && !empty($_GET["password"])){
                if ($album->getPassword() !== $_GET["password"]) {
                    $this->get('session')->getFlashBag()->add('error','Mot de passe invalide');
                    return $this->redirectToRoute('passwordAlbum',array('category' => $category,'id' => $album->getId()));
                }
            } else {
                return $this->redirectToRoute('passwordAlbum',array('category' => $category,'id' => $album->getId()));
            }
        }
        return $this->render('gallery/album.html.twig', [
            'controller_name' => 'GalleryController',
            'albumName' => 'CAEN HB BESANSON',
            'category' => $category,
            'album' => $album,
        ]);
    }
    /**
     * @Route("/galerie/particulier/rechercher/{info}", name="rechercher")
     */
    public function rechercher($info)
    {
        $photos = $this->getDoctrine()
            ->getRepository(Photo::class)
            ->findWhereInfo($info);
        return $this->render('gallery/rechercher.html.twig', [
            'info' => $info,
            'photos' => $photos,
            'category' => 'professionnel'
        ]);
    }
    /**
     * @Route("/galerie/{category}/album/{albumId}/photo/{photoId}", name="viewer")
     */
    public function viewer($category,$albumId,$photoId)
    {
        $album = $this->getDoctrine()
            ->getRepository(Album::class)
            ->find($albumId);

        if (strpos($album->getCategory(),'protected') !== false) {
            if (isset($_GET["password"]) && !empty($_GET["password"])){
                if ($album->getPassword() !== $_GET["password"]) {
                    $this->get('session')->getFlashBag()->add('error','Mot de passe invalide');
                    return $this->redirectToRoute('passwordAlbum',array('category' => $category,'id' => $album->getId()));
                }
            } else {
                return $this->redirectToRoute('passwordAlbum',array('category' => $category,'id' => $album->getId()));
            }
        }

        $photo = $this->getDoctrine()
            ->getRepository(Photo::class)
            ->find($photoId);

        $next = $this->getDoctrine()
            ->getRepository(Photo::class)
            ->next($albumId,$photoId);

        $previous = $this->getDoctrine()
            ->getRepository(Photo::class)
            ->previous($albumId,$photoId);

        list($width,$height) = getimagesize($this->getParameter('photos_directory') . DIRECTORY_SEPARATOR . $photo->getPhoto());
        $formats = $this->getDoctrine()
            ->getRepository(Format::class)
            ->findAll();

        return $this->render('gallery/viewer.html.twig', [
            'controller_name' => 'GalleryController',
            'category' => $category,
            'photo' => $photo,
            'album' => $album,
            'photoWidth' => $width,
            'photoHeight' => $height,
            'formats' => $formats,
            'next' => $next,
            'previous' => $previous
        ]);
    }
}
