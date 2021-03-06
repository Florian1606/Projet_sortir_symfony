<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Participant;
use App\Repository\ParticipantRepository;
use App\Entity\Lieu;
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;


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
    public function adminHomepage(ParticipantRepository $repo, $id = 0): Response
    {

        return $this->render('admin/dashboard.html.twig', [
            'controller_name' => 'AdminController',
        ]);
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
    public function registerFromFileCSV(EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder, $data)
    {

        //Check for errors
        if (count($data) == 0 || count($data) == 1 || is_null($data)) {
            $this->addFlash('success', 'Aucun membre dans la liste ?? ins??rer');
            return $this->redirectToRoute('display_events');
        }

        //Remove header (ie first lign):
        $newParticipants = array_slice($data, 1);

        $errors = array();

        foreach ($newParticipants as $participant) {

            $name = $participant[0];
            $surname = $participant[1];
            $email = $participant[2];
            $siteName = $participant[3];

            //Check si le membre existe deja dans la base de donn??es (email seulement car pas de pseudo encore)
            if ($em->getRepository(Member::class)->findByEmail($email)) {
                //Existe d??j??:
                $errors[] = ['member' => $participant, 'msg' => $email. ': email existe d??j?? dans la base, il n\'a pas ??t?? ins??r??.'];
                continue;
            }


            $user = new Participant();
            $user->setName($name);
            $user->setSurname($surname);
            $user->setMail($email);
            $plainPassword = DefaultPasswordGenerator::defaultPasswordFromNameAndSurname($name, $surname);
            $user->setPassword($passwordEncoder->encodePassword($user, $plainPassword));
            $user->setActive(true);

            //Set site:
            if( !is_null($siteUser = $this->getSite($em, $siteName)) ){
                $user->setSite($siteUser);
            }
            else{
                $errors[] = ['member' => $participant, 'msg' => $name. ' : site renseign??e inconnu, il n\'a pas ??t?? ins??r??.'];
                continue;
            }

            //Persist:
            $em->persist($user);
            $this->addFlash('error', $name . ' : inscrit avec succ??s !');
        }

        $em->flush();
        dump($errors);

        //Display errors:
        foreach ($errors as $error){
            $this->addFlash('error', $error['msg']);
        }

        return $this->redirectToRoute('display_events');

    }

        //set user site if site exists in db, return user, false otherwise
        public function getSite(EntityManagerInterface $em, $siteName){

            $site = $em->getRepository(Site::class)->findByName($siteName);
            if(!is_null($site)){
                return $site;
            }
            return null;
        }

            /**
     * @Route("/admin/importation", name="incorporation" )
     */
    public function incorporation(ParticipantRepository $repo, $id = 0): Response
    {
        return $this->render('admin/importation.html.twig', []);
    }

    /**
     * @Route("/displayevents", name="display_events")
     */
    public function displayEvents(EntityManagerInterface $entityManager){
        $site = $this->getUser()->getSite();
        $eventRepo = $entityManager->getRepository(Event::class);
        $sites = $entityManager->getRepository(Site::class)->findAll();

        $events = $eventRepo->findEventBySite($site);

        //to update events status
//        $updateOneEvent = new UpdateEventStatus($entityManager);
//        $updateOneEvent->updateEventStatus();

        return $this->render("displayevents/displayevents.html.twig",compact('events','sites'));
    }

}
