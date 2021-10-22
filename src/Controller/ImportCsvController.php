<?php

namespace App\Controller;

use App\Form\UploadMembersFromCSVFileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ImportCsvController extends AbstractController
{
    /**
     * @Route("/admin/upload-users-csv/", name="upload_user_csv");
     * */
    public function uploadMembersFromCSV(EntityManagerInterface $entityManager, Request $request)
    {
        // Formulaire de l'upload
        $formUploadCSV = $this->createForm(UploadMembersFromCSVFileType::class);
        $formUploadCSV->handleRequest($request);

        // Si le fichier est envoyé
        if ($formUploadCSV->isSubmitted() && $formUploadCSV->isValid()) {
            // On récupère le fichier
            $csvFile = $formUploadCSV->get('csvFile')->getData();
            if ($csvFile) {
                // On récupère le nom du fichier en lui ajoutant un id unique (évite les erreurs de doubles fichiers)
                $originalFileName = pathinfo($csvFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFileName);
                $newFilename = $safeFilename . '-' . uniqid() . '.csv';
                // On déplace le fichier dans le dossier qu'on souhaite
                try {
                    $csvFile->move(
                        $this->getParameter('data_csv_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
            }
            //On passe le nom du fichier dans une fonction qui importe les membres depuis le fichier csv
            $this->importMembersFromCSV($newFilename);
        }
        return $this->render(
            'admin/upload_csv.html.twig',
            [
                'formUploadCSV' => $formUploadCSV->createView(),
            ]
        );
    }


    /**
     * @Route("/admin/import-users-csv/{file_to_import}", name="import_user_csv_file");
     * */
    public function importMembersFromCSV($file_to_import)
    {
        // On récupère le fichier
        $pathToFile = $this->getParameter('data_csv_directory') . '/' . $file_to_import;
        // Cas d'erreur : si le fichier n'existe pas
        if (!file_exists($pathToFile)) {
            $this->addFlash('error', 'Impossible de trouver le ficher à importer!');
            return $this->redirectToRoute('main');
        }
        // On récupère les données du fichier via une fonction qui transforme les données du csv en tableau
        $data = $this->loadCSVtoArray($pathToFile);
        // Cas d'erreur: mauvais format de fichier
        if (!$data) {
            $this->addFlash('error', 'Erreur dans l\'importation du fichier, verifier le format!');
            return $this->redirectToRoute('main');
        }
        // On redigire vers un controller qui va enregistrer dans la bdd avec la data 
        $response = $this->forward('App\Controller\AdminController:registerFromFileCSV', ['data' => $data]);
        return $response;
    }


    public function loadCSVtoArray($pathToFile)
    {
        if (file_exists($pathToFile)) {
            $dataALL = array();
            // On ouvre le fichier puis on récupere ligne par ligne les données qu'on place dans un tableau 
            if (($handlerCSV = fopen($pathToFile, 'r')) !== false) {
                while (($data = fgetcsv($handlerCSV, 500, ";")) !== false) {
                    $dataALL[] = $data;
                }
                fclose($handlerCSV);
            }
            return $dataALL;
        } else {
            return false;
        }
    }
}
