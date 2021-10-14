<?php

namespace App\Repository;

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

    // Retourne un tableau de sorties selon la recherche effectuée dans la searchbar et le site indiquée
    public function findBySearchAndSite($search,$idSite)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.nom LIKE :search')
            ->andWhere('s.site_id =  :site')
            ->setParameter('site', $idSite)
            ->setParameter('search', '%'.$search.'%')
            ->getQuery()
            ->getResult()
        ;
    }

    // Retourne un tableau selon les dates rentrées et le site
    public function findByDates($dateDebut,$dateFin,$idSite)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.date_debut >= :dateDebut')
            ->andWhere('s.date_limite_inscription <= :dateFin')
            ->andWhere('s.site_id =  :site')
            ->setParameter('site', $idSite)
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->getQuery()
            ->getResult()
        ;
    }

    // Retourne un tableau selon si l'user current est l'organisateur/trice et le site
    public function findByIdOrganisateur($idUserCurrent,$idSite)
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.participant', 'p')
            ->andWhere('p.id =  :id')
            ->andWhere('s.site_id =  :site')
            ->setParameter('site', $idSite)
            ->setParameter('id', $idUserCurrent)
            ->getQuery()
            ->getResult()
        ;
    }

    // Retourne un tableau selon l'inscription de l'user current et le site
    public function findByIdParticipantInscrit($idUserCurrent,$idSite)
    {
        return $this->createQueryBuilder('s')
        ->leftJoin('s.participant', 'p')
        ->andWhere('p.id =  :id')
        ->andWhere('s.site_id =  :site')
        ->setParameter('site', $idSite)

        ->setParameter('id', $idUserCurrent)
        ->getQuery()
        ->getResult()
    ;
    }

    // Retourne un tableau selon la non inscription de l'user current et le site
    public function findByIdParticipantNonInscrit($idUserCurrent,$idSite)
    {

    
    }

    // Retourne un tableau des sorties passées
    public function findByEtatPassees($idSite)
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.etat', 'e')
            ->andWhere('e.id =  5')
            ->andWhere('s.site_id =  :site')
            ->setParameter('site', $idSite)
            ->getQuery()
            ->getResult()
        ;
    }


    // /**
    //  * @return Sortie[] Returns an array of Sortie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Sortie
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
