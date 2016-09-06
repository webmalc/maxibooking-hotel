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
     * @var \Memcached
     */
    private $memcached;

    public function __construct(array $params)
    {
        $this->globalPrefix = $params['prefix'];
        $this->isEnabled = $params['is_enabled'];
        $this->memcached = new \Memcached();
        $this->memcached->addServer('localhost', 11211);;
    }

    /**
     * @param string|null $prefix
     */
    public function clear(string $prefix = null)
    {
        $memcached = $this->memcached;

        $prefix = $this->globalPrefix . '_' . $prefix ?? $this->globalPrefix;
        $keys = array_filter($memcached->getAllKeys() ? $memcached->getAllKeys() : [], function ($val) use ($prefix) {
            $length = strlen($prefix);
            return (substr($val, 0, $length) === $prefix);
        });

        array_walk($keys, function ($val) {
            $this->memcached->delete($val);
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

        $this->memcached->set($this->generateKey($prefix, $keys), $value, self::LIFETIME);

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

        return $this->memcached->get($this->generateKey($prefix, $keys));
    }
}
