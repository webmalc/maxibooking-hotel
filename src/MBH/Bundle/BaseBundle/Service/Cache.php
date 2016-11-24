<?php

namespace MBH\Bundle\BaseBundle\Service;

use Symfony\Component\Cache\Adapter\ApcuAdapter;

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

    public function __construct(array $params)
    {
        $this->globalPrefix = $params['prefix'];
        $this->isEnabled = $params['is_enabled'];
        $this->cache = new ApcuAdapter();
    }

    /**
     * @param string|null $prefix
     */
    public function clear(string $prefix = null)
    {
        $this->cache->clear();
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

        $item = $this->cache->getItem($this->generateKey($prefix, $keys));
        $item->set($value)->expiresAfter(self::LIFETIME);
        $this->cache->save($item);

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
