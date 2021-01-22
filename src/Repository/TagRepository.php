<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Tag find($id, $lockMode = null, $lockVersion = null)
 * @method null|Tag findOneBy(array $criteria, array $orderBy = null)
 * @method Tag[]    findAll()
 * @method Tag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Job>
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    public function create(): Tag
    {
        return new Tag();
    }

    public function remove(int $id): void
    {
        /** @var object $tag */
        $tag = $this->getEntityManager()->getReference(
            $this->getClassName(),
            $id
        );

        $this->getEntityManager()->remove($tag);
        $this->getEntityManager()->flush();
    }

    public function save(Tag $tag): void
    {
        $this->getEntityManager()->persist($tag);
        $this->getEntityManager()->flush();
    }

    public function findById(int $id): ?Tag
    {
        $tag = $this->find($id);
        if (!$tag) {
            return null;
        }

        return $tag;
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
