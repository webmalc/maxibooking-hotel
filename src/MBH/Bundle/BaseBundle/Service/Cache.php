<?php

namespace MBH\Bundle\BaseBundle\Service;

use MBH\Bundle\BaseBundle\Document\CacheItem;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Component\Validator\Validator\ValidatorInterface;
/**
 * Helper service
 */
class Cache
{
    const LIFETIME = 60 * 60 * 24 * 3;

    /**
     * @var string
     */
    private $globalPrefix;

    /**
     * @var boolean
     */
    private $isEnabled;

    /**
     * @var RedisAdapter
     */
    private $cache;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $dm;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(array $params, ManagerRegistry $dm, ValidatorInterface $validator)
    {
        $this->globalPrefix = $params['prefix'];
        $this->isEnabled = $params['is_enabled'];
        $redis = RedisAdapter::createConnection('redis://az-mongo');
        $this->cache = new RedisAdapter($redis);
        $this->dm = $dm->getManager();
        $this->validator = $validator;
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

        $this->cache->deleteItems(
            $this->dm->getRepository('MBHBaseBundle:CacheItem')->getKeysByPrefix($prefix, $begin, $end)
        );

        return $this->dm->getRepository('MBHBaseBundle:CacheItem')->deleteByPrefix($prefix, $begin, $end);
    }

    /**
     * @param string $prefix
     * @param array $keys
     * @return string
     */
    public function generateKey(string $prefix, array $keys): string
    {
        $keyString = $this->globalPrefix . '_' . $prefix;
        $hash = '';

        foreach ($keys as $key) {
            if ($key instanceof \DateTime) {
                $hash .= '_' . $key->format('d.m.Y');
            }
            elseif (is_object($key) && method_exists($key, 'getId')) {
                $hash .= '_' . $key->getId();
            }
            elseif (is_array($key)) {
                $hash .= '_' . implode('.', $key);
            }
            elseif (is_object($key) && !method_exists($key, '__toString')) {
                continue;
            }
            else {
                $hash .= '_' . (string) $key;
            }

        }

        return $keyString . '_' . md5($hash);
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
        $item->set($value)->expiresAfter(self::LIFETIME);
        $this->cache->save($item);

        //save key to database
        $cacheItem = new CacheItem($key);

        if (!count($this->validator->validate($cacheItem))) {
            $dates = array_values(array_filter($keys, function ($entry) {
                return $entry instanceof \DateTime;
            }));

            if (isset($dates[0])) {
                $cacheItem->setBegin($dates[0]);
            }
            if (isset($dates[1])) {
                $cacheItem->setEnd($dates[1]);
            }

            $this->dm->persist($cacheItem);
            $this->dm->flush();
        }

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
