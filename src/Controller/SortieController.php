<?php

namespace App\Controller;

use App\Repository\SiteRepository;
use App\Entity\Sortie;
use App\Entity\Participant;
use App\Entity\Etat;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Symfony\Component\Validator\Validator\ValidatorInterface;


class SortieController extends AbstractController
{

    /**
     * @Route("/sortie/add", name="app_sortie_add")
     */
    public function addSortie(EntityManagerInterface $em, Request $request, EtatRepository $etat, ValidatorInterface $validator): Response
    {
        // Instance de la class Sortie
        $sortie = new Sortie();
        // Création du formulaire depuis l'entité Sortie
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        // Si button cancel is clicked -> retour vers la home page
        if ($form->get('cancel')->isClicked()) {
            return $this->redirectToRoute("main");
        }
        // Contrôle si les données sont valides et si le formulaire est soumis.
        if ($form->isSubmitted() && $form->isValid()) {
            $repoPart = $this->getDoctrine()->getRepository(Participant::class);
            // app current user -> organisateur
            $sortie->setOrganisateur($repoPart->find($request->request->get("idCurrentUser")));

            //!\\ Backslash pour indiquer une fonction PHP //!\\
            $sortie->setDateDebut(new \DateTime($request->request->get("dateDebut")));
            $sortie->setDateLimiteInscription(new \DateTime($request->request->get("dateLimiteInscription")));
            
            // Set etat suivant event
            $repo = $this->getDoctrine()->getRepository(Etat::class);
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
        $titre = "Création d'une sortie";

        $tab = compact("titre", "errors");
        $tab["formSortie"] = $form->createView();

        return $this->render('sortie/index.html.twig', $tab);
    }

    /**
     * @Route("/sortie/display/{id}",name="app_sortie_display")
     */
    public function displaySortie(SortieRepository $repo, Request $request, $id = 0): Response
    {
        $sortie = $repo->find($id);
        $titre = "Sortir.com - " . $sortie->getNom();
        $tab = compact("titre", "sortie");
        return $this->render("sortie/afficherUneSortie.html.twig", $tab);
    }

    /**
     * @Route("/sortie/cancel/{id}",name="app_sortie_cancel")
     */
    public function cancelSortie(SortieRepository $repo, EntityManagerInterface $em, $id): Response
    {
        $sortie = $repo->find($id);
        $repoEtat = $this->getDoctrine()->getRepository(Etat::class);
        $datenow = new \DateTime("now");
        if ($sortie->getDateDebut() > $datenow) {
            $sortie->setEtat($repoEtat->find(6));
            $em->flush();
            $this->addFlash('success', 'Sortie Annulée !');
            return $this->redirectToRoute("main");
       }
        $this->addFlash('warning', 'Vous ne pouvez pas annuler la sortie !');
        return $this->render("main");
    }


    /**
     * @Route("/sortie/update/{id}",name="app_sortie_update")
     */
    public function updateSortie(SortieRepository $repo, Request $request, $id = 0): Response
    {
        $sortie = $repo->find($id);
        $form = $this->createForm(SortieType::class, $sortie);
        $titre = "Sortir.com - " . $sortie->getNom()." - Modification";
        $tab = compact("titre", "sortie");
        $tab["formSortie"] = $form->createView();
        return $this->render("sortie/modifierSortie.html.twig", $tab);
    }





}
