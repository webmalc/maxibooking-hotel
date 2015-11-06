<?php
namespace MBH\Bundle\BaseBundle\Twig;

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
     * @author Aleksandr Arofikin <sashaaro@gmail.com>
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
        return $this->dm->getRepository('MBHClientBundle:ClientConfig')->findOneBy([]);
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            'mbh_format' => new \Twig_Filter_Method($this, 'format', ['is_safe' => array('html')]),
            'mbh_md5' => new \Twig_Filter_Method($this, 'md5'),
            'num2str' => new \Twig_Filter_Method($this, 'num2str'),
            'num2enStr' => new \Twig_Filter_Method($this, 'num2enStr'),
            'transToLat' => new \Twig_Filter_Method($this, 'translateToLat'),
            'convertMongoDate' => new \Twig_Filter_Method($this, 'convertMongoDate'),
            'friendly_interval' => new \Twig_Filter_Method($this, 'friendlyInterval'),
            'initial' => new \Twig_Filter_Method($this, 'initial'),
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
            'currency' => new \Twig_Function_Method($this, 'currency', ['is_safe' => ['html']]),
            'user_cash' => new \Twig_Function_Method($this, 'cashDocuments', ['is_safe' => ['html']]),
            'client_config' => new \Twig_Function_Method($this, 'clientConfig'),
            'currentWorkShift' => new \Twig_Function_Method($this, 'currentWorkShift'),
            'mbh_timezone_offset_get' => new \Twig_Filter_Method($this, 'timezoneOffsetGet', ['is_safe' => ['html']]),
        ];
    }

    /**
     * @return \MBH\Bundle\UserBundle\Document\WorkShift|null
     */
    public function currentWorkShift()
    {
        $repository = $this->container->get('doctrine_mongodb')->getRepository('MBHUserBundle:WorkShift');
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        return $repository->findCurrent($user);
    }

    /**
     * @return int
     */
    public function timezoneOffsetGet()
    {
        return (new \DateTime())->getOffset();
    }
}
