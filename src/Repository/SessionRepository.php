<?php

namespace App\Repository;

use App\Entity\Session;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Session>
 */
class SessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }

    //    /**
    //     * @return Session[] Returns an array of Session objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Session
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    //Afficher les stagiaires non inscrits dans une session
    public function findNonInscrits($session_id){
        $em = $this->getEntityManager();
        $sub = $em->createQueryBuilder();

        $qb = $sub;
        // sélectionner tous les stagiaires d'une session dont l'id est passé en paramètre
        $qb->select('s')
            ->from('App\Entity\Stagiaire', 's')
            ->leftJoin('s.sessions', 'se')
            ->where('se.id = :id');

        $sub = $em->createQueryBuilder();
        // sélectionner tous les stagiaires qui ne SONT PAS (NOT IN) dans le résultat précédent
        // on obtient donc les stagiaires non inscrits pour une session définie
        $sub->select('st')
            ->from('App\Entity\Stagiaire', 'st')
            ->where($sub->expr()->notIn('st.id', $qb->getDQL()))
            //requête paramétrée
            ->setParameter('id', $session_id)
            //trier la liste des stagiaires sur le nom de famille
            ->orderBy('st.nom');

        // renvoyer le résultat
        $query = $sub->getQuery();
        return $query->getResult();
    }

    public function findNonProgrammes($session_id){
        $em = $this->getEntityManager();
        $sub = $em->createQueryBuilder();

        $qb = $sub;
        // sélectionner tous les programme d'une session dont l'id est passé en paramètre
        $qb->select('s')
            ->from('App\Entity\Programme', 's')
            ->leftJoin('s.session', 'se')
            ->where('se.id = :id');

        $sub = $em->createQueryBuilder();
        // sélectionner tous les programme qui ne SONT PAS (NOT IN) dans le résultat précédent
        // on obtient donc les programme non inscrits pour une session définie
        $sub->select('st')
            ->from('App\Entity\Programme', 'st')
            ->where($sub->expr()->notIn('st.id', $qb->getDQL()))
            //requête paramétrée
            ->setParameter('id', $session_id);

        // renvoyer le résultat
        $query = $sub->getQuery();
        return $query->getResult();
    }
}
