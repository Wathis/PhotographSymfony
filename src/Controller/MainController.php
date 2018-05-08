<?php

/**
 * Created by PhpStorm.
 * User: mathisdelaunay
 * Date: 26/04/2018
 * Time: 22:02
 */

namespace App\Controller;

use App\Entity\Album;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MainController extends Controller
{

    /**
     * @Route("/", name="main")
     */
    public function index()
    {
        return $this->redirectToRoute('accueil');
    }
    /**
     * @Route("/accueil", name="accueil")
     */
    public function accueil()
    {
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

}
