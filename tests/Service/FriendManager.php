<?php

namespace App\Tests\Service;

use App\Entity\Friend;
use App\Entity\User;
use App\Repository\FriendRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Monolog\Test\TestCase;

class FriendManager extends TestCase
{

    public function testGetUserFriends()
    {
        // Set up test data
        $user = new User();
        $friends = [new Friend(), new Friend()];

        $query = $this->getMockBuilder(AbstractQuery::class)
            ->disableOriginalConstructor()
            ->getMock();

        $query->expects($this->once())
            ->method('getResult')
            ->withAnyParameters()
            ->willReturn($friends);

        // Create a mock ManagerRegistry object
        $doctrine = $this->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Create a mock EntityManagerInterface object
        $entityManager = $this->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Create a mock FriendRepository object
        $repo = $this->getMockBuilder(FriendRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $doctrine->expects($this->once())
            ->method('getRepository')
            ->withAnyParameters()
            ->willReturn($repo);

        // Set up the mock FriendRepository to return the test friends
        $qb = $this->getMockBuilder(\Doctrine\ORM\QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $qb->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $repo->expects($this->once())
            ->method('getQb')
            ->willReturn($qb);

        $repo->expects($this->once())
            ->method('getAllUserFriends')
            ->with($qb, $user);

        // Create an instance of the FriendManager class
        $friendManager = new \App\Service\FriendManager($doctrine, $entityManager);

        // Call the getUserFriends method with the test user
        $result = $friendManager->getUserFriends($user);

        // Assert that the method returned the expected output
        $this->assertEquals($friends, $result);
    }

    public function testGetUserFriends2()
    {
        // create test user
        $user = new User();
        // ... set user properties

        // create test friends
        $friend1 = new Friend();
        // ... set friend1 properties
        $friend1->setUser('alexandru');
        $this->entityManager->persist($friend1);

        $friend2 = new Friend();
        // ... set friend2 properties
        $friend2->setUser('alexandru');
        $this->entityManager->persist($friend2);

        // flush changes to database
        $this->entityManager->flush();

        $friendManager = new FriendManager($this->doctrine, $this->entityManager);

        $friends = $friendManager->getUserFriends($user);

        $this->assertCount(2, $friends);
    }
}