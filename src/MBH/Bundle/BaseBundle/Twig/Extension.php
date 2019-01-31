<?php
namespace MBH\Bundle\BaseBundle\Twig;

use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\PackageBundle\Models\Billing\Country;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Extension extends \Twig_Extension
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Symfony\Component\Translation\IdentityTranslator
     */
    protected $translator;

    /**
    * @var \Doctrine\ODM\MongoDB\DocumentManager
    */
    protected $dm;

    private $clientConfig;
    private $isClientConfigInit = false;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->translator = $this->container->get('translator');
        $this->dm = $this->container->get('doctrine_mongodb')->getManager();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mbh_twig_extension';
    }

    /**
     * @return string
     */
    public function format(\DateTime $date = null)
    {
        if (!$date) {
            return "<span class='date-month date-infinity'>∞</span>";
        }
        $now = new \DateTime();

        if ($now->format('Y') != $date->format('Y')) {
            return $date->format('d.m.y');
        }

        $months = [
            $this->translator->trans('twig.extension.jan', []),
            $this->translator->trans('twig.extension.feb', []),
            $this->translator->trans('twig.extension.march', []),
            $this->translator->trans('twig.extension.april', []),
            $this->translator->trans('twig.extension.may', []),
            $this->translator->trans('twig.extension.june', []),
            $this->translator->trans('twig.extension.july', []),
            $this->translator->trans('twig.extension.august', []),
            $this->translator->trans('twig.extension.september', []),
            $this->translator->trans('twig.extension.october', []),
            $this->translator->trans('twig.extension.november', []),
            $this->translator->trans('twig.extension.december', [])
        ];

        return sprintf(
            '%s&nbsp<span class=\"date-month\">%s</span>.',
                $date->format('d'),
                $months[$date->format('n') - 1]
        );
    }

    public function md5($value)
    {
        return md5($value);
    }

    public function num2str($value)
    {
        return $this->container->get('mbh.helper')->num2str($value);
    }

    public function num2enStr($value)
    {
        return $this->container->get('mbh.helper')->convertNumberToWords($value);
    }

    public function translateToLat($value)
    {
        return $this->container->get('mbh.helper')->translateToLat($value);
    }

    /**
     * @param $authorityOrganId
     * @return \MBH\Bundle\PackageBundle\Models\Billing\AuthorityOrgan
     */
    public function getAuthorityOrganById($authorityOrganId)
    {
        return $this->container->get('mbh.billing.api')->getAuthorityOrganById($authorityOrganId);
    }

    /**
     * @param $countryTld
     * @param null $locale
     * @return \MBH\Bundle\PackageBundle\Models\Billing\Country
     */
    public function getCountryByTld($countryTld, $locale = null)
    {
        return $this->container->get('mbh.billing.api')->getCountryByTld($countryTld, $locale);
    }

    /**
     * @param $regionId
     * @param null $locale
     * @return \MBH\Bundle\PackageBundle\Models\Billing\Region
     */
    public function getRegionById($regionId, $locale = null)
    {
        return $this->container->get('mbh.billing.api')->getRegionById($regionId, $locale);
    }

    /**
     * @param $cityId
     * @param null $locale
     * @return \MBH\Bundle\PackageBundle\Models\Billing\City
     */
    public function getCityById($cityId, $locale = null)
    {
        return $this->container->get('mbh.billing.api')->getCityById($cityId, $locale);
    }

    /**
     * @param \MongoDate $mongoDate
     * @return \DateTime
     */
    public function convertMongoDate(\MongoDate $mongoDate)
    {
        return new \DateTime('@' . $mongoDate->sec);
    }

    public function cashDocuments()
    {
        return $this->container->get('mbh.cash')->notConfirmedCashDocuments();
    }

    /**
     * @return array
     */
    public function currency()
    {
        return $this->container->get('mbh.currency')->info();
    }

    /**
     * @return ClientConfig
     */
    public function getClientConfig()
    {
        if (!$this->isClientConfigInit) {
            $this->clientConfig = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();
            $this->isClientConfigInit = true;
        }

        return $this->clientConfig;
    }

    public function getFilterBeginDate()
    {
        $now = new \DateTime("midnight");
        $config = $this->getClientConfig();
        $beginDate = $config->getBeginDate();
        if (!$beginDate || $beginDate < $now) {
            return $now;
        }

        return $beginDate;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            'mbh_format' => new \Twig_SimpleFilter('mbh_format', [$this, 'format'], ['is_safe' => ['html']]),
            'mbh_md5' => new \Twig_SimpleFilter('mbh_md5', [$this, 'md5']),
            'num2str' => new \Twig_SimpleFilter('num2str', [$this, 'num2str']),
            'num2enStr' => new \Twig_SimpleFilter('num2enStr', [$this, 'num2enStr']),
            'transToLat' => new \Twig_SimpleFilter('transToLat', [$this, 'translateToLat']),
            'convertMongoDate' => new \Twig_SimpleFilter('convertMongoDate', [$this, 'convertMongoDate']),
            'friendly_interval' => new \Twig_SimpleFilter('friendly_interval', [$this, 'friendlyInterval']),
            'initial' => new \Twig_SimpleFilter('initial', [$this, 'initial']),
        ];
    }

    public function friendlyInterval(\DateInterval $interval)
    {
        $format = [];
        if ($interval->d > 0) {
            $format[] = '%d {days}';
        }
        if ($interval->h > 0) {
            $format[] .= '%h {hours}';
        }
        if ($interval->i > 0) {
            $format[] .= '%i {minutes}';
        }
        $format = implode(' ', $format);
        $format = str_replace(['{days}', '{hours}', '{minutes}'], [
            $this->translator->trans('twig.extensiion.day_abbr'),
            $this->translator->trans('twig.extensiion.hour_abbr'),
            $this->translator->trans('twig.extensiion.minute_abbr')
        ], $format);

        return $interval->format($format);
    }

    public function initial($user)
    {
        return $user->getLastName() . ' ' .
        ($user->getFirstName() ? mb_substr($user->getFirstName(), 0, 1) . '.' : '') .
        ($user->getPatronymic() ? mb_substr($user->getPatronymic(), 0, 1) . '.' : '');
    }

    /**
     * @return \MBH\Bundle\BillingBundle\Lib\Model\Client
     */
    public function getClient()
    {
        return $this->container->get('mbh.client_manager')->getClient();
    }

    /**
     * @return bool
     */
    public function isRussianClient()
    {
        //TODO: Поменять когда создадут клиентов в биллинге
//        return $this->getClient()->getCountry() === Country::RUSSIA_TLD;
        return true;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            'currency' => new \Twig_SimpleFunction('currency', [$this, 'currency'], ['is_safe' => ['html']]),
            'user_cash' => new \Twig_SimpleFunction('user_cash', [$this, 'cashDocuments'], ['is_safe' => ['html']]),
            'client_config' => new \Twig_SimpleFunction('client_config', [$this, 'getClientConfig']),
            'filter_begin_date' => new \Twig_SimpleFunction('filter_begin_date', [$this, 'getFilterBeginDate']),
            'currentWorkShift' => new \Twig_SimpleFunction('currentWorkShift', [$this, 'currentWorkShift']),
            'mbh_timezone_offset_get' => new \Twig_SimpleFunction('mbh_timezone_offset_get', [$this, 'timezoneOffsetGet'], ['is_safe' => ['html']]),
            'get_authority_organ' => new \Twig_SimpleFunction('get_authority_organ', [$this, 'getAuthorityOrganById'], ['is_safe' => ['html']]),
            'get_country' => new \Twig_SimpleFunction('get_country', [$this, 'getCountryByTld'], ['is_safe' => ['html']]),
            'get_region' => new \Twig_SimpleFunction('get_region', [$this, 'getRegionById'], ['is_safe' => ['html']]),
            'get_city' => new \Twig_SimpleFunction('get_city', [$this, 'getCityById'], ['is_safe' => ['html']]),
            'get_client' => new \Twig_SimpleFunction('get_client', [$this, 'getClient'], ['is_safe' => ['html']]),
            'is_russian_client' => new \Twig_SimpleFunction('is_russian_client', [$this, 'isRussianClient'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @return \MBH\Bundle\UserBundle\Document\WorkShift|null
     */
    public function currentWorkShift()
    {
        $repository = $this->container->get('doctrine_mongodb')->getRepository('MBHUserBundle:WorkShift');
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        return $repository->findCurrentByUser($user);
    }

    /**
     * @return int
     */
    public function timezoneOffsetGet()
    {
        return (new \DateTime())->getOffset();
    }
}
