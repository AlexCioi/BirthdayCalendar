<?php

namespace App\Tests\Service;

use App\Entity\Event;
use App\Entity\Friend;
use App\Entity\User;
use App\Helpers\LocalTime;
use App\Service\FriendManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

class FriendManagerTest extends KernelTestCase
{
    protected EntityManagerInterface $entityManager;
    protected FriendManager $service;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        if ('test' !== $kernel->getEnvironment()) {
            throw new \LogicException('Execution only in Test environment possible!');
        }

        $this->initDatabase($kernel);

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->service = static::getContainer()->get(FriendManager::class);
    }

    private function initDatabase(KernelInterface $kernel): void
    {
        $entityManager = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $metaData = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->updateSchema($metaData);
    }

    public function testGetUserFriends()
    {
        $localTime = new LocalTime();

        $user = new User();
        $user->setEmail('alex');
        $user->setUsername('123');
        $this->entityManager->persist($user);

        $friend = new Friend();
        $friend->setUser($user->getUserIdentifier());
        $friend->setFirstName('alex');
        $friend->setLastName('cioi');
        $friend->setBirthDate($localTime->getLocalTime(''));
        $this->entityManager->persist($friend);

        $this->entityManager->flush();

        //dd($this->service->getUserFriends($user->getUserIdentifier()));

        $test = array($friend);
        $this->assertEquals($test, $this->service->getUserFriends('alex'));
    }
}
