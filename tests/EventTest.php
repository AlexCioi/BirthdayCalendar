<?php

namespace App\Tests;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EventTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();


        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('alexandrucioi@gmail.com');
        $client->loginUser($testUser);

        $crawler = $client->request('GET', 'dashboard/events');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('a', 'Events');
    }
}
