<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
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
            new Misd\GuzzleBundle\MisdGuzzleBundle(),
            new IamPersistent\MongoDBAclBundle\IamPersistentMongoDBAclBundle(),
            new Liip\ImagineBundle\LiipImagineBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle($this),
            new JMS\AopBundle\JMSAopBundle(),
            new JMS\TranslationBundle\JMSTranslationBundle(),
            new Liuggio\ExcelBundle\LiuggioExcelBundle(),
            new Ornicar\GravatarBundle\OrnicarGravatarBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),

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
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }

    protected function initializeContainer() {
        parent::initializeContainer();
        if (PHP_SAPI == 'cli') {
            $this->getContainer()->enterScope('request');
            $this->getContainer()->set('request', new \Symfony\Component\HttpFoundation\Request(), 'request');
        }
    }
}
