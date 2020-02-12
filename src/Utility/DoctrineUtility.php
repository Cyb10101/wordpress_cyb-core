<?php
namespace App\Utility;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Doctrine\ORM\Tools\Setup;
use Symfony\Component\Console\Helper\HelperSet;

/**
 * Class DoctrineUtility
 *
 * $doctrineUtility = DoctrineUtility::getInstance();
 * $doctrineUtility->addMappingPath(__DIR__ . '/Classes/Entity');
 * $entityManager = $doctrineUtility->getEntityManager();
 */
class DoctrineUtility extends Singleton {
    /**
     * @var array
     */
    protected $mappingPaths = [];

    /**
     * @var EntityManager
     */
    protected $entityManager = null;

    /**
     * @var HelperSet
     */
    protected $helperSet = null;

    /**
     * @return DoctrineUtility
     */
    public static function getInstance(): self {
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
            $this->createHelperSet($this->createEntityManager());
        }
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager(): EntityManager {
        return $this->entityManager;
    }

    /**
     * @return HelperSet
     */
    public function getHelperSet(): HelperSet {
        return $this->helperSet;
    }

    /**
     * @return EntityManager
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\ORMException
     */
    protected function createEntityManager(): EntityManager {
        $configuration = new Configuration();
        /** @var Connection $connection */
        $connection = DriverManager::getConnection([
            'url' => 'mysql://' . DB_USER . ':' . DB_PASSWORD . '@' . DB_HOST . '/' . DB_NAME
        ], $configuration);

        $isDevMode = true;
        $proxyDir = null;
        $cache = null;
        $useSimpleAnnotationReader = false;

        /** @var \Doctrine\ORM\Configuration $configuration */
        $configuration = Setup::createAnnotationMetadataConfiguration($this->mappingPaths, $isDevMode, $proxyDir, $cache, $useSimpleAnnotationReader);
        /** @var EntityManager $entityManager */
        $this->entityManager = EntityManager::create($connection, $configuration);
        return $this->entityManager;
    }

    /**
     * @param EntityManager $entityManager
     * @return HelperSet
     */
    protected function createHelperSet(EntityManager $entityManager): HelperSet {
        $this->helperSet = new HelperSet([
            'db' => new ConnectionHelper($entityManager->getConnection()),
            'em' => new EntityManagerHelper($entityManager)
        ]);
        return $this->helperSet;
    }
}
