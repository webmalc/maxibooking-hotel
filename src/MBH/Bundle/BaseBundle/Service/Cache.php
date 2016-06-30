<?php

namespace MBH\Bundle\BaseBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Helper service
 */
class Cache
{
    use ContainerAwareTrait;

    const LIFETIME = 60 * 60 * 24;

    /**
     * @var string
     */
    private $globalPrefix;

    /**
     * @var boolean
     */
    private $isEnabled;

    public function __construct(array $params)
    {
        $this->globalPrefix = $params['prefix'];
        $this->isEnabled = $params['is_enabled'];
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

        return $keyString;
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

        $memcached = $this->container->get('memcache.default');
        $memcached->set($this->generateKey($prefix, $keys), $value, 0, self::LIFETIME);

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
        $memcached = $this->container->get('memcache.default');

        return $memcached->get($this->generateKey($prefix, $keys));
    }
}
