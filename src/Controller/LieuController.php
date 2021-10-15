<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LieuController extends AbstractController
{
    /**
     * @Route("/lieu", name="lieu")
     */
    public function index(): Response
    {
        return $this->render('lieu/index.html.twig', [
            'controller_name' => 'LieuController',
        ]);
    }
    /**
     * @Route("/lieu/add/{nom}/{rue}/{ville}/{longitude}/{latitude}",name="app_lieu_add")
     */
    public function addLieu(LieuRepository $repo, EntityManagerInterface $em, $nom,$rue, $ville, $longitude, $latitude): Response
    {

//        $em, $nom,$rue, $ville, $longitude, $latitude

        $lieu = new Lieu();
        $lieu->setNomLieu($nom);
        $lieu->setRue($rue);

        $lieu->setLongitude($longitude);
        $lieu->setLatitude($latitude);

//        $lieu = $repo->find($id);


        $repoV = $this->getDoctrine()->getRepository(Ville::class);
        $idVille = $lieu->getVille()->getId();

        $ville = $repoV->findOneBy(array('id' => $lieu->getVille()->getId()));


        return $this->json('{"cp":"' . $ville->getCodePostal() . '","ville":"' . $ville->getNomVille() . '","rue":"' . $lieu->getRue() . '","lat":"' . $lieu->getLatitude() . '","long":"' . $lieu->getLongitude() . '"}');
    }

}
