<?php

/**
 * Created by PhpStorm.
 * User: mathisdelaunay
 * Date: 26/04/2018
 * Time: 22:02
 */

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MainController extends Controller
{
    /**
     * @Route("/", name="main")
     */
    public function index()
    {
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    /**
     * @Route("/gestion-site", name="gestion-site")
     */
    public function gestionSite()
    {
        return $this->render('gestion/index.html.twig');
    }
}
