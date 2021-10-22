<?php

namespace App\Controller;

use App\Entity\Lieu;
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
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Form\AjoutVilleType;
use App\Entity\Ville;
use App\Entity\Site;
use App\Form\AjoutSiteType;
use App\Form\LieuType;
use App\Service\FileUploader;
use Doctrine\ORM\Mapping\Id;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @Route("/admin/home", name="app_admin_home",)
     */
    public function adminHomepage(): Response
    {
        $repoParticipant = $this->getDoctrine()->getRepository(Participant::class);
        $participants = $repoParticipant->findAll();
        $participants = count($participants);
        $repoLieu = $this->getDoctrine()->getRepository(Lieu::class);
        $lieux = $repoLieu->findAll();
        $lieux = count($lieux);
        $repoVille = $this->getDoctrine()->getRepository(Ville::class);
        $villes = $repoVille->findAll();
        $villes = count($villes);
        $repoSortie = $this->getDoctrine()->getRepository(Sortie::class);
        $sorties = $repoSortie->findAll();
        $sorties = count($sorties);

        $admins = $repoParticipant->findby(
            array('isAdmin' => '1')
        );

        $tab = compact("lieux", "participants","villes","sorties",'admins');
        return $this->render("admin/dashboard.html.twig", $tab);

    }

    /**
     * @Route("/admin/participants", name="app_admin_participant")
     */
    public function gestionParticipants(EntityManagerInterface $em, ParticipantRepository $repo): Response
    {
        $participants = $repo->findAll();

        $titre = "Gestion des participants";

        $tab = compact("titre","participants");

        return $this->render('admin/gererParticipants.html.twig',$tab );
    }

    /**
     * @Route("/admin/lieux", name="app_admin_lieux")
     */
    public function gestionLieux(EntityManagerInterface $em, LieuRepository $repo): Response
    {
        $lieux = $repo->findAll();

        $titre = "Gestion des Lieux";

        $tab = compact("titre","lieux");

        return $this->render('admin/gererLieux.html.twig',$tab );
    }

    /**
     * @Route("/admin/villes", name="app_admin_villes")
     */
    public function gestionVilles(EntityManagerInterface $em, VilleRepository $repo): Response
    {
        $villes = $repo->findAll();

        $titre = "Gestion des Villes";

        $tab = compact("titre","villes");

        return $this->render('admin/gererVilles.html.twig',$tab );
    }

    /**
     * @Route("/admin/actif/{id}",name="app_admin_actif")
     */
    public function actif(EntityManagerInterface $em, $id): Response
    {
        $repo = $this->getDoctrine()->getRepository(Participant::class);
        $participant = $repo->find($id);
        $participant->setIsActif(true);
        $em->flush();
        $this->addFlash('success', 'Participant \"'.$participant->getPseudo().'\" est maintenant actif !');
        return $this->redirectToRoute("app_admin_participant");
    }

    /**
     * @Route("/admin/inactif/{id}",name="app_admin_inactif")
     */
    public function inactif(EntityManagerInterface $em, $id): Response
    {
        $repo = $this->getDoctrine()->getRepository(Participant::class);
        $participant = $repo->find($id);
        $participant->setIsActif(false);
        $em->flush();
        $this->addFlash('success', 'Participant "'.$participant->getPseudo().'" est maintenant inactif !');
        return $this->redirectToRoute("app_admin_participant");
    }

    /**
     * @Route("/admin/lieu/add",name="app_admin_lieu_add")
     */
    public function addLieu(EntityManagerInterface $em): Response
    {

        return $this->render('lieu/ajouerLieux.html.twig');
    }

        /**
     * @Route("/admin/register-from-csv-file/", name="app_register_from_csv")
     */
    public function registerFromFileCSV(ParticipantRepository $repoUser, SiteRepository $repoSite, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasherInterface, $data)
    {
        // Cas d'erreur : aucun membre dans la liste
        if (count($data) == 0 || is_null($data)) {
            $this->addFlash('danger', 'Aucun membre dans la liste à insérer');
            return $this->redirectToRoute('/admin/upload-users-csv/');
        }
        $errors = array();

        // On regarde par case du tableau les données (eq à une ligne du fichier csv)
        foreach ($data as $participant) {
            // On affecte les données pour une meilleur lisibilité du code
            $pseudo = $participant[0];
            $email = $participant[1];
            $password = $participant[2];
            $nom = $participant[3];
            $prenom = $participant[4];
            $tel = $participant[5];
            $idSite = $participant[6];

            //Cas d'erreurs : si le membre existe deja dans la base de données 
            if ($repoUser->findOneBy(['email' => $email])) {
                //Existe déjà:
                $errors[] = ['member' => $participant, 'msg' => $email. ': email existe déjà dans la base, il n\'a pas été inséré.'];
                continue;
            }

            // On crée un nouvel Participant auquel on affecte les données du fichier csv
            $user = new Participant();
            $user->setPseudo($pseudo);
            $user->setEmail($email);
            $user->setPassword(
                $userPasswordHasherInterface->hashPassword(
                    $user,
                    $password
                )
            );
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setTelephone($tel);
            $user->setIsActif(1);
            $user->setIsAdmin(0);
            $user->setAvatarFilename('avatar-default.jpg');
            $user->setRoles(["ROLE_PARTICIPANT"]);
           
           // Pour le site on fait une vérification si le site exsite bien dans la bdd
            $siteUser = $repoSite->find($idSite);
            if( $siteUser != null ){
                $user->setIdSite($siteUser);
            }
            else{
                $errors[] = ['member' => $participant, 'msg' => $pseudo. ' : site renseigné inconnu, il n\'a pas été inséré.'];
                continue;
            }
            $em->persist($user);
            $this->addFlash('success', $pseudo . ' : inscrit avec succès !');
        }
        $em->flush();
        // On envoie les erreurs:
        foreach ($errors as $error){
            $this->addFlash('danger',  $error['msg'] );
        }
        return $this->redirectToRoute('/admin/upload-users-csv/');
    }


    /**
     * @Route("/admin/importation", name="incorporation" )
     */
    public function incorporation(ParticipantRepository $repo, $id = 0): Response
    {
        return $this->render('admin/importation.html.twig', []);
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
            $this->addFlash('success', 'la ville   '  . ' a été ajouté');
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
            $profil->setAvatarFilename('avatar-default.jpg');
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
     * @Route("/admin/role/{id}",name="app_admin_role")
     */
    public function changeRole(EntityManagerInterface $em, $id): Response
    {


        $repo = $this->getDoctrine()->getRepository(Participant::class);
        $participant = $repo->find($id);
        if ($participant->getIsActif() == false) {
            $this->addFlash('warning', 'Participant "'.$participant->getPseudo().'" doit être actif !');
            return $this->redirectToRoute("app_admin_participant");
        }

        if ($participant == $this->getUser()) {
            $this->addFlash('warning', ' Ne jouer pas avec vos droits ! !');
            return $this->redirectToRoute("app_admin_participant");
        }

            if ($participant->getIsAdmin() == true){
                $participant->setIsAdmin(false);
                $roles[] = 'ROLE_PARTICIPANT';
                 $participant->setRoles($roles);
                $em->flush();
                $this->addFlash('success', 'Participant "'.$participant->getPseudo().'" n\'est plus admin !');

            } else {
                $participant->setIsAdmin(true);
                $roles[] = 'ROLE_ADMIN';
                $participant->setRoles($roles);
                $em->flush();
                $this->addFlash('success', 'Participant "'.$participant->getPseudo().'" devient admin "Un grand pouvoir implique de grandes responsabilités" !');

            }


        return $this->redirectToRoute("app_admin_participant");
    }

    /**
     * @Route("/admin/delete-participant/{id}",name="app_admin_delete_participant")
     */
    public function deleteUser(EntityManagerInterface $em, $id): Response
    {

// sorties et organisées ???
        $repo = $this->getDoctrine()->getRepository(Participant::class);
        $participant = $repo->find($id);

        if ($participant == $this->getUser()) {
            $this->addFlash('danger', 'Pas ça, Zinédine, pas aujourd’hui, pas maintenant, pas après tout ce que tu as fait');
            return $this->redirectToRoute("app_admin_participant");
        }

        if (count($participant->getSortiesOrganisees()) > 0 ){
            $this->addFlash('warning', 'Le participant ne doit être organisateur ....');
            return $this->redirectToRoute("app_admin_participant");
        }

        $em->remove($participant);
        $em->flush();
        $this->addFlash('success', 'Participant "'.$participant->getPseudo().'" est supprimé !');




        return $this->redirectToRoute("app_admin_participant");
    }

    /**
     * @Route("/admin/sites", name="app_admin_site")
     */
    public function gestionSite(EntityManagerInterface $em, SiteRepository $repo): Response
    {
        $sites = $repo->findAll();
        $titre = "Gestion des Sites";
        $tab = compact("titre","sites");
        return $this->render('admin/gererLesSites.html.twig',$tab );
    }

    /**
     * @Route("/site/delete/{id}",name="app_site_delete")
     */
    public function deleteSite(SiteRepository $repo, EntityManagerInterface $em, $id)
    {
        // Récupère le current user.
        $user = $this->get('security.token_storage')->getToken()->getUser();
        // Récupère le lieu suivant l'id passé en paramètre.
        $site = $repo->find($id);
        $tabSorties =$site->getSorties();
        // Vérification des droits
        // Doit être un admin et ne doit pas avoir de sortie
        if ($user->getIsAdmin() && count($tabSorties) == 0 ) {
            $this->addFlash('success', "Site : ".$site->getNom()." supprimé !");
            $em = $this->getDoctrine()->getManager();
            $em->remove($site);
            $em->flush();
        } else {
            $this->addFlash('danger', "Lieu : ".$site->getNom()." ne peut être supprimé ! (Rattaché à des sorties)");
        }
        return $this->redirectToRoute("app_admin_site");
    }
    /**
     * @Route("/admin/insererVille",name="app_modifier_ville")
     */
    public function modifierVille(FileUploader $fileUploader, Request $request, EntityManagerInterface $em, VilleRepository $repo, UserPasswordHasherInterface $userPasswordHasherInterface): Response
    {
        // instanciation de la classe produit
       $ville = new Ville;
        $form = $this->createForm(AjoutVilleType::class, $ville);
        // remplire l'objet wish (hydratation l'instance avec les données saisies dans le formulaire)
        $form->handleRequest($request);
        // verifier si on a soumis le form et si les donnes valide
        if ($form->isSubmitted() && $form->isValid()) {


            // appliquer insert into dans la bdd
            $em->persist($ville);
            $em->flush();
            //création de message de succes qui sera affiché sur la prochaine page
            $this->addFlash('success', 'Votre ville   ' . $ville->getNomVille() . ' a été ajouté');
            return $this->redirectToRoute("app_modifier_ville");
        }

        $formAjoutVille = $form->createView();
        $tab = compact( "formAjoutVille");

        return $this->render('admin/ajouterVille.html.twig',$tab);

    }
    /**
     * @Route("/admin/insererSite",name="app_modifier_Site")
     */
    public function modifierSite(FileUploader $fileUploader, Request $request, EntityManagerInterface $em, SiteRepository $repo, UserPasswordHasherInterface $userPasswordHasherInterface): Response
    {
        // instanciation de la classe produit
       $site = new Site;
        $form = $this->createForm(AjoutSiteType::class, $site);
        // remplire l'objet wish (hydratation l'instance avec les données saisies dans le formulaire)
        $form->handleRequest($request);
        // verifier si on a soumis le form et si les donnes valide
        if ($form->isSubmitted() && $form->isValid()) {


            // appliquer insert into dans la bdd
            $em->persist($site);
            $em->flush();
            //création de message de succes qui sera affiché sur la prochaine page
            $this->addFlash('success', 'Votre site  ' . $site->getNom() . ' a été ajouté');
            return $this->redirectToRoute("app_modifier_Site");
        }

        $formAjoutSite = $form->createView();
        $tab = compact( "formAjoutSite");

        return $this->render('admin/ajouterSite.html.twig',$tab);

    }
    /**
     * @Route("/admin/insererLieu",name="app_modifier_Lieu")
     */
    public function modifierLieu(FileUploader $fileUploader, Request $request, EntityManagerInterface $em, LieuRepository $repo, UserPasswordHasherInterface $userPasswordHasherInterface): Response
    {
        // instanciation de la classe produit
        $lieu = new Lieu;
        $form = $this->createForm(LieuType::class, $lieu);
        // remplire l'objet wish (hydratation l'instance avec les données saisies dans le formulaire)
        $form->handleRequest($request);
        // verifier si on a soumis le form et si les donnes valide
        if ($form->isSubmitted() && $form->isValid()) {

            // appliquer insert into dans la bdd
            $em->persist($lieu);
            $em->flush();
            //création de message de succes qui sera affiché sur la prochaine page
            $this->addFlash('success', 'Votre lieu  ' . $lieu->getNomLieu() . ' a été ajouté');
            return $this->redirectToRoute("app_modifier_Site");
        }

        $formAjoutLieu = $form->createView();
        $tab = compact( "formAjoutLieu");

        return $this->render('admin/ajouterlieu.html.twig',$tab);

    }

        /**
     * @Route("/ville/delete/{id}",name="app_ville_delete")
     */
    public function deleteVille(VilleRepository $repo, EntityManagerInterface $em, $id)
    {
        // Récupère le current user.
        $user = $this->get('security.token_storage')->getToken()->getUser();
        // Récupère le lieu suivant l'id passé en paramètre.
        $ville = $repo->find($id);

        $tabLieus =$ville->getLieus();
        // Vérification des droits
        // Doit être un admin et ne doit pas avoir de sortie
        if ($user->getIsAdmin() && count($tabLieus) == 0 ) {
            $this->addFlash('success', "Ville: ".$ville->getNomVille()." supprimé !");
            $em = $this->getDoctrine()->getManager();
            $em->remove($ville);
            $em->flush();
        } else {
            $this->addFlash('danger', "Ville : ".$ville->getNomVille()." ne peut être supprimé ! (Rattaché à des lieux)");
        }
        return $this->redirectToRoute("app_admin_villes");
    }
}
