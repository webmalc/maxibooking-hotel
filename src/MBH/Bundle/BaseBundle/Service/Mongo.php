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

    /**
     * @var \MongoDB
     */
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
    public function remove($collection, array $data)
    {
        if (empty($data)) {
            return null;
        }
        return $this->db->$collection->remove($data);
    }

    /**
     * @param string $collection
     * @return array
     */
    public function dropCollection($collection)
    {
        return $this->db->$collection->drop();
    }

    /**
      * @param $collection
      * @param array $data
      * @return null
      */
    public function insert($collection, array $data)
    {
        if (empty($data)) {
            return null;
        }
        return $this->db->$collection->insert($data);
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
     * @param string $collectionName
     * @return \MongoCollection
     */
    public function getCollection($collectionName)
    {
        return $this->db->$collectionName;
    }

    /**
     * @param string     {

     * @param array $query
     * @param array $update
     */
    public function copy($collectionName, array $query, array $update)
    {
        $collection = $this->getCollection($collectionName);
        $parentDocs = $collection->find($query);

        $newDocs = [];
        foreach ($parentDocs as $parent) {
            foreach ($update as $key => $value) {
                if (isset($parent[$key])) {
                    $parent[$key] = $value;
                }
            }
            unset($parent['_id']);
            $newDocs[] = $parent;
        }

        $this->batchInsert($collectionName, $newDocs);
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
