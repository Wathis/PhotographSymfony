<?php

namespace App\Controller\Payment;

use App\Entity\Format;
use App\Entity\Photo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PaymentController extends Controller
{
    /**
     * @Route("/panier", name="panier")
     */
    public function panier()
    {
        $panier = $this->get('session')->get('panier');
        $repositoryPhoto =  $this->getDoctrine()->getRepository(Photo::class);
        $repositoryFormat =  $this->getDoctrine()->getRepository(Format::class);
        $result = array();
        $photoSize = array();
        if ($panier !== null) {
            foreach ($panier as $photoId => $formats) {
                $photo = $repositoryPhoto->find($photoId);
                list($width,$height) = getimagesize($this->getParameter('photos_directory') . DIRECTORY_SEPARATOR . $photo->getPhoto());
                $photoSize[$photoId] = array(
                    'width' => $width,
                    'height' => $height
                );
                foreach ($formats as $format) {
                    $format = $repositoryFormat->find($format);
                    $result[] = array(
                        "photo" => $photo,
                        "format" => $format
                    );
                }
            }
        }
        return $this->render('payment/panier.html.twig', [
            'controller_name' => 'PaymentController',
            'panier'=> $result,
            'dimensions' => $photoSize
        ]);
    }

    /**
     * @Route("/photos/abandonner-panier", name="abandonnerPanier")
     */
    public function abandonnerPanier() {
        $session = $this->get('session');
        if ($session == null ) {
            $session->start();
        }
        $session->set('panier', null);
        return $this->redirectToRoute('accueil');
    }

    /**
     * @Route("/photos/ajouter-panier", name="ajouterPanier")
     */
    public function ajouterPanier(Request $request)
    {
        if (isset($_POST['photoId']) && !empty($_POST["photoId"])) {
            $photoId = $_POST["photoId"];
        } else {
            throw $this->createNotFoundException('Illegal arguments');
        }
        if (isset($_POST['format']) && !empty($_POST["format"])) {
            $formatId = $_POST["format"];
        } else {
            throw $this->createNotFoundException('Illegal arguments');
        }
        if (isset($_POST['acheter']) && !empty($_POST["acheter"])) {
            $acheter = $_POST["acheter"];
        } else {
            throw $this->createNotFoundException('Illegal arguments');
        }
        $session = $this->get('session');
        if ($session == null ) {
            $session->start();
        }
        $panier = $session->get('panier');
        if ($panier === null) {
            $panier = array();
        }
        if (array_key_exists($photoId,$panier)) {
            $tmp = $panier[$photoId];
            if (!in_array($formatId, $tmp)) {
                array_push($tmp, $formatId);
                $panier[$photoId] = $tmp;
                if ($acheter == "false")
                    $session->getFlashBag()->add('success','Photo ajoutée au panier');
            } else {
                if ($acheter == "false")
                    $session->getFlashBag()->add('error','Photo déjà présente dans le panier pour ce format');
            }
        } else {
            $panier[$photoId] = array($formatId);
            if ($acheter == "false")
                $session->getFlashBag()->add('success','Photo ajoutée au panier');
        }
        $session->set('panier', $panier);
        if ($acheter == "false"){
            return $this->redirect($request
                ->headers
                ->get('referer'));
        } else {
            return $this->redirectToRoute('panier');
        }
    }
}
