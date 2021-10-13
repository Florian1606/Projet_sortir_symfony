<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    /**
     * @Route("/sortie", name="sortie")
     */
    public function index(): Response
    {
        return $this->render('sortie/index.html.twig', [
            'controller_name' => 'SortieController',
        ]);
    }

    /**
     * @Route("/sortie/add", name="app_sortie_ajouter")
     */
    public function ajouterSortie(EntityManagerInterface $em, Request $request): Response
    {

        $sortie = new Sortie;
        //        backslash pour indiquer une fonction PHP
        $sortie->setDateLimiteInscription(new \DateTime("now"));
        $sortie->setDateDebut(new \DateTime("now"));

//        $sortie->setEtat("Créé");
        $form = $this->createForm(SortieType::class, $sortie);

        // remplire l'objet sortie
        $form->handleRequest($request);
        // Controle si les données sont valides et si le formulaire est soumis.
        if ($form->isSubmitted() && $form->isValid()) {
            // ajout de $sortie dans la transaction
            $em->persist($sortie);
            // fait la transaction : réalise insert into dans la bdd
            $em->flush();
            $this->addFlash('success', 'Création de la sortie');
            //            TODO Mettre à jour la redirection.
            return $this->redirectToRoute("main");
        }
        $error = "Problème !!";
        $titre = "App sortie";

        $tab = compact("titre", "error");
        $tab["formSortie"] = $form->createView();
        //            TODO Mettre à jour la redirection.
        return $this->render('sortie/index.html.twig', $tab);
    }

    /**
     * @Route("/sortie/add", name="app_sortie_annuler")
     */
    public function annulerSortie(EntityManagerInterface $em, SortieRepository $repo, $id): Response
    {
        $sortie = $repo->find($id);
        $sortie->setEtat();
        $em->flush();

        return $this->redirectToRoute('');

    }

    /**
     *@Route("/afficherSortie",name="app_afficherSortie")
     */
    public function afficherSortie(Request $request):Response{
        $titre= "Sortir.com - afficher une sortie";
        $tab = compact("titre");
        return $this->render("sortie/afficherUneSortie.html.twig",$tab);
    }

    /**
     *@Route("/annulerUneSortie",name="app_annulerUneSortie")
     */
    public function annulerUneSortie(Request $request):Response{
        $titre= "Sortir.com - annuler une sortie";
        $tab = compact("titre");
        return $this->render("sortie/annulerUneSortie.html.twig",$tab);
    }

    /**
     *@Route("/gererLesSites",name="gererLesSites")
     */
    public function legal(Request $request):Response{
        $titre= "Sortir.com - gérer les différents sites";
        $tab = compact("titre");
        return $this->render("sortie/gererLesSites.html.twig",$tab);
    }

    /**
     *@Route("/gererLesVilles",name="gererLesVilles")
     */
    public function contact(Request $request):Response{
        $titre= "Sortir.com - gérer les différents villes";
        $tab = compact("titre");
        return $this->render("sortie/gererLesVilles.html.twig",$tab);
    }
    
}
