<?php

namespace MBH\Bundle\BaseBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\BaseBundle\Document\CacheItem;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bridge\Monolog\Logger;

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

    /** @var ArrayCollection */
    private $clearCollection;

    /** @var ProducerInterface */
    private $producer;

    public function __construct(
        array $params,
        string $redisUrl,
        ManagerRegistry $documentManager,
        ValidatorInterface $validator,
        Logger $logger,
        Mongo $mongo,
        ProducerInterface $producer
    ) {
        $this->globalPrefix = $params['prefix'];
        $this->isEnabled = $params['is_enabled'];
        $this->lifetime = $params['lifetime'];

        if (!$this->lifetime) {
            throw new \InvalidArgumentException('lifetime == 0');
        }
        $this->mongo = $mongo;
        $redis = RedisAdapter::createConnection($redisUrl);
        $this->cache = new RedisAdapter($redis);
        $this->documentManager = $documentManager->getManager();
        $this->validator = $validator;
        $this->producer = $producer;
        if (!empty($params['logs'])) {
            $this->logger = $logger;
        }
        $this->clearCollection = new ArrayCollection();
    }


    /**
     * @param ArrayCollection $clearCollection
     */
    public function setClearCollection(ArrayCollection $clearCollection)
    {
        $this->clearCollection = $clearCollection;
    }

    /**
     * clear expired CacheItems from database
     *
     * @return int
     */
    public function clearExpiredItems(): int
    {
        return $this->documentManager
            ->getRepository('MBHBaseBundle:CacheItem')->clearExpiredItems();
    }

    /**
     * @param string|null $prefix
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param bool $all
     * @return int
     */
    public function clear(string $prefix = null, \DateTime $begin = null, \DateTime $end = null, bool $all = false): void
    {
        if (!$this->isEnabled) {
            return;
        }

        $prefix = $all ? 'all' : $this->globalPrefix.'_'.$prefix ?? $this->globalPrefix;
        $item = CacheItem::getInstance($prefix, $begin, $end);
        $this->addClearItem($item);
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
                $hash .= '_'.$key->format('d.m.Y');
            } elseif (is_object($key) && method_exists($key, 'getId')) {
                $hash .= '_'.$key->getId();
            } elseif (is_array($key)) {
                $hash .= '_'.implode('.', $key);
            } elseif (is_object($key) && !method_exists($key, '__toString')) {
                continue;
            } else {
                $hash .= '_'.(string)$key;
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
        $keyString = $this->globalPrefix.'_'.$prefix;

        return $keyString.'_'.md5($this->generateArgsString($keys));
    }

    /**
     * @param mixed $value
     * @param string $prefix
     * @param array $keys
     * @return Cache
     */
    public function set($value, string $prefix, array $keys): Cache
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
                'SET: '.$key.'__'.$this->generateArgsString($keys).
                ' - LIFETIME: '.$this->lifetime
            );
        }
        $dates = array_values(
            array_filter(
                $keys,
                function ($entry) {
                    return $entry instanceof \DateTime;
                }
            )
        );

        $data = ['key' => $key];

        if (isset($dates[0])) {
            $data['begin'] = new \MongoDate($dates[0]->getTimestamp());
        }
        if (isset($dates[1])) {
            $data['end'] = new \MongoDate($dates[1]->getTimestamp());
        }
        $expiresAt = new \DateTime('+'.$this->lifetime.' days');
        $data['lifetime'] = new \MongoDate($expiresAt->getTimestamp());

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


    private function addClearItem(CacheItem $item)
    {
        $this->clearCollection->add($item);
    }

    private function isClearAll(): bool
    {
        $result = false;

        foreach ($this->clearCollection as $item) {
            /** @var CacheItem $item */
            if ($item->getKey() === 'all') {
                $result = true;
                break;
            }
        }

        return $result;
    }

    public function cleanCache()
    {
        $repo = $this->documentManager->getRepository('MBHBaseBundle:CacheItem');
        if ($this->isClearAll()) {
            $this->cache->clear();
            $repo->deleteByPrefix('');
            $this->clearCollection = new ArrayCollection();

            return 0;
        }

        foreach ($this->clearCollection as $item) {
            /** @var CacheItem $item */
            $prefix = $item->getKey();
            $begin = $item->getBegin();
            $end = $item->getEnd();


            $keys = $this->documentManager->getRepository('MBHBaseBundle:CacheItem')->getKeysByPrefix(
                $prefix,
                $begin,
                $end
            );

            $this->cache->deleteItems($keys);
            if ($this->logger) {
                $this->logger->info('DEL: '.implode('', $keys));
            }

            $repo->deleteByPrefix($prefix, $begin, $end);
        }

        $this->clearCollection = new ArrayCollection();

        return 0;
    }

    public function onKernelTerminate(PostResponseEvent $event)
    {
        if ($this->clearCollection->count()) {
            $message = serialize($this->clearCollection);
            $this->producer->publish($message);
        }
    }

}
