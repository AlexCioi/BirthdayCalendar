<?php

namespace App\Repository;

use App\Entity\Event;
use App\Helpers\LocalTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function getQb()
    {
        return $this->createQueryBuilder('e');
    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function save(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getAllUserEvents(QueryBuilder $qb, $user): self
    {
        $localTime = new LocalTime();
        $localTime = $localTime->getLocalTime('');

        $qb
            ->where('e.user = :user')
            ->andWhere($qb->expr()->andX(
                $qb->expr()->gte('e.dueDate', ':localTime'),
            ))
            ->orderBy('e.dueDate', 'ASC')
            ->setParameters([
                'user' => $user,
                'localTime' => $localTime
            ]);

        return $this;
    }

    public function getShortTermUserEvents(QueryBuilder $qb, $user): self
    {
        $localTime = new LocalTime();
        $localTime = $localTime->getLocalTime('dateTime');

        $endTime = clone $localTime;
        $endTime->add(new \DateInterval('P3D'));
        $endTime->setTime(23,59,59);

        $qb
            ->where('e.user = :user')
            ->andWhere($qb->expr()->andX(
                $qb->expr()->gte('e.dueDate', ':localTime'),
                $qb->expr()->lte('e.dueDate', ':endTime')
            ))
            ->orderBy('e.dueDate', 'ASC')
            ->setParameters([
                'localTime' => $localTime,
                'endTime' => $endTime,
                'user' => $user
            ]);

        return $this;
    }

    public function getUserPastEvents(QueryBuilder $qb, $user): self
    {
        date_default_timezone_set('Europe/Bucharest');
        $timezone = new \DateTimeZone(date_default_timezone_get());
        $localTime = new \DateTime('now');
        $localTime->setTimezone($timezone);
        $localTime->setTime(0, 0 , 0);

        $qb
            ->where('e.user = :user')
            ->andWhere($qb->expr()->andX(
                $qb->expr()->lt('e.dueDate', ':localTime')
            ))
            ->orderBy('e.dueDate', 'ASC')
            ->setParameters([
                'localTime' => $localTime,
                'user' => $user
            ]);

        return $this;
    }

//    /**
//     * @return Event[] Returns an array of Event objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Event
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
