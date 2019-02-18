<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserManagerService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;


/**
 * Class UserManagerServiceTest
 * @package App\Tests\Service
 * @author carlos <carlos.santos.goncalves@websitebutler.de>
 */
class UserManagerServiceTest extends TestCase
{
    /** @var  EntityManagerInterface|MockObject $entityManagerMock */
    protected $entityManagerMock;

    /** @var  UserManagerService $instance */
    protected $instance;

    /**
     * @var UserRepository|MockObject $repoMock
     */
    protected $repoMock;

    /**
     * @throws \ReflectionException
     */
    public function setUp(): void
    {

        $this->repoMock = $this->getMockBuilder(UserRepository::class)
                               ->disableOriginalConstructor()
                               ->getMock()
        ;

        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);

        $this->instance = new UserManagerService($this->entityManagerMock);
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testCreate(): void
    {
        $user = $this->instance->create('chipreu', 'capoeira');

        $this->assertInstanceOf(User::class, $user);
    }

    public function testQuery(): void
    {
        $this->findOneByNameMock(new User);

        $this->assertInstanceOf(User::class, $this->instance->query('chipreu'));
    }

    public function testQueryUserNotFound(): void
    {
        $this->findOneByNameMock();

        $this->assertEquals('User not Found', $this->instance->query('carlos'));
    }

    public function testModify(): void
    {
        $this->findOneByNameMock(new User);

        $user = $this->instance->update('chipreu', 'cabritao','tchondicafe');

        $this->assertInstanceOf(User::class, $user);
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testModifyUserNotFound(): void
    {

        $this->findOneByNameMock();

        $user = $this->instance->update('carlos', 'chipreu','capoeira');

        $this->assertEquals('User not Found', $user);
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testDelete(): void
    {
        $this->findOneByNameMock(new User);

        $user = $this->instance->delete('cabritao');

        $this->assertEquals('User deleted', $user);
    }

    public function testCheckPasswordWeak(): void
    {
        $newHash = sha1('capoeira');

        $this->countUsersByPasswordMock();

        $this->assertFalse($this->instance->checkPassword('password', $newHash));
    }

    public function testCheckPasswordStrong(): void
    {
        $newHash = sha1('omundocabucaba');

        $this->countUsersByPasswordMock();

        $this->assertTrue($this->instance->checkPassword('', $newHash));
    }

    private function countUsersByPasswordMock(): void
    {
        $this->entityManagerMock->expects($this->once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($this->repoMock)
        ;

        $this->repoMock->expects($this->once())
            ->method('countUsersByPassword')
            ->willReturn(0)
        ;
    }

    private function findOneByNameMock(User $user = null)
    {
        $this->entityManagerMock->expects($this->once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($this->repoMock)
        ;

        $this->repoMock->expects($this->once())
            ->method('findOneByName')
            ->willReturn($user)
        ;
    }
}