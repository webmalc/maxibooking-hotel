<?php

use Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle;
use Gesdinet\JWTRefreshTokenBundle\GesdinetJWTRefreshTokenBundle;
use Knp\Bundle\GaufretteBundle\KnpGaufretteBundle;
use Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle;
use MBH\Bundle\BillingBundle\MBHBillingBundle;
use Ob\HighchartsBundle\Highcharts\Highchart;
use Ob\HighchartsBundle\ObHighchartsBundle;
use Oneup\FlysystemBundle\OneupFlysystemBundle;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * Class AppKernel
 */
class AppKernel extends Kernel
{
    /** @var string  */
    public const ENV_DEV = 'dev';
    /** @var string */
    public const CLIENT_VARIABLE = 'MB_CLIENT';
    /** @var string */
    public const CLIENTS_CONFIG_FOLDER = '/app/config/clients';
    /** @var string */
    public const DEFAULT_CLIENT = 'maxibooking';

    /** @var  string */
    protected $client;

    public function __construct($environment, $debug, $client = null)
    {
        $this->client = $client;
        parent::__construct($environment, $debug);

    }

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
            new Knp\Bundle\SnappyBundle\KnpSnappyBundle(),
            new Liip\ImagineBundle\LiipImagineBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle(),
            new JMS\AopBundle\JMSAopBundle(),
            new Liuggio\ExcelBundle\LiuggioExcelBundle(),
            new Ornicar\GravatarBundle\OrnicarGravatarBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new OldSound\RabbitMqBundle\OldSoundRabbitMqBundle(),
            new Phobetor\RabbitMqSupervisorBundle\RabbitMqSupervisorBundle(),
            new Lexik\Bundle\TranslationBundle\LexikTranslationBundle(),
            new Vich\UploaderBundle\VichUploaderBundle(),
            new Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle(),
            new DoctrineCacheBundle(),
            new KnpGaufretteBundle(),
            new OneupFlysystemBundle(),
            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
            new ObHighchartsBundle(),
            new LexikJWTAuthenticationBundle(),
            new GesdinetJWTRefreshTokenBundle(),


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
            new MBH\Bundle\WarehouseBundle\MBHWarehouseBundle(),
            new MBH\Bundle\RestaurantBundle\MBHRestaurantBundle(),
            new MBHBillingBundle(),
        );

        if (in_array($this->getEnvironment(), array(self::ENV_DEV, 'test'), true)) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Liip\FunctionalTestBundle\LiipFunctionalTestBundle();
            $bundles[] = new Fidry\PsyshBundle\PsyshBundle();
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

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }

    public function getClient(): ?string
    {
        return $this->client;
    }

    /**
     * @return bool
     */
    public function isDefaultClient(): bool
    {
        return $this->client === self::DEFAULT_CLIENT;
    }

    /**
     * @return bool
     */
    public function isDevEnv(): bool
    {
        return $this->getEnvironment() === self::ENV_DEV;
    }
}
