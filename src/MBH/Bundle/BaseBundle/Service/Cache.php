<?php

namespace MBH\Bundle\BaseBundle\Service;

use Lsw\MemcacheBundle\Cache\MemcacheInterface;

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
     * @var MemcacheInterface
     */
    private $memcache;

    public function __construct(array $params, MemcacheInterface $memcache)
    {
        $this->globalPrefix = $params['prefix'];
        $this->isEnabled = $params['is_enabled'];
        $this->memcache = $memcache;
    }

    /**
     * @param string|null $prefix
     */
    public function clear(string $prefix = null)
    {
        $memcached = new \Memcached();
        $memcached->addServer('localhost', 11211);

        $prefix = $this->globalPrefix . '_' . $prefix ?? $this->globalPrefix;
        $keys = array_filter($memcached->getAllKeys(), function ($val) use ($prefix) {
            $length = strlen($prefix);
            return (substr($val, 0, $length) === $prefix);
        });

        array_walk($keys, function ($val) {
            $this->memcache->delete($val);
        });
    }

    /**
     * @param string $prefix
     * @param array $keys
     * @return string
     */
    public function generateKey(string $prefix, array $keys): string
    {
        $keyString = $this->globalPrefix . '_' . $prefix;

        foreach ($keys as $key) {
            if ($key instanceof \DateTime) {
                $keyString .= '_' . $key->format('d.m.Y');
            }
            elseif (is_object($key) && method_exists($key, 'getId')) {
                $keyString .= '_' . $key->getId();
            }
            elseif (is_array($key)) {
                $keyString .= '_' . implode('.', $key);
            }
            elseif (is_object($key) && !method_exists($key, '__toString')) {
                continue;
            }
            else {
                $keyString .= '_' . (string) $key;
            }

        }

        return substr($keyString, 0, 250);
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

        $this->memcache->set($this->generateKey($prefix, $keys), $value, 0, self::LIFETIME);

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

        return $this->memcache->get($this->generateKey($prefix, $keys));
    }
}
