<?php

/**
 * Created by PhpStorm.
 * User: mathisdelaunay
 * Date: 26/04/2018
 * Time: 22:02
 */

namespace App\Controller\Contact;

use App\Entity\Album;
use FOS\UserBundle\Mailer\Mailer;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MainController extends Controller
{

    /**
     * @Route("/contact", name="contact")
     */
    public function contact(Request $request, \Swift_Mailer $mailer){

        $defaultData = array('message' => 'Type your message here');
        $form = $this->createFormBuilder($defaultData)
            ->add('nom', TextType::class,array('label' => false,'attr' => array('class' => 'browser-default')))
            ->add('email', EmailType::class,array('label' => false,'attr' => array('class' => 'browser-default')))
            ->add('message', TextareaType::class,array('label' => false,'attr' => array('class' => 'browser-default')))
            ->add('envoyer', SubmitType::class,array('attr' => array('class' => 'noInputStyle button buttonGreen center-align')))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $this->get('session')->getFlashBag()->add('success','Le message a bien été envoyé');
            $message = (new \Swift_Message('Contact photo sport normandy'))
                ->setFrom('photosportnormandy@gmail.com')
                ->setTo('photosportnormandy@gmail.com')
                ->setBody(
                    $this->renderView(
                        'emails/contact.html.twig',
                        array(
                            'email' => $data['email'],
                            'nom' => $data['nom'],
                            'message' => $data['message']
                        )
                    ),
                    'text/html'
                )
            ;
            $mailer->send($message);
        }

        return $this->render('contact/index.html.twig',array(
            'form' => $form->createView()
        ));
    }

}
