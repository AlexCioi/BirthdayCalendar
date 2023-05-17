<?php

namespace App\Tests;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\FriendManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

class EventTest extends WebTestCase
{
    protected $client;
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->initDatabase();

        $this->entityManager = static::getContainer()
            ->get('doctrine')
            ->getManager();
    }

    private function initDatabase(): void
    {
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        $metaData = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->updateSchema($metaData);
    }

    public function testSomething(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);

        $user = new User();
        $user->setEmail('alexandrucioi@gmail.com');
        $user->setUsername('123');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $testUser = $userRepository->findOneByEmail('alexandrucioi@gmail.com');
        $this->client->loginUser($testUser);

        $crawler = $this->client->request('GET', 'dashboard/events');

        echo($crawler->html());

        $this->assertResponseIsSuccessful();
       // $this->assertSelectorTextContains('a', 'Events');
    }
}
