<?php

namespace App\Controller\Gallery;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GalleryController extends Controller
{

    /**
     * @Route("/galerie/{category}", name="gallerie")
     */
    public function gallerie($category)
    {
        return $this->render('gallery/index.html.twig', [
            'controller_name' => 'GalleryController',
            'category' => $category
        ]);
    }

    /**
     * @Route("/galerie/{category}/album/{albumId}/photo/{photoId}", name="viewer")
     */
    public function viewer($category,$albumId,$photoId)
    {
        return $this->render('gallery/viewer.html.twig', [
            'controller_name' => 'GalleryController',
            'albumName' => 'CAEN HB BESANSON',
            'category' => $category
        ]);
    }
}