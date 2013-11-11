<?php
use Doctrine\ORM\EntityManager;

/**
 * Service layer for all kinds of connection related logic
 *
 * Class sspmod_janus_ConnectionService
 */
class sspmod_janus_ConnectionService extends sspmod_janus_Database
{

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * JANUS configuration
     * @var SimpleSAML_Configuration
     */
    private $config;

    /**
     * @param EntityManager $entityManager
     * @param SimpleSAML_Configuration $config
     */
    public function __construct(EntityManager $entityManager, SimpleSAML_Configuration $config)
    {
        $this->entityManager = $entityManager;
        $this->config = $config;
        parent::__construct($config->getValue('store'));
    }

    /**
     * @param int $id
     * @return sspmod_janus_Model_Connection
     * @throws Exception
     */
    public function getById($id)
    {
        $connection = $this->entityManager->getRepository('sspmod_janus_Model_Connection')->find($id);
        if (!$connection instanceof sspmod_janus_Model_Connection) {
            throw new \Exception("Connection '{$id}' not found");
        }

        return $connection;
    }

    public function getRevisionByEidAndRevision($eid, $revisionNr = null) {
        var_dump(array($eid, $revisionNr));exit;
        if (!$revisionNr) {
            $queryBuilder = $this->entityManager->createQueryBuilder();
            $revisionNr = $queryBuilder
                ->select('MAX(r.revisionNr)')
                ->from('sspmod_janus_Model_Connection_Revision','r')
                ->where($queryBuilder->expr()->eq('r.connection', ':eid' ))
                ->setParameter('eid', $eid)
                ->getQuery()->execute();
        }
        $connectionRevision = $this->entityManager->getRepository('sspmod_janus_Model_Connection_Revision')->findOneBy(array(
            'connection' => $eid,
            'revisionNr' => $revisionNr
            )
        );
        return $connectionRevision;
    }

    /**
     * Grants a user permission to a given entity
     *
     * @param sspmod_janus_Model_Connection $entity
     * @param sspmod_janus_Model_User $user
     */
    public function addUserPermission(sspmod_janus_Model_Connection $connection, sspmod_janus_Model_User $user)
    {
        $userConnectionRelation = new sspmod_janus_Model_User_ConnectionRelation(
            $user,
            $connection
        );

        $this->entityManager->persist($userConnectionRelation);
        $this->entityManager->flush();
    }
}
