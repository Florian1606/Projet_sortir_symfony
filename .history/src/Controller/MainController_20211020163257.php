<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\Participant;
use App\Form\MonProfilType;
use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use App\Service\DefaultPasswordGenerator;
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
    public function profil(SortieRepository $repoSorties, ParticipantRepository $repo, $id = 0): Response
    {

        $participant = $repo->find($id);
        $sorties = $repoSorties->findBy(array('organisateur' => $participant));
        return $this->render('main/profil.html.twig', [
            'participant' => $participant,
            'sorties' => $sorties,
        ]);
    }

    /**
     * @Route("/main/cgu", name="cgu",)
     */
    public function cgu(ParticipantRepository $repo, $id = 0): Response
    {
        $user = $this->getUser();
        dd($user->get);
        return $this->render('main/cgu.html.twig');
    }


    /**
     * @Route("/main", name="main")
     */
    public function index(SortieRepository $sortieRepo, SiteRepository $siteRepo): Response
    {
        $sorties = $sortieRepo->findAllWithSitesAndEtats();
        dump($sorties);
        $sites = $siteRepo->findAll();
        return $this->render('main/index.html.twig', [
            'sorties' => $sorties,
            'sites' => $sites,
        ]);
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
            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                // Supprimer photo si déjà existante
                if ($this->getUser()->getAvatarFilename() != 'avatar-default.jpg') {
                    $urlPhotoOld = $this->getUser()->getAvatarFilename();
                    $fileUploader->removeAvatar($urlPhotoOld);
                }
                // Mettre en place la nouvelle photo
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









}
