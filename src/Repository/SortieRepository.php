<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
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

    /**
     * Recherche les sorties en fonction des filtres fournis.
     *
     * @param array $filters Les filtres de recherche
     *
     * @return Sortie[] Les sorties filtrées
     */
    public function findFilteredSorties(array $filters): array
    {
        $qb = $this->createQueryBuilder('s');

        // Appliquer les filtres
        if (!empty($filters['campus'])) {
            $qb->andWhere('s.campus = :campus')
                ->setParameter('campus', $filters['campus']);
        }
        if (!empty($filters['search'])) {
            $qb->andWhere('s.nom LIKE :search')
                ->setParameter('search', '%' . $filters['search'] . '%');
        }
        if (!empty($filters['startDate'])) {
            $qb->andWhere('s.dateHeureDebut >= :startDate')
                ->setParameter('startDate', new \DateTime($filters['startDate']));
        }
        if (!empty($filters['endDate'])) {
            $qb->andWhere('s.dateHeureDebut <= :endDate')
                ->setParameter('endDate', new \DateTime($filters['endDate']));
        }
        if (!empty($filters['organisateur'])) {
            // Récupérer l'utilisateur actuel
            $user = $this->getUser();

            // Sous-requête pour sélectionner les ID des sorties où l'utilisateur est l'organisateur
            $subQuery = $qb->getEntityManager()->createQueryBuilder();
            $subQuery->select('s2.id')
                ->from(Sortie::class, 's2')
                ->andWhere('s2.organisateur = :userId')
                ->setParameter('userId', $user->getId());

            // Filtrer pour les sorties dont l'ID est dans la sous-requête
            $qb->andWhere($qb->expr()->in('s.id', $subQuery->getDQL()));
        }
        if (!empty($filters['inscrit'])) {
            // Récupérer l'utilisateur actuel
            $user = $this->getUser();

            // Filtrer pour les sorties auxquelles l'utilisateur est inscrit
            $qb->join('s.participants', 'p')
                ->andWhere('p.id = :userId')
                ->setParameter('userId', $user->getId());
        }
        if (!empty($filters['nonInscrit'])) {
            // Récupérer l'utilisateur actuel
            $user = $this->getUser();

            // Sous-requête pour sélectionner les ID des sorties auxquelles l'utilisateur est inscrit
            $subQuery = $qb->getEntityManager()->createQueryBuilder();
            $subQuery->select('s2.id')
                ->from(Sortie::class, 's2')
                ->join('s2.participants', 'p2')
                ->andWhere('p2.id = :userId')
                ->setParameter('userId', $user->getId());

            // Filtrer pour les sorties dont l'ID n'est pas dans la sous-requête
            $qb->andWhere($qb->expr()->notIn('s.id', $subQuery->getDQL()));
        }
        if (!empty($filters['passees'])) {
            // Filtrer pour les sorties passées
            $now = new \DateTime();
            $qb->andWhere('s.dateHeureDebut < :now')
                ->setParameter('now', $now);
        }

        return $qb->getQuery()->getResult();
    }

}
