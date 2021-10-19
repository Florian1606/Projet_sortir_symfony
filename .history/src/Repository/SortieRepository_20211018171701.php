<?php

namespace App\Repository;

use App\Data\SearchData;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function findSearch(SearchData $search, $idUserCurrent)
    {
        $query = $this
            ->createQueryBuilder('s');

        if (!empty($search->q)) {
            $query = $query
                ->andWhere('s.nom LIKE :search')
                ->setParameter('search', '%' . $search->q . '%');
        }

        // TODO : A REGLER
        /*  if (!empty($search->dateDebut)) {
            $query = $query
                ->andWhere('s.dateDebut >= :dateDebut')
                ->setParameter('dateDebut', $search->dateDebut);
        }

        // TODO : A REGLER
        if (!empty($search->dateFin)) {
            $query = $query
                ->andWhere('s.dateLimiteInscription <= :dateFin')
                ->setParameter('dateFin', $search->dateFin);
        }*/

        // if (!empty($search->villes)) {
        //     $query = $query
        //         ->innerJoin('s.site', 'si')
        //         ->andWhere('si.id IN :villes')
        //         ->setParameter('villes', $search->villes);
        // }

        if (!empty($search->isSortiesOrganisateur)) {
            $query = $query
                ->andWhere('s.organisateur  =  :idOrga')
                ->setParameter('idOrga', $idUserCurrent);
        }

        if (!empty($search->isSortiesInscrit)) {
            $query = $query
                ->innerJoin('s.participants', 'p')
                ->andWhere('p.id = :idParti')
                ->setParameter('idParti', $idUserCurrent);
        }

        if (!empty($search->isSortiesNonInscrit)) {
            $query = $query
                ->join('s.participants', 'p')
                ->andWhere('p.id <> :id')
                ->setParameter('id', $idUserCurrent);
        }

        if (!empty($search->isSortiesPassees)) {
            $query = $query
                ->join('s.etat', 'e')
                ->andWhere('e.id =  5');
        }

        $query = $query
            ->getQuery()
            ->getResult();
        return $query;
    }

    /*
        $this->createQueryBuilder('s')
        ->andWhere('s.organisateur  =  :idOrga')
        ->setParameter('idOrga', $idUserCurrent)
        ->innerJoin('s.participants', 'p')
        ->andWhere('p.id = :idParti')
        ->setParameter('idParti', $idUserCurrent)
        ->getQuery()
        ->getResult();

    */

    public function findByTEST($idUserCurrent)
    {
        return         $this->createQueryBuilder('s')
            ->andWhere('s.organisateur  =  :idOrga')
            ->setParameter('idOrga', $idUserCurrent)
            ->innerJoin('s.participants', 'p')
            ->andWhere('p.id = :idParti')
            ->setParameter('idParti', $idUserCurrent)
            ->getQuery()
            ->getResult();
    }


    
    // Retourne un tableau de sorties selon la recherche effectuée dans la searchbar et le site indiquée
    public function findBySearchAndSite($search, $idSite)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.nom LIKE :search')
            ->innerJoin('s.site', 'si')
            ->andWhere('si.id =  :site')
            ->andWhere('s.nom LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->setParameter('site', $idSite)
            ->setParameter('search', '%' . $search . '%')
            ->getQuery()
            ->getResult();
    }

    // Retourne un tableau selon les dates rentrées et le site et site
    public function findByDates($dateDebut, $dateFin, $idSite, $search)
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.site', 'si')
            ->andWhere('s.dateDebut >= :dateDebut')
            ->andWhere('s.dateLimiteInscription <= :dateFin')
            ->andWhere('si.id =  :site')
            ->andWhere('s.nom LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->setParameter('site', $idSite)
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->getQuery()
            ->getResult();
    }

    // Retourne un tableau selon si l'user current est l'organisateur/trice et le site
    public function findByIdOrganisateur($idUserCurrent, $idSite, $search)
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.site', 'si')
            ->andWhere('s.organisateur  =  :id')
            ->andWhere('si.id =  :site')
            ->andWhere('s.nom LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->setParameter('site', $idSite)
            ->setParameter('id', $idUserCurrent)
            ->getQuery()
            ->getResult();
    }

    // Retourne un tableau selon l'inscription de l'user current et le site
    public function findByIdParticipantInscrit($idUserCurrent, $idSite, $search)
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.participants', 'p')
            ->innerJoin('s.site', 'si')
            ->andWhere('p.id =  :id')
            ->andWhere('si.id =  :site')
            ->andWhere('s.nom LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->setParameter('site', $idSite)
            ->setParameter('id', $idUserCurrent)
            ->getQuery()
            ->getResult();
    }

    // Retourne un tableau selon la non inscription de l'user current et le site
    public function findByIdParticipantNonInscrit($sorties, $idUserCurrent, $idSite)
    {

        foreach ($sorties as $sortie) {
            foreach ($sortie->getParticipants() as $participant) {
                if ($participant->getId() == $idUserCurrent || $idSite != $sortie->getSite()->getId()) {
                    $id = array_search($sortie, $sorties);
                    unset($sorties[$id]);
                }
            }
        }
        return $sorties;
    }

    // Retourne un tableau des sorties passées
    public function findByEtatPassees($idSite, $search)
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.etat', 'e')
            ->innerJoin('s.site', 'si')
            ->andWhere('e.id =  5')
            ->andWhere('si.id =  :site')
            ->andWhere('s.nom LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->setParameter('site', $idSite)
            ->getQuery()
            ->getResult();
    }

    // Retourne un tableau des sorties selon le lieu
    public function findBySites($idSite)
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.site', 'si')
            ->andWhere('si.id =  :site')
            ->setParameter('site', $idSite)
            ->getQuery()
            ->getResult();
    }
}
