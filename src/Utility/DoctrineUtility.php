<?php
namespace App\Utility;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Helper\HelperSet;

/**
 * $doctrineUtility = DoctrineUtility::getInstance();
 * $doctrineUtility->addMappingPath(__DIR__ . '/Classes/Entity');
 * $entityManager = $doctrineUtility->getEntityManager();
 */
class DoctrineUtility extends Singleton {
    protected array $mappingPaths = [];
    protected ?EntityManager $entityManager = null;

    public static function getInstance(): DoctrineUtility {
        return parent::getInstance();
    }

    /**
     * @param string|array $mappingPath
     * @return void
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\ORMException
     */
    public function addMappingPath($mappingPath = []) {
        $mappingPath = (array)$mappingPath;
        $recreate = false;
        foreach ($mappingPath as $path) {
            if (!in_array($path, $this->mappingPaths)) {
                $this->mappingPaths[] = $path;
                $recreate = true;
            }
        }
        if ($recreate) {
            $this->createEntityManager();
        }
    }

    public function getEntityManager(): EntityManager {
        return $this->entityManager;
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\ORMException
     */
    protected function createEntityManager(): EntityManager {
        $isDevMode = (wp_get_environment_type() === 'development');

        $config = \Doctrine\ORM\ORMSetup::createAttributeMetadataConfiguration([], $isDevMode);
        $connection = \Doctrine\DBAL\DriverManager::getConnection([
            'driver' => 'pdo_mysql',
            'host' => DB_HOST,
            'user' => DB_USER,
            'password' => DB_PASSWORD,
            'dbname' => DB_NAME,
        ], $config);

        /** @var EntityManager $entityManager */
        $this->entityManager = new \Doctrine\ORM\EntityManager($connection, $config);
        return $this->entityManager;
    }
}
