<?php

namespace MBH\Bundle\BaseBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Mongo service
 */
class Mongo
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var MongoClient
     */
    protected $mongo;

    protected $db;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $this->container->getParameter('mbh.mongodb');
        $this->mongo = new \MongoClient($this->config['url']);
        $database = $this->config['db'];
        $this->db = $this->mongo->$database;
    }

    public function __destruct()
    {
        $this->close();
    }

    public function close()
    {
        $this->mongo->close();
    }

    /**
     * @param $collection
     * @param array $data
     * @return null
     */
    public function batchInsert($collection, array $data)
    {
        if (empty($data)) {
            return null;
        }
        return $this->db->$collection->batchInsert($data);
    }

    /**
     * @param $collection
     * @param array $data
     */
    public function update($collection, array $data)
    {
        foreach ($data as $entry) {
            if (!isset($entry['criteria']) || !isset($entry['values']) || !is_array($entry['criteria']) || !is_array($entry['values'])) {
                continue;
            }
            $this->db->$collection->update($entry['criteria'], ['$set' => $entry['values']]);
        }
    }
}
