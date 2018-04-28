<?php

namespace App\Controller\Gallery;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GalleryController extends Controller
{
    /**
     * @Route("/galerie/particulier", name="particulier")
     */
    public function particulier()
    {
        return $this->render('gallery/index.html.twig', [
            'controller_name' => 'GalleryController'
        ]);
    }

    /**
     * @Route("/galerie/professionnel", name="professionnel")
     */
    public function professionnel()
    {
        return $this->render('gallery/index.html.twig', [
            'controller_name' => 'GalleryController'
        ]);
    }
}
