<?php


namespace MBH\Bundle\SearchBundle\EventSubscriber;


use MBH\Bundle\SearchBundle\Lib\Events\InvalidateKeysEvent;
use MBH\Bundle\SearchBundle\Services\Cache\CacheKeyCreator;
use MBH\Bundle\SearchBundle\Services\Cache\CacheWarmer;
use MBH\Bundle\SearchBundle\Services\Cache\Invalidate\SearchCacheInvalidator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InvalidateEventSubscriber implements EventSubscriberInterface
{

    /** @var CacheWarmer */
    private $warmer;

    /** @var CacheKeyCreator */
    private $keyCreator;

    /**
     * InvalidateEventSubscriber constructor.
     * @param CacheWarmer $warmer
     */
    public function __construct(CacheWarmer $warmer, CacheKeyCreator $keyCreator)
    {
        $this->warmer = $warmer;
        $this->keyCreator = $keyCreator;
    }


    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            SearchCacheInvalidator::INVALIDATOR_KEY_INVALIDATE => [
                'warmUp',
                0,
            ],
        ];
    }

    /**
     * @param InvalidateKeysEvent $event
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\CacheKeyFactoryException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchConditionException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchQueryGeneratorException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SearchResultComposerException
     * @throws \MBH\Bundle\SearchBundle\Lib\Exceptions\SharedFetcherException
     */
    public function warmUp(InvalidateKeysEvent $event): void
    {
        $keys = $event->getKeys();
        if (\count($keys)) {
            foreach ($keys as $key) {
                $data = $this->keyCreator->extractWarmUpKey($key);
                $this->warmer->warmUpSpecificQuery(
                    $data['begin'],
                    $data['end'],
                    [$data['roomTypeId']],
                    [$data['tariffId']],
                    $data['combination']
                );
            }
        }
    }

}