<?php

namespace App\Repository;

use App\Entity\Friend;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Friend>
 *
 * @method Friend|null find($id, $lockMode = null, $lockVersion = null)
 * @method Friend|null findOneBy(array $criteria, array $orderBy = null)
 * @method Friend[]    findAll()
 * @method Friend[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FriendRepository extends ServiceEntityRepository
{
    public function getQb()
    {
        return $this->createQueryBuilder('friend');
    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Friend::class);
    }

    public function save(Friend $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Friend $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getAllUserFriends(QueryBuilder $qb, $user): self
    {
        $qb
            ->where('friend.user = :user')
            ->orderBy('friend.birthDate', 'ASC')
            ->setParameters([
                'user' => $user
            ]);

        return $this;
    }

    public function getNotificationBirthdays(QueryBuilder $qb): self
    {
        date_default_timezone_set('Europe/Bucharest');
        $timezone = new \DateTimeZone(date_default_timezone_get());
        $localTime = new \DateTime('now');
        $localTime->setTimezone($timezone);
        $localTime->setTime(0, 0 , 0);

        $qb
            ->where('friend.notification_date = :localTime')
            ->setParameters([
                'localTime' => $localTime
            ]);

        return $this;
    }

//    /**
//     * @return Friend[] Returns an array of Friend objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Friend
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
