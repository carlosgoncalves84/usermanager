<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param $name
     * @return User[] Returns an array of User objects
     */
    public function findByName($name): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.name = :name')
            ->setParameter('name', $name)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param $password
     * @return User[] Returns an array of User objects
     */
    public function findByPassword($password): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.password = :password')
            ->setParameter('password', $password)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param string $name
     * @return User|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByName($name): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param string $password
     * @return User|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByPassword($password): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.password = :password')
            ->setParameter('password', $password)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param $name
     * @return int
     */
    public function countUsersByName($name): int
    {
        try {
            return $this->createQueryBuilder('u')
                ->select('COUNT(u.id)')
                ->andWhere('u.name = :name')
                ->setParameter('name', $name)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
            return 0;
        }
    }

    /**
     * @param $password
     * @return int
     */
    public function countUsersByPassword($password): int
    {
        try {
            return $this->createQueryBuilder('u')
                ->select('COUNT(u.id)')
                ->andWhere('u.password = :password')
                ->setParameter('password', $password)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
            return 0;
        }
    }

}
