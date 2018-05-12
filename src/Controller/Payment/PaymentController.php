<?php

namespace App\Controller\Payment;

use App\Entity\Format;
use App\Entity\Photo;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
     * @Route("/achat", name="achat")
     */
    public function achat() {
        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                $this->getParameter('paypal_api_key'),
                $this->getParameter('paypal_secret')
            )
        );
        $payment = Payment::get($_GET["paymentId"],$apiContext);
        $execution = (new PaymentExecution())
            ->setPayerId($_GET["PayerID"])
            ->setTransactions($payment->getTransactions());
        try {
            $payment->execute($execution,$apiContext);
            print_r("Paiement effectué");
        } catch (PayPalConnectionException $e) {
            var_dump(json_decode($e->getData()));
        }
        return $this->render('payment/acheter.html.twig', [
            'controller_name' => 'PaymentController'
        ]);
    }

    /**
     * @Route("/acheter", name="acheter")
     */
    public function buy() {
        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                $this->getParameter('paypal_api_key'),
                $this->getParameter('paypal_secret')
            )
        );

        $payment = new Payment();
        $payment->setIntent("sale");
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($this->generateUrl('achat',array(),UrlGeneratorInterface::ABSOLUTE_URL) );
        $redirectUrls->setCancelUrl($this->generateUrl('panier',array(),UrlGeneratorInterface::ABSOLUTE_URL));
        $payment->setRedirectUrls($redirectUrls);
        $payment->setPayer(
            (new Payer())->setPaymentMethod('paypal')
        );

        $list = new ItemList();
        $item = (new Item())
            ->setPrice("12")
            ->setQuantity(1)
            ->setName("Nom du produit")
            ->setCurrency("EUR");
        $list->addItem($item);

        $details = (new Details())
            ->setSubtotal(12);

        $amout = (new Amount())
            ->setTotal(12)
            ->setDetails($details)
            ->setCurrency("EUR");

        $transaction = (new Transaction())
            ->setItemList($list)
            ->setDescription('Achat de photo')
            ->setCustom("api_verification_exemple")
            ->setAmount($amout);

        $payment->setTransactions(array(
            $transaction
        ));
        try {
            $payment->create($apiContext);
            return $this->redirect($payment->getApprovalLink());
        } catch (PayPalConnectionException $e) {
            var_dump(json_decode($e->getData()));
        }
        return $this->render('payment/acheter.html.twig', [
            'controller_name' => 'PaymentController'
        ]);
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
