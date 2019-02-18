<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;


/**
 * Class UserManagerService
 * @package App\Service
 * @author carlos <carlos.santos.goncalves@websitebutler.de>
 */
class UserManagerService
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param $name
     * @param $password
     * @return User|string
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create($name, $password)
    {
        $user = new User();

        $user
            ->setName($name)
            ->setPassword($password)
        ;

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @param $name
     * @return User|string
     */
    public function query($name)
    {
        try{
            $user = $this->getUser($name);

            if ($user instanceof User) {
                return $user;
            }

            return 'User not Found';
        } catch (NonUniqueResultException $exception) {
            return 'Internal Error. Contact IT Department';
        }
    }

    /**
     * @param null $name
     * @param null $newName
     * @param null $newPassword
     * @return User|null|string
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function update($name = null, $newName = null , $newPassword = null)
    {
        try{
            $user = $this->getUser($name);

            if ($user instanceof User) {

                if (null !== $newName) {
                    $user->setName($newName);
                }

                if (null !== $newPassword) {
                    $user->setPassword($newPassword);
                }

                $this->entityManager->flush();

                return $user;
            }

            return 'User not Found';
        } catch (NonUniqueResultException $exception) {
            return 'Internal Error. Contact IT Department';
        }


    }

    /**
     * @param $name
     * @return string
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function delete($name): string
    {
        try {
            $user = $this->getUser($name);

            if ($user instanceof User) {
                $this->entityManager->remove($user);
                $this->entityManager->flush();

                return 'User deleted';
            }

            return 'User not Found';
        } catch (NonUniqueResultException $e) {
            return 'Internal Error. Contact IT Department';
        }
    }

    /**
     * @param $name
     * @return User|null
     * @throws NonUniqueResultException
     */
    public function getUser($name): ?User
    {
        return $this->getUserRepository()->findOneByName($name);
    }

    /**
     * @return UserRepository|\Doctrine\Common\Persistence\ObjectRepository
     */
    public function getUserRepository()
    {
        return $this->entityManager->getRepository(User::class);
    }

    /**
     * @param $password
     * @param $newHash
     * @return bool
     */
    public function checkPassword($password, $newHash): bool
    {
        try{
            $firstFiveChar = strtoupper(substr($newHash, 0, 5));
            $client = new Client(['base_uri' => 'https://api.pwnedpasswords.com/range/']);
            $response = $client->request('GET', $firstFiveChar);
            $hashSuffixes = $response->getBody()->getContents();
            /** @var int $user */
            $user = $this->getUserRepository()->countUsersByPassword($password);
            $newSuffix = strtoupper(substr($newHash, 5));
        } catch (GuzzleException $e) {
            return 'Could\'t verify password. Contact IT Department' ;
        }

        return !(strpos($hashSuffixes, $newSuffix) || $user > 0);
    }
}