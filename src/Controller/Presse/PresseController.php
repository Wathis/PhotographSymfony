<?php

namespace App\Controller\Presse;

use App\Entity\Presse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PresseController extends Controller
{
    /**
     * @Route("/presse", name="presse")
     */
    public function index() {

        $presses = $this->getDoctrine()->getRepository(Presse::class)->findAll();

        return $this->render('presse/index.html.twig', [
            'presses' => $presses
        ]);
    }
}
