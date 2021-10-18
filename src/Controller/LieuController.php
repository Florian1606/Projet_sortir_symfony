<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;
use App\Entity\Lieu;
use App\Entity\Ville;

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
    public function addLieu(LieuRepository $repo, EntityManagerInterface $em, $nom,$rue, $ville, $longitude, $latitude)
    {

        $repoVille = $this->getDoctrine()->getRepository(Ville::class);

        $lieu = new Lieu();
        $lieu->setNomLieu($nom);
        $lieu->setRue($rue);
        $lieu->setVille($repoVille->find($ville));
        $lieu->setLongitude($longitude);
        $lieu->setLatitude($latitude);

        $em->persist($lieu);
        $em->flush();

        return $this->render('sortie/index.html.twig', [
            'controller_name' => 'LieuController',
        ]);


    }

}
