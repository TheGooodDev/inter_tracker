<?php

namespace App\Repository;

use App\Entity\Donjon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Faker\Generator;
use Faker\Factory;

/**
 * @extends ServiceEntityRepository<Donjon>
 *
 * @method Donjon|null find($id, $lockMode = null, $lockVersion = null)
 * @method Donjon|null findOneBy(array $criteria, array $orderBy = null)
 * @method Donjon[]    findAll()
 * @method Donjon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DonjonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Donjon::class);
    }

    public function save(Donjon $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Donjon $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findWithPagination($page,$limit){
        $qb = $this->createQueryBuilder('s')
        ->setFirstResult(($page-1)*$limit)
        ->setMaxResults($limit);
        return $qb->getQuery()->getResult();
    }

    /**
     * Return a random Donjon entity
     * @return Donjon
     */
    public function getRandomDonjon() : ?Donjon
    {
        $this->faker = Factory::create("fr_FR");
        $idtoLook = $this->faker->numberBetween(0,count($this->findAll())-1);
        return $this->findAll()[$idtoLook];
    }

//    /**
//     * @return Donjon[] Returns an array of Donjon objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Donjon
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
