<?php

namespace App\Repository;

use App\Entity\Place;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use function Doctrine\ORM\QueryBuilder;

/**
 * @method Place|null find($id, $lockMode = null, $lockVersion = null)
 * @method Place|null findOneBy(array $criteria, array $orderBy = null)
 * @method Place[]    findAll()
 * @method Place[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Place::class);
    }

    /**
     * @param $latMax
     * @param $lngMax
     * @param $latMin
     * @param $lngMin
     * @param int $limit
     * @param int $offset
     * @return Place[]
     */
    public function findNearPlaces($latMax, $lngMax, $latMin, $lngMin, $limit = 50, $offset = 0)
    {
        $db = $this->createQueryBuilder('p');
        return $db->andWhere(
            $db->expr()->gte('p.lat', ':latMin')
        )->andWhere(
            $db->expr()->gte('p.lng', ':lngMin')
        )->andWhere(
            $db->expr()->lte('p.lat', ':latMax')
        )->andWhere(
            $db->expr()->lte('p.lng', ':lngMax')
        )->setParameter('latMin', $latMin)
            ->setParameter('lngMin', $lngMin)
            ->setParameter('latMax', $latMax)
            ->setParameter('lngMax', $lngMax)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    public function findByCity($city, $limit = 50, $offset = 0)
    {
        return $this->createQueryBuilder('p')
            ->join('p.city',  'c')
            ->where('c.name = :city')
            ->setParameter('city', $city)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Place[] Returns an array of Place objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Place
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
