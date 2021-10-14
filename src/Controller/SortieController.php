<?php

namespace App\Controller;


use App\Entity\Sortie;
use App\Entity\Participant;
use App\Entity\Etat;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Repository\WishRepository;
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
    public function ajouterSortie(EntityManagerInterface $em, Request $request, EtatRepository $etat, ValidatorInterface $validator): Response
    {

//       $request->request->set("dateDebut",new \DateTime($request->request->get("dateDebut")));
        // Instance de la class Sortie
        $sortie = new Sortie();
        // Création du formulaire depuis l'entité Sortie
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        if ($form->get('cancel')->isClicked()) {
            return $this->redirectToRoute("main");
        }
        // Contrôle si les données sont valides et si le formulaire est soumis.
        if ($form->isSubmitted() && $form->isValid()) {
            //!\\ Backslash pour indiquer une fonction PHP //!\\
            $repoPart = $this->getDoctrine()->getRepository(Participant::class);
            $sortie->setOrganisateur($repoPart->find($request->request->get("test")));
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
     *@Route("/afficherSortie/{id}",name="app_afficherSortie")
     */
    public function afficherSortie(SortieRepository $repo,Request $request, $id = 0):Response{

        $sortie = $repo->find($id);
        $titre= "Sortir.com - afficher une sortie";
        $tab = compact("titre",'sortie');
        return $this->render("sortie/afficherUneSortie.html.twig",$tab);
    }

    /**
     *@Route("/annulerUneSortie/{id}",name="app_annulerUneSortie")
     */
    public function annulerUneSortie(SortieRepository $repo, EntityManagerInterface $em, $id):Response{
        $sortie = $repo->find($id);
        $repoEtat = $this->getDoctrine()->getRepository(Etat::class);
        $datenow = new \DateTime("now");
        if ($sortie->getDateDebut() > $datenow ) {
        $sortie->setEtat($repoEtat->find(6));
        $this->addFlash('success', 'Sortie Annulée !');
            return $this->redirectToRoute("main");
        }
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

    /**
     *@Route("/get-code-p/{id}",name="getCode")
     */
    public function getCodeP(Request $request, SortieRepository $repo,$id =0):Response{

//        $tab = $repo->find($id);
        $tab=[
            "0"=>"error",
            "1"=>"44500",
            "3"=>"75222",
            "2"=>"35500"
        ];
        return  $this->json('{"code": '.$tab[$id].'}');
    }

    /**
     *@Route("/sortie/update/{id}",name="app_update_sortie")
     */
    public function updateSortie(SortieRepository $repo,  EntityManagerInterface $em, $id,Request $request):Response{
        $sortie = $repo->find($id);
        
        $form = $this->createForm(SortieType::class, $sortie);

        if (!$sortie) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repoPart = $this->getDoctrine()->getRepository(Participant::class);
            $sortie->setDateDebut(new \DateTime($request->request->get("dateDebut")));
            $sortie->setDateLimiteInscription(new \DateTime($request->request->get("dateLimiteInscription")));

            $em->flush();
        }
        $titre= "Sortir.com - Modifier une sortie";

        $tab = compact("titre","sortie");
        $tab["formSortie"] = $form->createView();
        return $this->render("sortie/modifierSortie.html.twig",$tab);
    }
    

}
