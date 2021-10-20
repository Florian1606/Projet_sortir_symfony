<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Entity\Participant;
use App\Form\MonProfilType;
use App\Repository\ParticipantRepository;
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
use App\Entity\Sortie;
use App\Entity\Etat;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\VilleRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Form\AjoutVilleType;
use App\Entity\Ville;
use App\Entity\Site;
use App\Form\AjoutSiteType;
use App\Form\SearchType;
use App\Service\FileUploader;
use Doctrine\ORM\Mapping\Id;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MainController extends AbstractController
{

    /**
     * @Route("/main/profil/{id}", name="profil", requirements={"id":"\d+"})
     */
    public function profil(ParticipantRepository $repo, $id = 0): Response
    {

        $participant = $repo->find($id);
        return $this->render('main/profil.html.twig', [
            'participant' => $participant,
        ]);
    }

    /**
     * @Route("/main/cgu", name="cgu",)
     */
    public function cgu(ParticipantRepository $repo, $id = 0): Response
    {

        return $this->render('main/cgu.html.twig');
    }

    /**
     * @Route("/admin/home", name="app_admin-home",)
     */
    public function adminHomepage(ParticipantRepository $repo, $id = 0): Response
    {

        return $this->render('main/dashboard.html.twig');
    }



    /**
     * @Route("/main", name="main")
     */
    public function index( SortieRepository $sortieRepo, SiteRepository $siteRepo): Response
    {
        $sorties = $sortieRepo->findAll();
        $sites = $siteRepo->findAll();
        return $this->render('main/index.html.twig', [
            'sorties' => $sorties,
            'sites' => $sites,
        ]);
    }

    /**
     * @Route("/admin/creationProfil", name="app_creationProfil")
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
                    $form->get('password')->getData()
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
            return $this->redirectToRoute("app_creationProfil", array('id' => $id = $profil->getId()));
        }
        $titre = "Sortir.com - Mon Profil";
        $formProfil = $form->createView();
        $tab = compact("titre", "formProfil");

        return $this->render('admin/creationProfil.html.twig', $tab);
    }

    /**
     *@Route("/main/annuler",name="app_Annuler")
     */
    public function Annuler(Request $request): Response
    {
        return $this->render("main/base.html.twig");
    }

    /**
     *@Route("/main/about_us",name="app_about_us")
     */
    public function aboutUs(Request $request): Response
    {
        return $this->render("main/about-us.html.twig");
    }



    /**
     *@Route("/admin/gererLesVilles",name="app_gerer_les_villes")
     */
    public function gererLesVilles(Request $request, VilleRepository $villeRepo, EntityManagerInterface $em,  UserPasswordHasherInterface $userPasswordHasherInterface): Response
    {
        // instanciation de la classe produit
        $formville = new Ville();
        // la creation du formulaire
        $form = $this->createForm(AjoutVilleType::class, $formville);


        // remplire l'objet wish (hydratation l'instance avec les données saisies dans le formulaire)
        $form->handleRequest($request);
        // verifier si on a soumis le form et si les donnes valide
        if ($form->isSubmitted() && $form->isValid()) {

            // génerer sql insert into et ajouter dans queue

            $em->persist($formville);
            // appliquer insert into dans la bdd
            $em->flush();
            // redirect vers la liste wish
            //création de message de succes qui sera affiché sur la prochaine page
            $this->addFlash('success', 'la ville   '  . ' a été ajoute');
            //redirection pour eviter un ajout en double en cas de réactualisation de la plage par l'utilisateur
            $id = $formville->getId();
            return $this->redirectToRoute("app_gerer_les_villes", array('id' => $id = $formville->getId()));
        }
        $titre = "Sortir.com - gererville";

        $villes = $villeRepo->findAll();
        return $this->render('admin/gererLesVilles.html.twig', [
            'villes' => $villes,
            'formville2' => $form->createView(),
        ]);
    }

    /**
     *@Route("/admin/gererLesSites",name="app_gerer_les_sites")
     */
    public function gererLesSites(Request $request, SiteRepository $siteRepo, EntityManagerInterface $em,  UserPasswordHasherInterface $userPasswordHasherInterface): Response
    {
        // instanciation de la classe produit
        $formsite = new Site();
        // la creation du formulaire
        $form = $this->createForm(AjoutSiteType::class, $formsite);
        // remplire l'objet wish (hydratation l'instance avec les données saisies dans le formulaire)
        $form->handleRequest($request);
        // verifier si on a soumis le form et si les donnes valide
        if ($form->isSubmitted() && $form->isValid()) {
            // génerer sql insert into et ajouter dans queue

            $em->persist($formsite);
            // appliquer insert into dans la bdd
            $em->flush();
            // redirect vers la liste wish
            //création de message de succes qui sera affiché sur la prochaine page
            $this->addFlash('success', 'le site   '  . ' a été ajoute');
            //redirection pour eviter un ajout en double en cas de réactualisation de la plage par l'utilisateur
            $id = $formsite->getId();
            return $this->redirectToRoute("app_gerer_les_sites", array('id' => $id = $formsite->getId()));
        }
        $titre = "Sortir.com - gererville";

        $sites = $siteRepo->findAll();
        return $this->render('admin/gererLesSites.html.twig', [
            'sites' => $sites,
            'formsite2' => $form->createView(),
        ]);
    }

    /**
     *@Route("/search/{id}",name="app_search")
     */
    public function search(Request $request, SortieRepository $sortieRepo, SiteRepository $siteRepo, $id): Response
    {
        /* BARRE DE RECHERCHE CONTROLLER */
        /* 1/ Recuperer données de la barre de recherche */
        $search = $request->request->get('search');
        $dateDebut = $request->request->get('dateDebut');
        $dateLimiteInscription = $request->request->get('dateFin');
        $isSortiesOrganisateur = $request->request->get('isSortiesOrganisateur');
        $isSortiesInscrit = $request->request->get('isSortiesInscrit');
        $isSortiesNonInscrit = $request->request->get('isSortiesPasInscrit');
        $isSortiesPassees =  $request->request->get('isSortiesPassees');
        $idCurrentUser = $id;
        $idSite = $request->request->get('site');

        // Tableau des sorties à rentrer (doublon supprimée après)
        $sorties = [];

        /* 2/ Vérifier données vides ou non  et remplir un tableau en conséquence*/
        //  Retourne un tableau de sorties selon la recherche effectuée dans la searchbar et le site indiquée
        if (
            $search != "" && $isSortiesOrganisateur == null && $isSortiesInscrit == null && $isSortiesNonInscrit == null
            && $isSortiesPassees == null && $dateDebut == "" && $dateLimiteInscription == ""
        ) {
            $sorties = array_merge($sorties, $sortieRepo->findBySearchAndSite($search, $idSite, $search)); // NE RIEN METTRE = findAll()
        }

        if (
            $idSite != "" && $search == "" && $isSortiesOrganisateur == null && $isSortiesInscrit == null && $isSortiesNonInscrit == null
            && $isSortiesPassees == null && $dateDebut == "" && $dateLimiteInscription == ""
        ) {
            $sorties = array_merge($sorties, $sortieRepo->findBySites($idSite));
        }

        // Retourne un tableau selon les dates rentrées et le site et site
        if ($dateDebut != "" && $dateLimiteInscription != "") {
            $sorties = array_merge($sorties, $sortieRepo->findByDates($dateDebut, $dateLimiteInscription, $idSite, $search));
        }

        // Retourne un tableau selon si l'user current est l'organisateur/trice et le site
        if ($isSortiesOrganisateur != null) {
            $sorties = array_merge($sorties, $sortieRepo->findByIdOrganisateur($idCurrentUser, $idSite, $search));
        }

        // Retourne un tableau selon l'inscription de l'user current et le site
        if ($isSortiesInscrit != null) {
            $sorties =  array_merge($sorties, $sortieRepo->findByIdParticipantInscrit($idCurrentUser, $idSite, $search));
        }

        // Retourne un tableau selon la non inscription de l'user current et le site
        if ($isSortiesNonInscrit != null) {
            $sorties = array_merge($sorties, $sortieRepo->findByIdParticipantNonInscrit($sortieRepo->findAll(), $idCurrentUser, $idSite));
        }


        // Retourne un tableau des sorties passées
        if ($isSortiesPassees != null) {
            $sorties = array_merge($sorties, $sortieRepo->findByEtatPassees($idSite, $search));
        }



        /* 3/ Supprimer les doublons du tableau de sorties */
        $sorties = array_unique($sorties, SORT_REGULAR);

        $sites = $siteRepo->findAll();
        return $this->render('main/index.html.twig', [
            'sorties' => $sorties,
            'sites' => $sites,
        ]);
    }

    /**
     * @Route("/monProfil/{id}",name="app_modifier")
     */
    public function modifier(FileUploader $fileUploader, Request $request, EntityManagerInterface $em, ParticipantRepository $repo, UserPasswordHasherInterface $userPasswordHasherInterface, $id): Response
    {
        // instanciation de la classe produit
        $participant = $repo->find($id);
        $form = $this->createForm(MonProfilType::class, $participant);
        // remplire l'objet wish (hydratation l'instance avec les données saisies dans le formulaire)
        $form->handleRequest($request);
        // verifier si on a soumis le form et si les donnes valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Supprimer photo si déjà existante
            $urlPhotoOld = $participant->getAvatarFilename();
            

            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                $photoFileName = $fileUploader->upload($photoFile);
                $participant->setAvatarFilename($photoFileName);
            }
            // génerer sql insert into et ajouter dans queue
            $participant->setIsAdmin(false);
            $participant->setIsActif(false);
            $participant->setPassword(
                $userPasswordHasherInterface->hashPassword(
                    $participant,
                    $form->get('password')->getData()
                )
            );
            // appliquer insert into dans la bdd
            $em->persist($participant);
            $em->flush();
            //création de message de succes qui sera affiché sur la prochaine page
            $this->addFlash('success', 'Votre profil   ' . $participant->getMonprofil() . ' a été modifié');
            return $this->redirectToRoute("app_modifier", array('id' => $id));
        }
        $em->flush();
        return $this->render('main/modifProfil.html.twig', [
            'formProfil' => $form->createView(),
        ]);
    }
    /**
     * @Route("/admin/importation", name="incorporation" )
     */
    public function incorporation(ParticipantRepository $repo, $id = 0): Response
    {
        return $this->render('admin/importation.html.twig', []);
    }
}
