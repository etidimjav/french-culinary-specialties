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
     * @param mixed   $page
     * @param mixed   $pageSize
     * @param mixed   $limit
     * @param mixed   $locale
     * @param mixed   $options
     */
    public function findByFilters($filters, $page, $pageSize, $limit, $locale, $options = [])
    {
        return $this->parentFindByFilters($filters, $page, $pageSize, $limit, $locale, $options);
    }
}
