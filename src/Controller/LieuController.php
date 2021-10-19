<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;
use App\Entity\Lieu;
use App\Entity\Ville;
use App\Form\LieuType;

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

    /**
     * @Route("/lieu/add",name="app_lieu_add_azert")
     */
    public function azerty( Request $request,EntityManagerInterface $em) : Response
    {


            // Créer une instance de lieu
            $lieu = new Lieu();
            $lieuForm = $this->createForm(LieuType::class,$lieu);
            $lieuForm->handleRequest($request);
            if($lieuForm->isSubmitted() && $lieuForm->isValid()){
                $em->persist($lieu);
                $em->flush();
                return $this->json('{"status":201}');
            }


        // persist

        return $this->render('lieu/index.html.twig', [
            'controller_name' => 'LieuController',
        ]);
    }

    /**
     * @Route("/lieu/delete/{id}",name="app_lieu_delete")
     */
    public function deleteLieu(LieuRepository $repo, EntityManagerInterface $em, $id)
    {

        // Récupère le current user.
        $user = $this->get('security.token_storage')->getToken()->getUser();
        // Récupère le lieu suivant l'id passé en paramètre.
        $lieu = $repo->find($id);

        $tabSorties =$lieu->getSorties();


        // Vérification des droits
        // Doit être un admin et ne doit pas avoir de sortie
        if ($user->getIsAdmin() && count($tabSorties) == 0 ) {
            $this->addFlash('success', "Lieu : ".$lieu->getNomLieu()." supprimé !");
            $em = $this->getDoctrine()->getManager();
            $em->remove($lieu);
            $em->flush();
        } else {
            $this->addFlash('danger', "Lieu : ".$lieu->getNomLieu()." ne peut être supprimé ! (Rattaché à des sorties)");


        }

        return $this->redirectToRoute("app_admin_lieux");
    }





}
