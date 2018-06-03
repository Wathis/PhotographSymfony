<?php

/**
 * Created by PhpStorm.
 * User: mathisdelaunay
 * Date: 26/04/2018
 * Time: 22:02
 */

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Carousel;
use FOS\UserBundle\Mailer\Mailer;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
     * @Route("/cgv", name="cgv")
     */
    public function cgv()
    {
        return $this->render('cgv.html.twig', [
            'controller_name' => 'MainController'
        ]);
    }

    /**
     * @Route("/accueil", name="accueil")
     */
    public function accueil(Request $request)
    {
        $carousels = $this->getDoctrine()->getManager()->getRepository(Carousel::class)->findAll();
        $form = $this->createFormBuilder()
            ->add('info',TextType::class,array(
                'label' => false,
                'attr' => array(
                    'placeholder' => 'Ex : NOM Prenom',
                    'class' => 'z-depth-1 searchInput browser-default center-align',
                )
            ))
            ->add('Rechercher', SubmitType::class,array(
                'attr' => array(
                    'class' => 'noInputStyle buttonWhite center-align'
                )
            ))
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            return $this->redirectToRoute('rechercher',array(
               'info' => $data["info"]
            ));
        }

        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
            'form' => $form->createView(),
            'carousels' => $carousels
        ]);
    }

}
