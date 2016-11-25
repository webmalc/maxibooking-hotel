<?php

namespace MBH\Bundle\BaseBundle\Service;

use MBH\Bundle\BaseBundle\Document\CacheItem;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Component\Validator\Validator\ValidatorInterface;
/**
 * Helper service
 */
class Cache
{
    const LIFETIME = 60 * 60 * 24;

    /**
     * @var string
     */
    private $globalPrefix;

    /**
     * @var boolean
     */
    private $isEnabled;

    /**
     * @var \ApcuAdapter
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
        $this->cache = new ApcuAdapter();
        $this->dm = $dm->getManager();
        $this->validator = $validator;
    }

    /**
     * @param string|null $prefix
     * @param bool $all
     * @return self
     */
    public function clear(string $prefix = null, bool $all = false): self
    {
        if (!$this->isEnabled) {
            return $this;
        }
        if ($all) {
            $this->cache->clear();
            return $this;
        }

        $prefix = $this->globalPrefix . '_' . $prefix ?? $this->globalPrefix;
        $this->cache->deleteItems($this->dm->getRepository('MBHBaseBundle:CacheItem')->getKeysByPrefix($prefix));
        $this->dm->getRepository('MBHBaseBundle:CacheItem')->deleteByPrefix($prefix);

        return $this;
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
