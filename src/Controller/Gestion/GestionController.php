<?php

namespace App\Controller\Gestion;


use App\Entity\Achat;
use App\Entity\Album;
use App\Entity\Client;
use App\Entity\Format;
use App\Entity\Photo;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GestionController extends Controller
{

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
                        'class' => 'col l5 m5 s12 contactInput browser-default'
                    )
                ))
            ->add('add', SubmitType::class, array('label' => 'Ajouter l\'album','attr' => array('class' => 'col l5 m5 offset-l2 offset-m2 s12 buttonGerer noInputStyle button buttonGreen')))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $album = $form->getData();
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
                        'class' => 'col l2 m2 s12 offset-l1 offset-m1 rangeInput contactInput browser-default'
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
            ->findAll();
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
        $photo = new Photo();
        $photo->setPhotoDate(new \DateTime());
        $photo->setAlbum($album);
        $form = $this->createFormBuilder($photo)
            ->add('photoName', TextType::class,
                array(
                    'label' => false,
                    'attr' => array(
                        'placeholder' => 'Nom de la photo',
                        'class' => 'col l4 m4 s12 contactInput browser-default'
                    )
                ))
            ->add('photo', FileType::class,
                array(
                    'label' => false,
                    'attr' => array(
                        'class' => 'choisirPhoto'
                    )
                ))
            ->add('add', SubmitType::class, array('label' => 'Ajouter la photo','attr' => array('class' => 'col l3 m3 s12 offset-l1 offset-m1 noInputStyle button buttonGreen')))
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photo = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $photo->getPhoto();
            $extension = $file->guessExtension();
            $fileName = md5(uniqid()).'.'. $extension;
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
            imagefilter($watermark, IMG_FILTER_COLORIZE, 0,0,0,127*$opacity);
            //Collage du watermark
            $im = imagecreatefromjpeg($this->getParameter('photos_directory') . DIRECTORY_SEPARATOR . $fileName);
            imagecopy($im, $watermark, imagesx($im) - imagesx($watermark) - 10, imagesy($im) - imagesy($watermark) - 10, 0, 0, imagesx($watermark), imagesy($watermark));
            $fileWatermarkName = md5(uniqid()).'.'. $extension;
            imagejpeg($im,$this->getParameter('watermarked_photos_directory') . DIRECTORY_SEPARATOR . $fileWatermarkName);
            $photo->setPhoto($fileName);
            $photo->setWatermark($fileWatermarkName);
            $entityManager->persist($photo);
            $entityManager->flush();
            $this->get('session')->getFlashBag()->add('success','Photo ajoutée');
            return new RedirectResponse($this->generateUrl('gererAlbum',array('albumId' => $albumId)));
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
}
