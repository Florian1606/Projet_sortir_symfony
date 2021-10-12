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

        $form = $this->createForm(SortieType::class, $sortie);

        $form->handleRequest($request);
        // Controle si les données sont valides et si le formulaire est soumis.

        if ($form->isSubmitted() && $form->isValid()) {
            $sortie->setDateDebut(new \DateTime($request->request->get("dateDebut")));
            $sortie->setDateLimiteInscription(new \DateTime($request->request->get("dateLimiteInscription")));


//           if($form->get('save')->isClicked()){
//               $sortie->setEtat(new Etat());
//           }
//
//            if($form->get('add')->isClicked()){
//                $sortie->setEtat(new Etat());
//            }


            // ajout de $sortie dans la transaction
            $em->persist($sortie);
            // fait la transaction : réalise insert into dans la bdd
            $em->flush();
            $this->addFlash('success', 'Création de la sortie');
            $error = "C'est bon !!";
            //            TODO Mettre à jour la redirection.
            //return $this->redirectToRoute("main");
        }
        $error = "C'est pas bon!!";
        $titre = "App sortie";

        $tab = compact("titre", "error");
        $tab["formSortie"] = $form->createView();
        //            TODO Mettre à jour la redirection.
        return $this->render('sortie/index.html.twig', $tab);
    }



}
