<?php

namespace App\Controller\Gestion;


use App\Entity\Achat;
use App\Entity\Album;
use App\Entity\Carousel;
use App\Entity\Client;
use App\Entity\Format;
use App\Entity\Personne;
use App\Entity\Photo;
use App\Entity\Presse;
use Sonata\CoreBundle\Form\Type\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class GestionController extends Controller
{

    /**
     * @Route("/gestion-site", name="gestion-site")
     */
    public function index() {
        return $this->redirectToRoute('gestion-site-albums');
    }

    /**
     * @Route("/gestion-site/album", name="gestion-site-albums")
     */
    public function albums(Request $request) {

        $albums = $this->getDoctrine()
            ->getRepository(Album::class)
            ->findAll();

        $album = new Album();
        $album->setAlbumDate(new \DateTime());

        $form = $this->createFormBuilder($album)
            ->add('albumName', TextType::class,
                array(
                    'label' => false,
                    'attr' => array(
                        'placeholder' => 'Nom de l\'album',
                        'class' => 'col l4 m4 s12 contactInput browser-default'
                    )
                ))
            ->add('category', ChoiceType::class,
                array(
                    'choices' => array(
                        'Album payant' => 'payant',
                        'Album gratuit' => 'free',
                        'Album payant protégé' => 'payant_protected',
                        'Album gratuit protégé' => 'free_protected',
                    ),
                    'label' => false,
                    'attr' => array(
                        'placeholder' => 'Nom de l\'album',
                        'class' => 'col l3 m3 offset-l1 offset-m1 s12 contactInput'
                    )
                ))
            ->add('add', SubmitType::class, array('label' => 'Ajouter l\'album','attr' => array('class' => 'col l3 m3 offset-l1 offset-m1 s12 buttonGerer noInputStyle button buttonGreen')))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $album = $form->getData();
            if (strpos($album->getCategory(),'protected') !== false) {
                $album->setPassword(md5(uniqid()));
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($album);
            $entityManager->flush();
            $this->get('session')->getFlashBag()->add('success',"Album " . $album->getAlbumName() . " créé");
            return $this->redirectToRoute('gestion-site-albums');
        }

        return $this->render('gestion/album.html.twig',array(
            'form' => $form->createView(),
            'albums' => $albums,
            ''
        ));
    }


    /**
     * @Route("/gestion-site/prix", name="gestion-site-prix")
     */
    public function prix(Request $request) {

        $formats = $this->getDoctrine()
            ->getRepository(Format::class)
            ->findAll();

        $format = new Format();

        $form = $this->createFormBuilder($format)
            ->add('categorie', ChoiceType::class,
                array(
                    'label' => false,
                    'choices'  => array(
                        'Professionnel' => 'professionnel',
                        'Particulier' => 'particulier',
                    ),
                    'attr' => array(
                        'placeholder' => 'Categorie',
                        'class' => 'col l2 m2 s12 contactInput browser-default'
                    )
                ))
            ->add('ratioTaille', RangeType::class,
                array(
                    'label' => false,
                    'label_format' => false,
                    'attr' => array(
                        'step' => '0.01',
                        'max' => '1',
                        'min' => '0.01',
                        'placeholder' => 'Ratio de taille',
                        'oninput' => 'changeRatio(this)',
                        'class' => 'col l2 m2 s11 rangeInput contactInput browser-default'
                    )
                ))
            ->add('prix', IntegerType::class,
                array(
                    'label' => false,
                    'attr' => array(
                        'placeholder' => 'Prix',
                        'class' => 'col l2 m2 s12 offset-l1 offset-m1 contactInput browser-default'
                    )
                ))
            ->add('add', SubmitType::class, array('label' => 'Ajouter','attr' => array('class' => 'col l2 m2 s12  offset-l1 offset-m1  buttonGerer noInputStyle button buttonGreen')))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ( $form->isValid()) {
                $format = $form->getData();
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($format);
                $entityManager->flush();
                $this->get('session')->getFlashBag()->add('success','Tarification ajoutée');
                return $this->redirectToRoute('gestion-site-prix');
            }else {
                $this->get('session')->getFlashBag()->add('error','Erreur de saisie');
            }
        }

        return $this->render('gestion/prix.html.twig',array(
            'form' => $form->createView(),
            'formats' => $formats
        ));
    }

    /**
     * @Route("/gestion-site/albums/{id}/ajouter-personne", name="ajouterPersonne")
     */
    public function ajouterPersonne(Photo $photo, Request $request) {

        $personnes = $this->getDoctrine()
            ->getRepository(Personne::class)
            ->findBy(array(
                'photo' => $photo
            ));

        $personne = new Personne();
        $personne->setPhoto($photo);

        $form = $this->createFormBuilder($personne)
            ->add('nom', TextType::class,
                array(
                    'label' => false,
                    'attr' => array(
                        'placeholder' => 'Nom',
                        'class' => 'col l3 m3 s12 contactInput browser-default'
                    )
                ))
            ->add('prenom', TextType::class,
                array(
                    'label' => false,
                    'attr' => array(
                        'placeholder' => 'Prenom',
                        'class' => 'col l3 m3 offset-l1 offset-m1 s12 contactInput browser-default'
                    )
                ))
            ->add('add', SubmitType::class, array('label' => 'Ajouter personne','attr' => array('class' => 'col l4 m4 offset-l1 offset-m1 s12 buttonGerer noInputStyle button buttonGreen')))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $personne = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($personne);
            $entityManager->flush();
            $this->get('session')->getFlashBag()->add('success',$personne->getNom()." ". $personne->getPrenom() . " ajoutée");
            return $this->redirectToRoute('ajouterPersonne',array('id'=>$photo->getId()));
        }

        return $this->render('gestion/personnes.html.twig',array(
            'form' => $form->createView(),
            'personnes' => $personnes,
            'photo' => $photo
        ));
    }

    /**
     * @Route("/gestion-site/albums/{idPhoto}/delete-personne/{id}", name="deletePersonne")
     */
    public function deletePersonne($idPhoto,Personne $personne) {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($personne);
        $entityManager->flush();
        $this->get('session')->getFlashBag()->add('success',$personne->getNom() . ' ' . $personne->getPrenom() . ' enlevée');
        return $this->redirectToRoute('ajouterPersonne',array('id' => $idPhoto ));
    }

    /**
     * @Route("/gestion-site/delete-album/{id}", name="deleteAlbum")
     */
    public function deleteAlbum(Album $album) {
        $album = $this->getDoctrine()->getRepository(Album::class)->find($album->getId());
        if (count($album->getPhotos())){
            $this->get('session')->getFlashBag()->add('error','L\'album contient des photos et ne peut donc être supprimé');
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($album);
            $entityManager->flush();
            $this->get('session')->getFlashBag()->add('success','Album supprimé');
        }
        return $this->redirectToRoute('gestion-site-albums');
    }

    /**
     * @Route("/gestion-site/achats", name="gestion-site-achats")
     */
    public function achats() {
        $achats = $this->getDoctrine()
            ->getRepository(Achat::class)
            ->findBy(array(), array('date' => 'DESC'));
        return $this->render('gestion/achat.html.twig',array(
            'achats' => $achats
        ));
    }

    /**
     * @Route("/gestion-site/clients", name="gestion-site-clients")
     */
    public function clients() {
        $clients = $this->getDoctrine()
            ->getRepository(Client::class)
            ->findAll();
        return $this->render('gestion/clients.html.twig',array(
            'clients' => $clients
        ));
    }

    /**
     * @Route("/gestion-site/delete-format/{id}", name="deleteFormat")
     */
    public function deleteFormat(Format $format) {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($format);
        $entityManager->flush();
        $this->get('session')->getFlashBag()->add('success','Tarification supprimée');
        return $this->redirectToRoute('gestion-site-prix');
    }

    /**
     * @Route("/gestion-site/album/{albumId}", name="gererAlbum")
     */
    public function gererAlbum(Request $request,$albumId) {
        $album = $this->getDoctrine()->getManager()->getRepository(Album::class)->find($albumId);
        $dateTime = new \DateTime();
        $form = $this->createFormBuilder()
            ->add('photos', FileType::class,
                array(
                    'label' => false,
                    'multiple' => true,
                    'attr' => array(
                        'class' => 'choisirPhoto',
                        'id' => 'uploadPhotoId',
                        'onchange'=> 'getFileName("form_photos")',
                        'accept' => '.jpg, .jpeg',
                        'multiple' => 'ltiple'
                    )
                ))
            ->add('add', SubmitType::class, array('label' => 'Ajouter la photo','attr' => array('class' => 'col l3 m3 s12 offset-l1 offset-m1 noInputStyle button buttonGreen')))
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $data = $form->getData();
                foreach ($data["photos"] as $photoForm) {
                    $photo = new Photo();
                    $photo->setPhotoDate($dateTime);
                    $photo->setAlbum($album);
                    $entityManager = $this->getDoctrine()->getManager();
                    /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
                    $file = $photoForm;
                    $extension = $file->guessExtension();
                    $fileName = md5(uniqid()) . '.' . $extension;
                    $clientFileName = preg_replace('/\\.[^.\\s]{3,4}$/', '', $file->getClientOriginalName());
                    $photo->setPhotoName($clientFileName);
                    $file->move(
                        $this->getParameter('photos_directory'),
                        $fileName
                    );
                    //Creation du watermark
                    $filePathWatermark = $this->getParameter('img_directory') . DIRECTORY_SEPARATOR . 'watermark.png';
                    $watermark = imagecreatefrompng($filePathWatermark);
                    //Changement de l'opacité
                    $opacity = 0.6;
                    imagealphablending($watermark, false);
                    imagesavealpha($watermark, true);
                    imagefilter($watermark, IMG_FILTER_COLORIZE, 0, 0, 0, 127 * $opacity);
                    //Collage du watermark
                    $im = imagecreatefromjpeg($this->getParameter('photos_directory') . DIRECTORY_SEPARATOR . $fileName);
                    imagecopy($im, $watermark, imagesx($im) - imagesx($watermark) - 10, imagesy($im) - imagesy($watermark) - 10, 0, 0, imagesx($watermark), imagesy($watermark));
                    $fileWatermarkName = md5(uniqid()) . '.' . $extension;
                    imagejpeg($im, $this->getParameter('watermarked_photos_directory') . DIRECTORY_SEPARATOR . $fileWatermarkName);
                    $photo->setPhoto($fileName);
                    $photo->setWatermark($fileWatermarkName);
                    $entityManager->persist($photo);
                    $entityManager->flush();
                    imagedestroy($im);
                    imagedestroy($watermark);
                }
                $message = count($data["photos"]) <= 1 ? 'Photo ajoutée' : 'Photos ajoutées';
                $this->get('session')->getFlashBag()->add('success', $message);
                return new RedirectResponse($this->generateUrl('gererAlbum', array('albumId' => $albumId)));
            }
        }
        return $this->render('gestion/photo.html.twig',array(
            'form' => $form->createView(),
            'album' => $album
        ));
    }

    /**
     * @Route("/gestion-site/album/{albumId}/delete-photo/{photoId}", name="deletePhoto")
     */
    public function deletePhoto($albumId, $photoId) {
        $photo = $this->getDoctrine()->getRepository(Photo::class)->find($photoId);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($photo);
        $filePath = $this->getParameter("photos_directory") . DIRECTORY_SEPARATOR . $photo->getPhoto();
        $filePathWatermark = $this->getParameter("watermarked_photos_directory") . DIRECTORY_SEPARATOR . $photo->getWatermark();
        if(file_exists($filePath)) {
            unlink($filePath);
            $this->get('session')->getFlashBag()->add('success','Photo supprimée');
        }
        if (file_exists($filePathWatermark)) {
            unlink($filePathWatermark);
        }
        $entityManager->flush();
        return new RedirectResponse($this->generateUrl('gererAlbum',array('albumId' => $albumId)));
    }

    /**
     * @Route("/gestion-site/presse/delete/{id}", name="deletePresse")
     */
    public function deletePresse(Presse $presse) {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($presse);
        $filePath = $this->getParameter("presse_directory") . DIRECTORY_SEPARATOR . $presse->getPhoto();
        if(file_exists($filePath)) {
            unlink($filePath);
            $this->get('session')->getFlashBag()->add('success','Photo supprimée');
        }
        $entityManager->flush();
        return $this->redirectToRoute('gestion-site-presse');
    }

    /**
     * @Route("/gestion-site/profil", name="gestion-site-profil")
     */
    public function profil(UserPasswordEncoderInterface $passwordEncoder,Request $request) {

        $user = $this->getUser();
        $form = $this->createFormBuilder()
            ->add('password', PasswordType::class,
                array(
                    'attr' => array(
                        'required' => 'false',
                        'placeholder' => 'Mot de passe',
                        'class' => 'col l4 m4 offset-l4 offset-m4 s12 contactInput browser-default'
                    )
                ))
            ->add('verification', PasswordType::class,
                array(
                    'attr' => array(
                        'required' => 'false',
                        'placeholder' => 'Verification',
                        'class' => 'col l4 m4 offset-l4 offset-m4 s12 contactInput browser-default'
                    )
                ))
            ->add('modifier', SubmitType::class, array(
                'label' => 'Modifier',
                'attr' => array(
                    'class' => 'col l4 m4 offset-l4 offset-m4 s12 buttonGerer noInputStyle button buttonGreen')
                ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $info = $form->getData();
            if ($info["password"] === $info["verification"]) {
                $user->setPassword($passwordEncoder->encodePassword($user, $info["password"]));
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->flush();
                $this->get('session')->getFlashBag()->add('success',"Profil modifié");
            } else {
                $this->get('session')->getFlashBag()->add('error',"Les mot de passe ne sont pas identiques");
            }
        }

        return $this->render('gestion/profil.html.twig',array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/gestion-site/presse", name="gestion-site-presse")
     */
    public function presse(Request $request) {

        $presses = $this->getDoctrine()
            ->getRepository(Presse::class)
            ->findAll();

        $presse = new Presse();

        $form = $this->createFormBuilder($presse)
            ->add('title',TextType::class,array(
                'label' => false,
                'attr' => array(
                    'placeholder' => 'Source',
                    'class' => 'col l3 m3 s12 contactInput browser-default'
                )
            ))
            ->add('photo', FileType::class,
                array(
                    'label' => false,
                    'attr' => array(
                        'class' => 'choisirPhoto',
                        'id' => 'uploadPhotoId',
                        'onchange'=> 'getFileName("form_photo")'
                    )
                ))
            ->add('add', SubmitType::class, array('label' => 'Ajouter','attr' => array('class' => 'col l2 m2 offset-l1 offset-m1 s12 buttonGerer noInputStyle button buttonGreen')))
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $presse = $form->getData();
            $file = $presse->getPhoto();
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();
            $presse->setPhoto($fileName);
            $file->move(
                $this->getParameter('presse_directory'),
                $fileName
            );
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($presse);
            $entityManager->flush();
            $this->get('session')->getFlashBag()->add('success',"Parution presse ajoutée");
            return $this->redirectToRoute('gestion-site-presse');
        }

        return $this->render('gestion/presse.html.twig',array(
            'form' => $form->createView(),
            'presses' => $presses
        ));
    }

    /**
     * @Route("/gestion-site/carousel", name="gestion-site-carousel")
     */
    public function carousel(Request $request) {

        $carousels = $this->getDoctrine()
            ->getRepository(Carousel::class)
            ->findAll();

        $carousel = new Carousel();

        $form = $this->createFormBuilder($carousel)
            ->add('photo', FileType::class,
                array(
                    'label' => false,
                    'attr' => array(
                        'class' => 'choisirPhoto',
                        'id' => 'uploadPhotoId',
                        'onchange'=> 'getFileName("form_photo")'
                    )
                ))
            ->add('add', SubmitType::class, array('label' => 'Ajouter','attr' => array('class' => 'col l2 m2 offset-l1 offset-m1 s12 buttonGerer noInputStyle button buttonGreen')))
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $carousel = $form->getData();
            $file = $carousel->getPhoto();
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();
            $carousel->setPhoto($fileName);
            $file->move(
                $this->getParameter('carousel_directory'),
                $fileName
            );
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($carousel);
            $entityManager->flush();
            $this->get('session')->getFlashBag()->add('success',"Photo carousel ajoutée");
            return $this->redirectToRoute('gestion-site-carousel');
        }

        return $this->render('gestion/carousel.html.twig',array(
            'form' => $form->createView(),
            'carousels' => $carousels
        ));
    }

    /**
     * @Route("/gestion-site/carousel/delete/{id}", name="deleteCarousel")
     */
    public function deleteCarousel(Carousel $carousel) {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($carousel);
        $filePath = $this->getParameter("carousel_directory") . DIRECTORY_SEPARATOR . $carousel->getPhoto();
        if(file_exists($filePath)) {
            unlink($filePath);
            $this->get('session')->getFlashBag()->add('success','Photo supprimée');
        }
        $entityManager->flush();
        return $this->redirectToRoute('gestion-site-carousel');
    }
}
