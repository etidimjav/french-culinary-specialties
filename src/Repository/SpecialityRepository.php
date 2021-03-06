<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Speciality;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Speciality find($id, $lockMode = null, $lockVersion = null)
 * @method null|Speciality findOneBy(array $criteria, array $orderBy = null)
 * @method Speciality[]    findAll()
 * @method Speciality[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Job>
 */
class SpecialityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Speciality::class);
    }

    public function create(): Speciality
    {
        return new Speciality();
    }

    public function remove(int $id): void
    {
        /** @var object $speciality */
        $speciality = $this->getEntityManager()->getReference(
            $this->getClassName(),
            $id
        );

        $this->getEntityManager()->remove($speciality);
        $this->getEntityManager()->flush();
    }

    public function save(Speciality $speciality): void
    {
        $this->getEntityManager()->persist($speciality);
        $this->getEntityManager()->flush();
    }

    public function findById(int $id): ?Speciality
    {
        $speciality = $this->find($id);
        if (!$speciality) {
            return null;
        }

        return $speciality;
    }

    /**
     * @param mixed[] $filters
     */
    public function findByFilters($filters)
    {
        $qb = $this->createQueryBuilder('s');

        $qb
            ->addSelect('st')
            ->leftJoin('s.tags', 'st')
        ;

        if (isset($filters['name']) && null !== $name = $filters['name']) {
            $qb
                ->andWhere($qb->expr()->like('s.name', ':name'))
                ->setParameter(':name', '%'.$name.'%')
            ;
        }
        if (isset($filters['tags']) && null !== $tags = $filters['tags']) {
            foreach ($tags as $key => $tag) {
                $qb->andWhere(":value_{$key} MEMBER OF s.tags");
                $qb->setParameter("value_{$key}", $tag);
            }
        }

        $query = $qb->getQuery();

        return $query->execute();
    }
}
