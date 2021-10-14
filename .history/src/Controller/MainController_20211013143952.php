<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\MonProfilType;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use DateTime;
use App\Repository\WishRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class MainController extends AbstractController
{
    //    /**
    //     * @Route("/main/profil/{id}", name="profil", requirements={"id":"\d+"})
    //     */
    //    public function detail(ParticipantRepository $repo,$id=0): Response
    //    {
    //        $participant = $repo->find($id);
    //        $titre= "Sortir.com - Profil";
    //        $tab = compact("titre","participant");
    //        return $this->render('/main/profil.html.twig', $tab);
    //    }


    /**
     * @Route("/main", name="main")
     */
    public function index(SortieRepository $sortieRepo, SiteRepository $siteRepo): Response
    {
        $sorties = $sortieRepo->findAll();

        /* 1/ Recuperer données de la barre de recherche */
        $dateDebut = new DateTime('2016-02-01');
        $dateLimiteInscription = new DateTime('2016-02-29');
        $idCurrentUser = 1;
        $idSite = 1;
        /* 2/ Vérifier données vides ou non  et remplir un tableau en conséquence*/
        //  Retourne un tableau de sorties selon la recherche effectuée dans la searchbar et le site indiquée
        //$sorties = $sortieRepo->findBySearchAndSite("", 1); // NE RIEN METTRE = findAll()

        // Retourne un tableau selon les dates rentrées et le site et site
        //$sorties = $sortieRepo->findByDates($dateDebut, $dateLimiteInscription, 1);

        // Retourne un tableau selon si l'user current est l'organisateur/trice et le site
        //$sorties = $sortieRepo->findByIdOrganisateur(1,1);

        // Retourne un tableau selon l'inscription de l'user current et le site
        //$sorties = $sortieRepo->findByIdParticipantInscrit(1,1);

        // Retourne un tableau selon la non inscription de l'user current et le site
        $all = $sortieRepo->findAll();
        $tab = [];
        foreach ($all as $sortie) {
           // dump($sortie->getParticipants());
            foreach ($sortie->getParticipants() as $participant) {
                if ($participant->getId() == $idCurrentUser) {

                    $id = array_search($sortie,$all);
                    dump($id);
                    array_splice($all, $id);
                }
            }
        }



        // Retourne un tableau des sorties passées
        //$sorties = $sortieRepo->findByEtatPassees(1);

        dd($tab);
        /* 3/ Supprimer les doublons du tableau de sorties */

        $sites = $siteRepo->findAll();
        return $this->render('main/index.html.twig', [
            'sorties' => $sorties,
            'sites' => $sites,
        ]);
    }

    /**
     * @Route("/main/monProfil", name="app_monProfil")
     */
    public function addForm(EntityManagerInterface $em, Request $request, UserPasswordHasherInterface $userPasswordHasherInterface): Response //entity manager ..... des données
    {
        // instanciation de la classe produit
        $profil = new Participant();
        // la creation du formulaire
        $form = $this->createForm(MonProfilType::class, $profil);
        // remplire l'objet wish (hydratation l'instance avec les données saisies dans le formulaire)
        $form->handleRequest($request);
        // verifier si on a soumis le form et si les donnes valide
        if ($form->isSubmitted() && $form->isValid()) {
            // génerer sql insert into et ajouter dans queue

            $profil->setIsAdmin(false);
            $profil->setIsActif(false);
            $profil->setPassword(
                $userPasswordHasherInterface->hashPassword(
                    $profil,
                    $form->get('plainPassword')->getData()
                )
            );

            $em->persist($profil);
            // appliquer insert into dans la bdd
            $em->flush();
            // redirect vers la liste wish
            //création de message de succes qui sera affiché sur la prochaine page
            $this->addFlash('success', 'le titre   ' . $profil->getMonprofil() . ' a été ajoute');
            //redirection pour eviter un ajout en double en cas de réactualisation de la plage par l'utilisateur
            $id = $profil->getId();
            return $this->redirectToRoute("app_monProfil", array('id' => $id = $profil->getId()));
        }
        $titre = "Sortir.com - Mon Profil";
        $formProfil = $form->createView();
        $tab = compact("titre", "formProfil");
        return $this->render('main/monProfil.html.twig', $tab);
    }
    /**
     *@Route("/main",name="app_Annuler")
     */
    public function Annuler(Request $request): Response
    {
        return $this->render("main/base.html.twig");
    }



}