<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserManagerService;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;


/**
 * Class UserManagerTest
 * @package App\Tests\Service
 * @author carlos <carlos.santos.goncalves@websitebutler.de>
 */
class UserManagerTest extends TestCase
{
    /** @var  EntityManager|MockObject $entityManagerMock */
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
        $this->entityManagerMock = $this->getMockBuilder(EntityManager::class)
                                        ->disableOriginalConstructor()
                                        ->getMock()
        ;

        $this->repoMock = $this->getMockBuilder(UserRepository::class)
                               ->disableOriginalConstructor()
                               ->getMock()
        ;

        $this->instance = new UserManagerService($this->entityManagerMock);
    }

    public function testCreate(): void
    {
        $user = $this->instance->create('chipreu', 'capoeira');

        $this->assertInstanceOf(User::class, $user);
    }

    public function testQuery(): void
    {
        $this->entityManagerMock->expects($this->once())
                                ->method('getRepository')
                                ->with(User::class)
                                ->willReturn($this->repoMock)
        ;

        $this->repoMock->expects($this->once())
                       ->method('findOneByName')
                       ->willReturn(new User())
        ;

        $this->assertInstanceOf(User::class, $this->instance->query('chipreu'));
    }

    public function testQueryUserNotFound(): void
    {
        $this->entityManagerMock->expects($this->once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($this->repoMock)
        ;

        $this->repoMock->expects($this->once())
            ->method('findOneByName')
            ->willReturn(null)
        ;

        $this->assertEquals('User not Found', $this->instance->query('carlos'));
    }

    public function testModify(): void
    {
        $this->entityManagerMock->expects($this->once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($this->repoMock)
        ;

        $this->repoMock->expects($this->once())
            ->method('findOneByName')
            ->willReturn(new User())
        ;

        $user = $this->instance->update('chipreu', 'cabritao','tchondicafe');

        $this->assertInstanceOf(User::class, $user);
    }

    public function testModifyUserNotFound(): void
    {

        $this->entityManagerMock->expects($this->once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($this->repoMock)
        ;

        $this->repoMock->expects($this->once())
            ->method('findOneByName')
            ->willReturn(null)
        ;

        $user = $this->instance->update('carlos', 'chipreu','capoeira');

        $this->assertEquals('User not Found', $user);
    }

    public function testDelete(): void
    {
        $this->entityManagerMock->expects($this->once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($this->repoMock)
        ;

        $this->repoMock->expects($this->once())
            ->method('findOneByName')
            ->willReturn(new User())
        ;

        $user = $this->instance->delete('cabritao');

        $this->assertEquals('User deleted', $user);
    }

    public function testCheckPasswordWeak(): void
    {
        $newHash = sha1('capoeira');

        $this->entityManagerMock->expects($this->once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($this->repoMock)
        ;

        $this->repoMock->expects($this->once())
            ->method('findOneByName')
            ->willReturn(new User())
        ;

        $this->assertFalse($this->instance->checkPassword('', $newHash));
    }
//
//    public function testCheckPasswordStrong(): void
//    {
//        $newHash = sha1('omundocabucaba');
//
//        $this->assertTrue($this->instance->checkPassword('', $newHash));
//    }
}