<?php

namespace App\Controller\Payment;

use App\Entity\Achat;
use App\Entity\Client;
use App\Entity\Format;
use App\Entity\Photo;
use DateTime;
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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymentController extends Controller
{

    public function getPanierFromSession() {
        $panierSession = $this->get('session')->get('panier');
        $repositoryPhoto =  $this->getDoctrine()->getRepository(Photo::class);
        $repositoryFormat =  $this->getDoctrine()->getRepository(Format::class);
        $panier = array();
        if ($panierSession !== null) {
            foreach ($panierSession as $photoId => $formats) {
                $photo = $repositoryPhoto->find($photoId);
                list($width,$height) = getimagesize($this->getParameter('photos_directory') . DIRECTORY_SEPARATOR . $photo->getPhoto());
                foreach ($formats as $format) {
                    $format = $repositoryFormat->find($format);
                    $panier[] = array(
                        "photo" => $photo,
                        "format" => $format,
                        "width" => (int) ($format->getRatioTaille() * $width),
                        "height" => (int) ($format->getRatioTaille() * $height)
                    );
                }
            }
        }
        return $panier;
    }

    /**
     * @Route("/panier", name="panier")
     */
    public function panier()
    {
        $panier = $this->getPanierFromSession();
        return $this->render('payment/panier.html.twig', [
            'controller_name' => 'PaymentController',
            'panier'=> $panier
        ]);
    }

    public function viderPanier() {
        $session = $this->get('session');
        if ($session == null ) {
            $session->start();
        }
        $session->set('panier', null);
    }

    /**
     * @Route("/photos/abandonner-panier", name="abandonnerPanier")
     */
    public function abandonnerPanier() {
        $this->viderPanier();
        return $this->redirectToRoute('accueil');
    }

    /**
     * @Route("/acheter/email", name="email")
     */
    public function email(Request $request) {
        $client = new Client();

        $form = $this->createFormBuilder($client)
            ->add('email', TextType::class, array("label" => false,'attr' => array('class' => 'emailInput browser-default','placeholder' => 'Email')))
            ->add('save', SubmitType::class, array('label' => 'Continuer achat','attr' => array('class' => 'noInputStyle button buttonGreen center-align')))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $client = $form->getData();
            $clientSearched = $this->getDoctrine()->getRepository(Client::class)->findBy(array(
                'email' => $client->getEmail()
            ));
            if (count($clientSearched)  == 0) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($client);
                $entityManager->flush();
            } else {
                $client = $clientSearched[0];
            }
            $this->get('session')->set("clientId",$client->getId());

            return $this->redirectToRoute('acheter');
        }

        return $this->render('payment/email.html.twig', [
            'controller_name' => 'PaymentController',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/achat", name="achat")
     */
    public function achat(\Swift_Mailer $mailer) {
        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                $this->getParameter('paypal_api_key'),
                $this->getParameter('paypal_secret')
            )
        );
        if (!(isset($_GET["paymentId"]) && isset($_GET["PayerID"]))) {
            throw new \Exception('Quelque chose s\' est mal passé !');
        }
        $payment = Payment::get($_GET["paymentId"],$apiContext);
        $transactions = $payment->getTransactions();
        $execution = (new PaymentExecution())
            ->setPayerId($_GET["PayerID"])
            ->setTransactions($transactions);
        $paymentValidated = false;
        $links = array();
        try {
            $paymentUniqueId = $this->get("session")->get("paymentUniqueId");
            if ($paymentUniqueId == $transactions[0]->getCustom()){
                $payment->execute($execution,$apiContext);
                $paymentValidated = true;
                $panier = $this->getPanierFromSession();
                //Resize the photo
                foreach ($panier as $photo) {
                    $filePathFullPhoto = $this->getParameter("photos_directory")
                        . DIRECTORY_SEPARATOR
                        . $photo["photo"]->getPhoto();
                    $ext = pathinfo($filePathFullPhoto, PATHINFO_EXTENSION);
                    $fileName = hash("sha256",$photo["photo"]->getPhoto() . $photo["format"]->getId()) . '.' . $ext ;;
                    $filePath = $this->getParameter("photos_directory")
                        . DIRECTORY_SEPARATOR
                        . $fileName;
                    list($width,$height) = getimagesize($filePathFullPhoto);
                    if (!file_exists($filePath)) {
                        $src = imagecreatefromjpeg($filePathFullPhoto);
                        $dst = imagecreatetruecolor($photo["width"], $photo["height"]);
                        imagecopyresampled($dst, $src, 0, 0, 0, 0, $photo["width"], $photo["height"], $width, $height);
                        imagejpeg ( $dst,  $filePath );
                    }
                    $links[] = $fileName;
                }
                $clientId = $this->get('session')->get("clientId");
                $client = $this->getDoctrine()->getRepository(Client::class)->find($clientId);
                $message = (new \Swift_Message('Achat de photos'))
                    ->setFrom('photosportnormandy@gmail.com')
                    ->setTo($client->getEmail())
                    ->setBody(
                        $this->renderView(
                            'emails/payment_done.html.twig',
                            array('links' => $links)
                        ),
                        'text/html'
                    )
                ;
                $mailer->send($message);
                $achat = new Achat();
                $achat->setClient($client);
                $achat->setDate(new DateTime());
                $achat->setPrix($this->get('session')->get('prix'));
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($achat);
                $entityManager->flush();
                $this->viderPanier();
                $this->get('session')->getFlashBag()->add('success','Paiement effectué avec succès, photos envoyées à l\'addresse ' . $client->getEmail());
            } else {
                $this->get('session')->getFlashBag()->add('error','Erreur lors du paiement, paiement non effectué');
            }
        } catch (PayPalConnectionException $e) {
//            var_dump(json_decode($e->getData()));
            throw new \Exception('Erreur lors du paiement');
        }

        return $this->render('payment/acheter.html.twig', [
            'controller_name' => 'PaymentController',
            'links' => $links,
            'isPaymentValidated' => $paymentValidated
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
        $panier = $this->getPanierFromSession();
        $prixTotal = 0;
        foreach ($panier as $product) {
            $item = (new Item())
                ->setName($product["photo"]->getPhotoName() . ' ' . $product["width"] . 'x' . $product["height"])
                ->setCurrency("EUR")
                ->setPrice($product["format"]->getPrix())
                ->setQuantity(1);
            $prixTotal += $product["format"]->getPrix();
            $list->addItem($item);
        }

        $details = (new Details())
            ->setSubtotal($prixTotal);

        $amout = (new Amount())
            ->setTotal($prixTotal)
            ->setDetails($details)
            ->setCurrency("EUR");

        $uniqueId = md5(uniqid());
        $this->get("session")->set("paymentUniqueId",$uniqueId);
        $this->get("session")->set("prix",$prixTotal);

        $transaction = (new Transaction())
            ->setItemList($list)
            ->setDescription('Achat de photo')
            ->setCustom($uniqueId)
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
            'controller_name' => 'PaymentController',
            'isPaymentValidated' => false
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
