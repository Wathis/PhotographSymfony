<?php

namespace App\Controller\Gallery;

use App\Entity\Format;
use App\Entity\Photo;
use Proxies\__CG__\App\Entity\Album;
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
            $miniatures[$album->getId()] = count($album->getPhotos()) > 0 ? $album->getPhotos()[0]->getWatermark() : "pas-apercu.png";
        }
        return $this->render('gallery/gallery.html.twig', [
            'controller_name' => 'GalleryController',
            'category' => $category,
            'albums' => $albums,
            'miniatures' => $miniatures
        ]);
    }
    /**
     * @Route("/galerie/{category}/album/{albumId}", name="album")
     */
    public function album($category,$albumId)
    {
        $album = $this->getDoctrine()
            ->getRepository(Album::class)
            ->find($albumId);
        return $this->render('gallery/album.html.twig', [
            'controller_name' => 'GalleryController',
            'albumName' => 'CAEN HB BESANSON',
            'category' => $category,
            'album' => $album
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
