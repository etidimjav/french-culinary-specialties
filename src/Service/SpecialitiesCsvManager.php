<?php

namespace App\Service;

use App\Entity\Speciality;
use App\Entity\Tag;
use App\Repository\TagRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

class SpecialitiesCsvManager
{
    const CSV_TABLE_ALIAS = [
        'speciality',
        'tag',
        'specialities_tags',
    ];

    const PUBLIC_IMAGES_PATH = 'public/images/';

    /**
     * @var TagRepository
     */
    private $tagRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(TagRepository $tagRepository, EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->tagRepository = $tagRepository;
        $this->em = $em;
        $this->logger = $logger;

        $this->specialities = [];
        $this->tags = [];
    }

    /**
     * Specialities import.
     *
     * @param mixed $filepath
     * @param mixed $filename
     * @param mixed $csvAbsoluteFilepath
     * @param mixed $imagesFolderAbsoluteFilepath
     */
    public function import($csvAbsoluteFilepath, $imagesFolderAbsoluteFilepath)
    {
        //truncate table Speciality and Tag
        foreach (self::CSV_TABLE_ALIAS as $tableName) {
            $this->truncateTable($tableName);
        }

        //create images public folder
        $filesystem = new Filesystem();
        if (!$filesystem->exists(self::PUBLIC_IMAGES_PATH)) {
            $filesystem->mkdir(self::PUBLIC_IMAGES_PATH);
        }

        if ($filesystem->exists($csvAbsoluteFilepath) && '.csv' === strrchr($csvAbsoluteFilepath, '.')) {
            $this->logger->info('-------------------------------');
            $this->logger->info('-- Specialities import -- Start');

            try {
                $this->csvManagement($csvAbsoluteFilepath);
                $this->logger->info('-------------------------------');
                $this->logger->info('-- CSV Management -- OK -------');
            } catch (\Exception $ex) {
                $this->logger->info('-------------------------------');
                $this->logger->error($ex);

                return false;
            }

            $i = 1;
            foreach ($this->tags as $oneTag) {
                $tag = new Tag();
                $tag->setCreatedAt(new DateTime('NOW'));
                $tag->setName($oneTag);

                $this->em->persist($tag);
                $this->logger->info('-------------------------------');
                $this->logger->info('-----  ADD TAG '.$oneTag.'  -----');
                $this->logger->info('-------------------------------');

                if (0 == $i % 50 || $i == count($this->tags)) {
                    $this->em->flush();
                    $this->logger->info('-------------------------------');
                    $this->logger->info('-----  FLUSH TAG OK  -----');
                    $this->logger->info('-------------------------------');
                }
                ++$i;
            }

            $i = 1;
            foreach ($this->specialities as $oneSpeciality) {
                if (isset($oneSpeciality['name'])) {
                    $speciality = new Speciality();
                    $speciality->setCreatedAt(new DateTime('NOW'));
                    $speciality->setName($oneSpeciality['name']);

                    if ($filesystem->exists($imagesFolderAbsoluteFilepath.$oneSpeciality['images'])) {
                        $speciality->setMedia($oneSpeciality['images']);
                        $filesystem->copy($imagesFolderAbsoluteFilepath.$oneSpeciality['images'], self::PUBLIC_IMAGES_PATH.$oneSpeciality['images']);
                    }

                    $specialityTags = array_filter(array_unique(explode(',', $oneSpeciality['tags'])));
                    foreach ($specialityTags as $specialityTag) {
                        $entityTag = $this->tagRepository->findOneBy(['name' => $specialityTag]);
                        $speciality->addTag($entityTag);
                    }

                    $this->em->persist($speciality);
                    $this->logger->info('-------------------------------');
                    $this->logger->info('-----  ADD SPECIALITY '.$oneSpeciality['name'].'  -----');
                    $this->logger->info('-------------------------------');
                }

                if (0 == $i % 50 || $i == count($this->specialities)) {
                    $this->em->flush();
                    $this->logger->info('-------------------------------');
                    $this->logger->info('-----  FLUSH SPECIALITY OK  -----');
                    $this->logger->info('-------------------------------');
                }
                ++$i;
            }
        }
    }

    /**
     * Truncate table.
     *
     * @param mixed $tableName
     */
    private function truncateTable($tableName)
    {
        $connection = $this->em->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $connection->beginTransaction();

        try {
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS=0');
            $q = $dbPlatform->getTruncateTableSql($tableName);
            $connection->executeStatement($q);
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS=1');
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
        }
    }

    /**
     * Csv management.
     *
     * @param mixed $csvAbsoluteFilepath
     */
    private function csvManagement($csvAbsoluteFilepath)
    {
        $row = 1;
        $specialities = [];
        $tags = '';
        if (false !== ($handle = fopen($csvAbsoluteFilepath, 'r'))) {
            while (false !== ($data = fgetcsv($handle, 1000, ';'))) {
                $num = count($data);
                if (1 == $row) {
                    ++$row;

                    continue;
                }
                ++$row;

                for ($c = 0; $c < $num; ++$c) {
                    $specialities[$row] = [
                        'name' => $data[0] ?? '',
                        'tags' => $data[1] ?? '',
                        'images' => $data[2] ?? '',
                    ];
                    $tags .= !empty($data[1]) ? $data[1].',' : '';
                }
            }
            fclose($handle);
        }

        $this->tags = array_filter(array_unique(explode(',', $tags)));
        $this->specialities = $specialities;
    }
}
