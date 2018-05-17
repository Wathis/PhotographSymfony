<?php

/**
 * Created by PhpStorm.
 * User: mathisdelaunay
 * Date: 26/04/2018
 * Time: 22:02
 */

namespace App\Controller;

use App\Entity\Album;
use FOS\UserBundle\Mailer\Mailer;
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
    public function accueil(\Swift_Mailer $mailer)
    {
        $message = (new \Swift_Message('Achat de photos'))
            ->setFrom('photosportnomandy@gmail.com')
            ->setTo("delaunaymathis@yahoo.fr")
            ->setBody(
                $this->renderView(
                    'emails/payment_done.html.twig',
                    array('links' => array(
                        "http://liens",
                        "http://liens"
                    ))
                ),
                'text/html'
            )
        ;
        $mailer->send($message);
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

}
