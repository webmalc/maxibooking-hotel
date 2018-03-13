<?php
namespace MBH\Bundle\BaseBundle\Twig;

use MBH\Bundle\ClientBundle\Document\ClientConfig;
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

        return $date->format('d') . " <span class='date-month'> " . $months[$date->format('n') - 1] . '.</span>';
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
     * @param \MongoDate $mongoDate
     * @return \DateTime
     *

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
     * @return array
     */
    public function clientConfig()
    {
        return $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();
    }

    public function getFilterBeginDate()
    {
        /** @var  ClientConfig $config */
        $now = new \DateTime("midnight");
        $config = $this->clientConfig();
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
            'mbh_format' => new \Twig_SimpleFilter('mbh_format', [$this, 'format'], ['is_safe' => array('html')]),
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
        $format = str_replace(['{days}', '{hours}', '{minutes}'], ['д.', 'ч.', 'мин.'], $format);
        return $interval->format($format);
    }

    public function initial($user)
    {
        return $user->getLastName() . ' ' .
        ($user->getFirstName() ? mb_substr($user->getFirstName(), 0, 1) . '.' : '') .
        ($user->getPatronymic() ? mb_substr($user->getPatronymic(), 0, 1) . '.' : '');
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            'currency' => new \Twig_SimpleFunction('currency', [$this, 'currency'], ['is_safe' => ['html']]),
            'user_cash' => new \Twig_SimpleFunction('user_cash', [$this, 'cashDocuments'], ['is_safe' => ['html']]),
            'client_config' => new \Twig_SimpleFunction('client_config', [$this, 'clientConfig']),
            'filter_begin_date' => new \Twig_SimpleFunction('filter_begin_date', [$this, 'getFilterBeginDate']),
            'currentWorkShift' => new \Twig_SimpleFunction('currentWorkShift', [$this, 'currentWorkShift']),
            'mbh_timezone_offset_get' => new \Twig_SimpleFunction('mbh_timezone_offset_get', [$this, 'timezoneOffsetGet'], ['is_safe' => ['html']]),
            'get_current_hotel' => new \Twig_SimpleFunction('get_current_hotel', [$this, 'getCurrentHotel'], ['is_safe' => ['html']]),
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

    /**
     * @return \MBH\Bundle\HotelBundle\Document\Hotel|null
     */
    public function getCurrentHotel()
    {
        return $this->container->get('mbh.hotel.selector')->getSelected();
    }
}
