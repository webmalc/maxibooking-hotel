<?php

namespace MBH\Bundle\BaseBundle\Service;

use MBH\Bundle\BaseBundle\Document\CacheItem;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bridge\Monolog\Logger;
use MBH\Bundle\BaseBundle\Service\Mongo;

/**
 * Helper service
 */
class Cache
{
    /**
     * @var int
     */
    private $lifetime;

    /**
     * @var string
     */
    private $globalPrefix;

    /**
     * @var boolean
     */
    private $isEnabled;

    /**
     * @var \RedisAdapter
     */
    private $cache;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $documentManager;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var Mongo
     */
    private $mongo;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(array $params, string $redisUrl, ManagerRegistry $documentManager, ValidatorInterface $validator, Logger $logger, Mongo $mongo)
    {
        $this->globalPrefix = $params['prefix'];
        $this->isEnabled = $params['is_enabled'];
        $this->lifetime = $params['lifetime'];
        $this->mongo = $mongo;
        $redis = RedisAdapter::createConnection($redisUrl);
        $this->cache = new RedisAdapter($redis);
        $this->documentManager = $documentManager->getManager();
        $this->validator = $validator;
        if (!empty($params['logs'])) {
            $this->logger = $logger;
        }
    }

    /**
     * @param string|null $prefix
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param bool $all
     * @return int
     */
    public function clear(string $prefix = null, \DateTime $begin = null, \DateTime $end = null, bool $all = false): int
    {
        if (!$this->isEnabled) {
            return 0;
        }
        if ($all) {
            $this->cache->clear();
            return 0;
        }

        $prefix = $this->globalPrefix . '_' . $prefix ?? $this->globalPrefix;

        $keys = $this->documentManager->getRepository('MBHBaseBundle:CacheItem')
            ->getKeysByPrefix($prefix, $begin, $end);
        $this->cache->deleteItems($keys);
        if ($this->logger) {
            $this->logger->info('DEL: ' . implode('', $keys));
        }

        return $this->documentManager->getRepository('MBHBaseBundle:CacheItem')->deleteByPrefix($prefix, $begin, $end);
    }
    /**
     * @param array $keys
     * @return string
     */
    public function generateArgsString(array $keys): string
    {
        $hash = '';

        foreach ($keys as $key) {
            if ($key instanceof \DateTime) {
                $hash .= '_' . $key->format('d.m.Y');
            } elseif (is_object($key) && method_exists($key, 'getId')) {
                $hash .= '_' . $key->getId();
            } elseif (is_array($key)) {
                $hash .= '_' . implode('.', $key);
            } elseif (is_object($key) && !method_exists($key, '__toString')) {
                continue;
            } else {
                $hash .= '_' . (string) $key;
            }
        }

        return $hash;
    }

    /**
     * @param string $prefix
     * @param array $keys
     * @return string
     */
    public function generateKey(string $prefix, array $keys): string
    {
        $keyString = $this->globalPrefix . '_' . $prefix;

        return $keyString . '_' . md5($this->generateArgsString($keys));
    }

    /**
     * @param mixed $value
     * @param string $prefix
     * @param array $keys
     * @return Cache
     */
    public function set($value, string $prefix, array $keys) : Cache
    {
        if (!$this->isEnabled) {
            return $this;
        }

        $key = $this->generateKey($prefix, $keys);

        $item = $this->cache->getItem($this->generateKey($prefix, $keys));
        $item->set($value)->expiresAfter($this->lifetime * 24 * 60 * 60);
        $this->cache->save($item);

        if ($this->logger) {
            $this->logger->info(
                'SET: ' . $key . '__' .$this->generateArgsString($keys) .
                ' - LIFETIME: ' . $this->lifetime
            );
        }
        $dates = array_values(array_filter($keys, function ($entry) {
            return $entry instanceof \DateTime;
        }));

        $data = ['key' => $key];

        if (isset($dates[0])) {
            $data['begin'] = new \MongoDate($dates[0]->getTimestamp());
        }
        if (isset($dates[1])) {
            $data['end'] = new \MongoDate($dates[1]->getTimestamp());
        }

        $this->mongo->insert('CacheItem', $data);

        return $this;
    }

    /**
     * @param string $prefix
     * @param array $keys
     * @return mixed
     */
    public function get(string $prefix, array $keys)
    {
        if (!$this->isEnabled) {
            return false;
        }

        $item = $this->cache->getItem($this->generateKey($prefix, $keys));
        return $item->isHit() ? $item->get() : false;
    }
}
