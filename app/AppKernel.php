<?php

use MBH\Bundle\SearchBundle\MBHSearchBundle;
use Nelmio\CorsBundle\NelmioCorsBundle;
use Snc\RedisBundle\SncRedisBundle;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Ob\HighchartsBundle\ObHighchartsBundle(),
            new Knp\Bundle\SnappyBundle\KnpSnappyBundle(),
            new Dinhkhanh\MongoDBAclBundle\MongoDBAclBundle(),
            new Liip\ImagineBundle\LiipImagineBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle(),
            new JMS\AopBundle\JMSAopBundle(),
            new JMS\TranslationBundle\JMSTranslationBundle(),
            new Liuggio\ExcelBundle\LiuggioExcelBundle(),
            new Ornicar\GravatarBundle\OrnicarGravatarBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new OldSound\RabbitMqBundle\OldSoundRabbitMqBundle(),
            new Phobetor\RabbitMqSupervisorBundle\RabbitMqSupervisorBundle(),
            new Vich\UploaderBundle\VichUploaderBundle(),
            new SncRedisBundle(),
            new NelmioCorsBundle(),

            //Project bundles,
            new MBH\Bundle\BaseBundle\MBHBaseBundle(),
            new MBH\Bundle\UserBundle\MBHUserBundle(),
            new MBH\Bundle\HotelBundle\MBHHotelBundle(),
            new MBH\Bundle\PriceBundle\MBHPriceBundle(),
            new MBH\Bundle\PackageBundle\MBHPackageBundle(),
            new MBH\Bundle\CashBundle\MBHCashBundle(),
            new MBH\Bundle\ChannelManagerBundle\MBHChannelManagerBundle(),
            new MBH\Bundle\OnlineBundle\MBHOnlineBundle(),
            new MBH\Bundle\DemoBundle\MBHDemoBundle(),
            new MBH\Bundle\ClientBundle\MBHClientBundle(),
            new MBH\Bundle\VegaBundle\MBHVegaBundle(),
            new MBH\Bundle\OnlineBookingBundle\MBHOnlineBookingBundle(),
            new MBH\Bundle\WarehouseBundle\MBHWarehouseBundle(),
            new MBH\Bundle\RestaurantBundle\MBHRestaurantBundle(),
            new MBHSearchBundle()
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Liip\FunctionalTestBundle\LiipFunctionalTestBundle();
        }

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }
    public function getCacheDir()
    {
        return dirname(__DIR__).'/var/cache/'.$this->getEnvironment();
    }
    public function getLogDir()
    {
        return dirname(__DIR__).'/var/logs';
    }
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }
}
