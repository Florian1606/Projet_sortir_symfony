<?php

namespace App\Controller;

use App\Repository\SiteRepository;
use App\Entity\Sortie;
use App\Entity\Participant;
use App\Entity\Etat;
use App\Entity\Ville;
use App\Entity\Lieu;
use App\Entity\Site;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Form\LieuType;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function PHPUnit\Framework\isEmpty;

class SortieController extends AbstractController
{

    /**
     * @Route("/sortie/add", name="app_sortie_add")
     */
    public function addSortie(EntityManagerInterface $em, Request $request, EtatRepository $etat, ValidatorInterface $validator): Response
    {
        $repoVille = $this->getDoctrine()->getRepository(Ville::class);
        $villes = $repoVille->findAll();

        // Instance de la class Sortie
        $sortie = new Sortie();

        // Création du formulaire depuis l'entité Sortie
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        // Si button cancel is clicked -> retour vers la home page
        if ($form->get('cancel')->isClicked()) {
            return $this->redirectToRoute("main");
        }

        // VERIF DATE 
        $dateNow =  new \DateTime("now");
        $dateNow = $dateNow->getTimestamp();
        $dateDebut = $request->request->get("dateDebut");
        $dateDebutUnix = strtotime($request->request->get("dateDebut"));
        $dateLimiteInscription = $request->request->get("dateLimiteInscription");
        $dateLimiteInscriptionUnix = strtotime($request->request->get("dateLimiteInscription"));

        $msg_error = [];
        if ($dateDebut != null) {
            if ($dateDebutUnix > $dateNow) {
                $sortie->setDateDebut(new \DateTime($dateDebut));
            } else
                array_push($msg_error, "La date de sortie doit être supérieur ou égale à la date d'aujourd'hui");
        } else {
            array_push($msg_error, "Veuillez saisir une date de début");
        }


        // VERIF DATE FIN
        if ($dateLimiteInscription != null) {
            if ($dateLimiteInscriptionUnix > $dateNow) {
                if ($dateLimiteInscriptionUnix < $dateDebutUnix) {
                    $sortie->setDateDebut(new \DateTime($dateLimiteInscription));
                } else
                    array_push($msg_error, "La date de limite d'inscription doit être strictement inférieur à la date de début de la sortie");
            } else
                array_push($msg_error, "La date de limite d'inscription doit être supérieur ou égale à la date d'aujourd'hui");
        } else
            array_push($msg_error, "Veuillez saisir une date de limite d'inscription");



        // Contrôle si les données sont valides et si le formulaire est soumis.
        if ($form->isSubmitted() && $form->isValid() && isEmpty($msg_error)) {
            // app current user -> organisateur
            $sortie->setOrganisateur($this->getUser());
            //!\\ Backslash pour indiquer une fonction PHP //!\\
            $sortie->setDateLimiteInscription(new \DateTime($request->request->get("dateLimiteInscription")));
            $repoSite = $this->getDoctrine()->getRepository(Site::class);
            $sortie->setSite($repoSite->find($this->getUser()->getIdSite()->getId()));
            // Set etat suivant event
            $repo = $this->getDoctrine()->getRepository(Etat::class);

            // Publier isClicked
            if ($form->get('add')->isClicked()) {
                $sortie->setEtat($repo->find(1));
                $this->addFlash('success', 'Sortie publiée !');
                $em->persist($sortie);
                $em->flush();
            }
            // Enregistrer isClicked
            if ($form->get('save')->isClicked()) {
                $sortie->setEtat($repo->find(2));
                $this->addFlash('success', 'Sortie enregistrée !');
                $em->persist($sortie);
                $em->flush();
            }
            return $this->redirectToRoute("main");
        }
        // Récupère le erreurs
        $errors = $validator->validate($sortie);
        $titre = "Création d'une sortie";

        $tab = compact("titre", "errors", 'villes', 'msg_error');
        $tab["formSortie"] = $form->createView();

        // Créé et rend le formulaire pour l'affichage sur la page
        $lieu = new Lieu();
        $lieuForm = $this->createForm(LieuType::class, $lieu);
        $tab['lieuForm'] = $lieuForm->createView();

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
    public function cancelSortie(Request $request, SortieRepository $repo, EntityManagerInterface $em, $id): Response
    {
        $sortie = $repo->find($id);
        $user = $this->getUser();
        if ($user == $sortie->getOrganisateur() || $user->getIsAdmin()) {
            if ($request->get('btn-cancel') != null) {
                $repoEtat = $this->getDoctrine()->getRepository(Etat::class);
                $datenow = new \DateTime("now");
                if ($sortie->getDateDebut() >= $datenow) {
                    $sortie->setEtat($repoEtat->find(6));
                    $sortie->setMotifAnnulation($request->get('motif'));
                    $em->flush();
                    $this->addFlash('success', 'Sortie Annulée !');
                    return $this->redirectToRoute("main");
                }
                $this->addFlash('warning', 'Vous ne pouvez pas annuler la sortie !');
            }
        } else {
            $this->addFlash('warning', "Vous n'avez pas les droits pour annuler cette sortie");
            return $this->redirectToRoute("main");
        }



        return $this->render("sortie/annulerUneSortie.html.twig", [
            'sortie' => $sortie,
        ]);
    }

    /**
     * @Route("/sortie/unsubscribe/{idUser}/{idSortie}",name="app_sortie_unsubscribe")
     */
    public function unsubscribeSortie(SortieRepository $repo, EntityManagerInterface $em, $idUser, $idSortie): Response
    {
        $user = $this->getUser();
        $sortie = $repo->find($idSortie);
        if ($user == $sortie->getOrganisateur() || $user->getIsAdmin()) {
            
        }
        $sortie = $repo->find($idSortie);
        $repoParticipant = $this->getDoctrine()->getRepository(Participant::class);
        $user = $repoParticipant->find($idUser);
        $sortie->removeParticipant($user);
        $em->flush();
        $this->addFlash('success', 'Vous êtes désinscrit de la sortie !');
        return $this->redirectToRoute("main");
    }

    /**
     * @Route("/sortie/subscribe/{idUser}/{idSortie}",name="app_sortie_subscribe")
     */
    public function subscribeSortie(SortieRepository $repo, EntityManagerInterface $em, $idUser, $idSortie): Response
    {

        $sortie = $repo->find($idSortie);
        $repoParticipant = $this->getDoctrine()->getRepository(Participant::class);
        $user = $repoParticipant->find($idUser);
        $sortie->addParticipant($user);
        $em->flush();
        $this->addFlash('success', 'Vous avez était inscrit à la sortie !');
        return $this->redirectToRoute("main");
    }

    /**
     * @Route("/sortie/delete/{id}",name="app_sortie_delete")
     * @param SortieRepository $repo
     * @param EntityManagerInterface $em
     * @param $id
     * @return Response
     */
    public function deleteSortie(SortieRepository $repo, EntityManagerInterface $em, $id): Response
    {
        // Récupère le current user.
        $user = $this->get('security.token_storage')->getToken()->getUser();

        // Récupère la sortie suivant l'id passé en paramètre.
        $sortie = $repo->find($id);
        $idEtat = $sortie->getEtat()->getId();

        // Vérification des droits
        // Doit être l'organisateur ou un admin.
        if ($user == $sortie->getOrganisateur() || $user->getIsAdmin()) {

            if ($idEtat != 1 || !$user->getIsAdmin()) {
                $this->addFlash('warning', "Il n'est plus possible de supprimer cette sortie");
                return $this->redirectToRoute("main");
            } else {
                $repoEtat = $this->getDoctrine()->getRepository(Etat::class);
                $sortie->setEtat($repoEtat->find(7));
                $em->flush();
                $this->addFlash('success', 'Sortie supprimée !');
                return $this->redirectToRoute("main");
            }
        } else {
            $this->addFlash('warning', "Vous n'avez pas les droits pour supprimer cette sortie");
            return $this->redirectToRoute("main");
        }
    }

    /**
     * @Route("/sortie/update/{id}",name="app_sortie_update")
     */
    public function updateSortie(SortieRepository $repo, Request $request, EntityManagerInterface $em, $id = 0): Response
    {

        $sortie = $repo->find($id);
        $form = $this->createForm(SortieType::class, $sortie);
        $repoEtat = $this->getDoctrine()->getRepository(Etat::class);


        // Annuler -> Retour home page
        if ($request->request->get("cancel")) {
            return $this->redirectToRoute("main");
        }

        // IsClicked sur bouton "supprimer"
        if ($request->request->get("supprimer")) {
            $this->deleteSortie($id);
        }

        // Enregistrer

        if ($request->request->get("save")) {

            // #################################################################
            $sortie = $repo->find($id);
            $form = $this->createForm(SortieType::class, $sortie);
            $form->handleRequest($request);
            dump($form->isSubmitted());
            dump($form->isValid());
            // verifier si on a soumis le form et si les donnes valide
            if ($form->isSubmitted() && $form->isValid()) {
                // génerer sql insert into et ajouter dans queue
                $sortie->setDateDebut(new \DateTime($request->request->get("dateDebut")));
                $sortie->setDateLimiteInscription(new \DateTime($request->request->get("dateLimiteInscription")));

                // appliquer insert into dans la bdd
                $em->persist($sortie);
                $em->flush();
                //création de message de succes qui sera affiché sur la prochaine page
                $this->addFlash('success', 'Votre sortie   ' . $sortie->getNom() . ' a été modifié');

                //            ############################################################################################
                $this->addFlash('success', 'ON A FAIT UN SAVE !');
                return $this->redirectToRoute("main");
            }
        }

        // Publier
        if ($request->request->get("add")) {
            $sortie->setEtat($repoEtat->find(2));
            $em->flush();
            $this->addFlash('success', 'Sortie publiée !');
            return $this->redirectToRoute("main");
        }


        $titre = "Sortir.com - " . $sortie->getNom() . " - Modification";

        $tab = compact("titre", "sortie");
        $tab["formSortie"] = $form->createView();
        return $this->render("sortie/modifierSortie.html.twig", $tab);
    }



    /**
     * @Route("/sortie/infosLieu/{id}", name="infosLieu")
     */
    public function infosLieu(LieuRepository $repo, $id): Response
    {
        $lieu = $repo->find($id);
        return $this->json('{"rue":"' . $lieu->getRue() . '","lat":"' . $lieu->getLatitude() . '","long":"' . $lieu->getLongitude() . '"}');
    }
    /**
     * @Route("/sortie/lieu/{id}", name="lieu")
     */
    public function afficherLieu(VilleRepository $repo, $id): Response
    {
        $ville = $repo->find($id);
        $lieuTab = $ville->getLieus();
        $tab = [];
        foreach ($lieuTab as $val) {
            array_push($tab, array("id" => $val->getId(), "nom" => $val->getNomLieu()));
        }
        return $this->json(json_encode($tab));
    }
    /**
     * @Route("/sortie/lieu/cp/{id}", name="cp")
     */
    public function afficherCP(VilleRepository $repo, $id): Response
    {
        $ville = $repo->find($id);
        return $this->json('{"codePostal":"' . $ville->getCodePostal() . '"}');
    }
}
