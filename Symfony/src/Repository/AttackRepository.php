<?php

namespace App\Repository;

use App\Entity\Attack;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Attack>
 *
 * @method Attack|null find($id, $lockMode = null, $lockVersion = null)
 * @method Attack|null findOneBy(array $criteria, array $orderBy = null)
 * @method Attack[]    findAll()
 * @method Attack[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AttackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Attack::class);
    }

    public function save(Attack $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Attack $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Attack[] Returns an array of Attack objects
    */
    public function findAvailableAttacksForLevelAndType($level, $type): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT a
            FROM App\Entity\Attack a
            WHERE a.levelRequired <= :level
            AND a.attackTree = :type'
        )->setParameters([
            'level' => $level,
            'type' => $type
        ]);

        // returns an array of Attacks
        return $query->getResult();
    }
}
