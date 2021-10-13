<?php

namespace App\Controller;


use App\Entity\Sortie;
use App\Entity\Etat;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Validator\Validator\ValidatorInterface;


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
    public function ajouterSortie(EntityManagerInterface $em, Request $request, EtatRepository $etat): Response
    {
        // Instance de la class Sortie
        $sortie = new Sortie();
        // Création du formulaire depuis l'entité Sortie
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        // Contrôle si les données sont valides et si le formulaire est soumis.
        if ($form->isSubmitted() && $form->isValid()) {
            //!\\ Backslash pour indiquer une fonction PHP //!\\
            $repo = $this->getDoctrine()->getRepository(Etat::class);
            $sortie->setDateDebut(new \DateTime($request->request->get("dateDebut")));
            $sortie->setDateLimiteInscription(new \DateTime($request->request->get("dateLimiteInscription")));
            if ($form->get('add')->isClicked()) {
                $sortie->setEtat($repo->find(1));
                $this->addFlash('success', 'Sortie publiée !');
            }
            if ($form->get('save')->isClicked()) {
                $sortie->setEtat($repo->find(2));
                $this->addFlash('success', 'Sortie enregistrée !');
            }
            $em->persist($sortie);
            $em->flush();
            return $this->redirectToRoute("main");
        }
        $errors = $validator->validate($sortie);
dump($errors);
        $titre = "Création d'une sortie";

        $tab = compact("titre", "errors");
        $tab["formSortie"] = $form->createView();

        return $this->render('sortie/index.html.twig', $tab);
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
