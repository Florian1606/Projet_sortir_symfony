<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;
use App\Entity\Lieu;
use App\Entity\Ville;
use App\Form\LieuType;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class LieuController extends AbstractController
{
    /**
     * @Route("/lieu", name="lieu")
     */
    public function index(): Response
    {
        return $this->render('lieu/index.html.twig', [
            'controller_name' => 'LieuController',
        ]);
    }

    /**
     * @Route("/lieu/add",name="app_lieu_add_azert")
     */
    public function azerty( Request $request,EntityManagerInterface $em,ValidatorInterface $validator,SerializerInterface $serializer) : Response
    {


        // Créer une instance de lieu
        $lieu = new Lieu();
        $lieuForm = $this->createForm(LieuType::class,$lieu);
        $lieuForm->handleRequest($request);


        if($lieuForm->isSubmitted() && $lieuForm->isValid()){
            $em->persist($lieu);
            $em->flush();
            return $this->json('{"status":201}');
        }

        $errors = $validator->validate($lieuForm);
        $json = [];
        foreach ($errors as $val){
            array_push($json,["property"=> $val->getPropertyPath(),"message"=>$val->getMessage()]);
        }
        $json = json_encode($json);
        return $this->json($json);

    }

    /**
     * @Route("/lieu/delete/{id}",name="app_lieu_delete")
     */
    public function deleteLieu(LieuRepository $repo, EntityManagerInterface $em, $id)
    {

        // Récupère le current user.
        $user = $this->get('security.token_storage')->getToken()->getUser();
        // Récupère le lieu suivant l'id passé en paramètre.
        $lieu = $repo->find($id);

        $tabSorties =$lieu->getSorties();


        // Vérification des droits
        // Doit être un admin et ne doit pas avoir de sortie
        if ($user->getIsAdmin() && count($tabSorties) == 0 ) {
            $this->addFlash('success', "Lieu : ".$lieu->getNomLieu()." supprimé !");
            $em = $this->getDoctrine()->getManager();
            $em->remove($lieu);
            $em->flush();
        } else {
            $this->addFlash('danger', "Lieu : ".$lieu->getNomLieu()." ne peut être supprimé ! (Rattaché à des sorties)");


        }

        return $this->redirectToRoute("app_admin_lieux");
    }





}
