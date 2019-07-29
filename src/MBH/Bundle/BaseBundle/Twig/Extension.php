<?php
namespace MBH\Bundle\BaseBundle\Twig;

use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Service\CurrencySymbol;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\BillingBundle\Lib\Model\Country;
use MBH\Bundle\ClientBundle\Service\DocumentSerialize\Mortal;
use MBH\Bundle\ClientBundle\Service\DocumentSerialize\Organization;
use MBH\Bundle\PackageBundle\Lib\AddressInterface;
use MBH\Bundle\UserBundle\DataFixtures\MongoDB\UserData;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;

class Extension extends \Twig_Extension
{
    /**
     * @var bool|null
     */
    private $cacheIsRussianClient;

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
    private $twigData;
    private $isTwigDataInit = false;

    /**
     * @var array
     */
    private $months = [];

    /**
     * @var bool
     */
    private $isInitMonths = false;

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

        $this->initMonths();

        $text = sprintf(
            '%s&nbsp;<span class=\'date-month\'>%s</span>.',
            $date->format('d'),
            $this->months[$date->format('n') - 1]
        );

        return sprintf(
            '%s&nbsp;<span class=\'date-month\'>%s</span>.',
            $date->format('d'),
            $this->months[$date->format('n') - 1]
        );
    }

    private function initMonths(): void
    {
        if (!$this->isInitMonths) {
            $this->months = [
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

            $this->isInitMonths = true;
        }
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
     * if there are more than one CashDocuments - returns first CashDocument's total
     *
     * @param \MBH\Bundle\ClientBundle\Service\DocumentSerialize\Order $order
     * @return string
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function getCashPrepayment(\MBH\Bundle\ClientBundle\Service\DocumentSerialize\Order $order): string
    {
        $prepaymentPrice = $this->container->get('mbh.template_prices_generator')->getPrepayment($order);

        return $prepaymentPrice === null ? '0.00' : (string)$prepaymentPrice;
    }

    public function getCashSurcharge(\MBH\Bundle\ClientBundle\Service\DocumentSerialize\Order $order): string
    {
        $surchargePrice = $this->container->get('mbh.template_prices_generator')->getSurcharge($order);

        return $surchargePrice === null ? '0.00' : (string)$surchargePrice;
    }

    /**
     * @param \MBH\Bundle\ClientBundle\Service\DocumentSerialize\Order $order
     * @return string
     */
    public function getCashPrice(\MBH\Bundle\ClientBundle\Service\DocumentSerialize\Order $order): string
    {
        $price = $this->container->get('mbh.template_prices_generator')->getPriceByMethod(
            $order,
            [CashDocument::METHOD_CASH]
        );

        return $price === null ? '0.00' : (string)$price;
    }

    /**
     * @param \MBH\Bundle\ClientBundle\Service\DocumentSerialize\Order $order
     * @return string
     */
    public function getCashlessPrice(\MBH\Bundle\ClientBundle\Service\DocumentSerialize\Order $order): string
    {
        $price = $this->container->get('mbh.template_prices_generator')->getPriceByMethod(
            $order,
            [CashDocument::METHOD_ELECTRONIC, CashDocument::METHOD_CASHLESS]
        );

        return $price === null ? '0.00' : (string)$price;
    }

    /**
     * @param $serviceId
     * @return string
     */
    public function getBillingService($serviceId)
    {
        return $this->container->get('mbh.billing.api')->getServiceById($serviceId);
    }

    /**
     * @param $authorityOrganId
     * @return \MBH\Bundle\BillingBundle\Lib\Model\AuthorityOrgan
     */
    public function getAuthorityOrganById($authorityOrganId)
    {
        return $this->container->get('mbh.billing.api')->getAuthorityOrganById($authorityOrganId);
    }

    /**
     * @param $countryTld
     * @param null $locale
     * @return \MBH\Bundle\BillingBundle\Lib\Model\Country
     */
    public function getCountryByTld($countryTld, $locale = null)
    {
        return $this->container->get('mbh.billing.api')->getCountryByTld($countryTld, $locale);
    }

    /**
     * @param $regionId
     * @param null $locale
     * @return \MBH\Bundle\BillingBundle\Lib\Model\Region
     */
    public function getRegionById($regionId, $locale = null)
    {
        return $this->container->get('mbh.billing.api')->getRegionById($regionId, $locale);
    }

    /**
     * @param $cityId
     * @param null $locale
     * @return \MBH\Bundle\BillingBundle\Lib\Model\City
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

    public function currencySymbolWithPrice(string $price, string $wrapperId = null, string $wrapperTag = 'span'): string
    {
        return $this->container->get(CurrencySymbol::class)->symbolWithPrice($price, $wrapperId, $wrapperTag);
    }

    public function resultNumberFormat(float $price): string
    {
        $decimals = 1;

        if (ceil($price) === $price) {
            $decimals = 0;
        }

        return number_format($price, $decimals, '.', ' ');
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

    /**
     * @return \MBH\Bundle\HotelBundle\Document\Hotel|null
     */
    public function getCurrentHotel()
    {
        return $this->container->get('mbh.hotel.selector')->getSelected();
    }

    public function stringToDate($dateString, $dateFormat = 'd.m.Y')
    {
        return \DateTime::createFromFormat($dateFormat, $dateString);
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
     * Заменяет больше двух побелов одним
     *
     * @param string $str
     * @return string
     */
    public function clearAdjacentWhitespace(string $str): string
    {
        return preg_replace('/\s{2,}/',' ', $str);
    }

    public function removeNonPrintableCharacters(string $str): string
    {
        return str_replace(['&nbsp;', '&ensp;', '&emsp;', '&shy;'], '', $str);
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
            'str_to_date' => new \Twig_SimpleFilter('str_to_date', [$this, 'stringToDate']),
            'clear_adjacent_whitespace' => new \Twig_SimpleFilter('clear_adjacent_whitespace', [$this, 'clearAdjacentWhitespace']),
            'result_number_format' => new TwigFilter('result_number_format', [$this, 'resultNumberFormat']),
            'remove_non_printable_characters' => new TwigFilter('remove_non_printable_characters', [$this, 'removeNonPrintableCharacters'])
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
    public function isRussianClient(): bool
    {
        if ($this->cacheIsRussianClient === null) {
            $this->cacheIsRussianClient = $this->getClient()->getCountry() === Country::RUSSIA_TLD;
        }

        return $this->cacheIsRussianClient;
    }

    /**
     * @return string
     */
    public function getSettingsDataForFrontend()
    {
        $session = $this->container->get('session');
        $language = $session->get('_locale')
            ? $session->get('_locale')
            : $this->container->getParameter('locale');

        $data = [
            'allowed_guides' => $this->container->get('mbh.guides_data_service')->getAllowedGuides(),
            'client_country' => $this->getClient()->getCountry(),
            'front_token'    => $this->container->getParameter('billing_front_token'),
            'billing_host'   => $this->container->getParameter('billing_url') . '/',
            'behavior_menu'  => $this->container->getParameter('mbh.menu.behaviors.now'),
            'language' => $language
        ];

        return json_encode($data);
    }

    public function getTwigData()
    {
        if (!$this->isTwigDataInit) {
            $supportData = $this->container->getParameter('support');
            $supportEmail = $this->isRussianClient()
                ? $supportData['clients_support_email_ru']
                : $supportData['clients_support_email_com'];
            $supportMainEmail = $supportData['support_main_email'][$this->container->getParameter('locale')];

            $this->twigData = [
                'demo_user_token' => UserData::SANDBOX_USER_TOKEN,
                'clients_support_email' => $supportEmail,
                'support_phone' => $supportData['russian_support_phone'],
                'support_main_email' => $supportMainEmail,
                'locale' => $this->container->getParameter('locale')
            ];

            $this->isTwigDataInit = true;
        }

        return $this->twigData;
    }

    /**
     * @param Base $document
     * @param string $fieldName
     * @return string
     */
    public function getFieldTitleByName(Base $document, string $fieldName)
    {
        return $this->container->get('mbh.document_fields_manager')->getFieldName(get_class($document), $fieldName);
    }

    /**
     * @param AddressInterface $obj
     * @return string
     */
    public function getImperialAddressCity(AddressInterface $obj): string
    {
        $address = $this->container->get('mbh.address');
        return $address->getImperialCityStr($obj);
    }

    /**
     * @param AddressInterface $obj
     * @return string
     */
    public function getImperialAddressStreet(AddressInterface $obj): string
    {
        $address = $this->container->get('mbh.address');
        return $address->getImperialStreetStr($obj);
    }

    /**
     * @return bool
     */
    public function isMBUser()
    {
        /** @var User $user */
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        return $user instanceof User && $user->getUsername() === UserData::MB_USER_USERNAME;
    }

    public function getMethodsForTemplate(): string
    {
        return json_encode($this->container->get('MBH\Bundle\ClientBundle\Service\DocumentSerialize\Helper')->methodsOfEntity());
    }

    /**
     * @param string|null $type
     * @param string|null $name
     * @return string
     */
    public function getGuideArticleUrl(string $type = null, string $name = null)
    {
        $url = 'https://support.maxi-booking.com/hc/' . ($this->isRussianClient() ? 'ru' : 'com' );
        if (!is_null($type) && !is_null($name)) {
            $articlesByTypes = $this->container->getParameter('guides_site')['articles'];
            if (!isset($articlesByTypes[$type])) {
                throw new \InvalidArgumentException('Incorrect type of articles:' . $type);
            }
            if (!isset($articlesByTypes[$type][$name])) {
                throw new \InvalidArgumentException('Incorrect name of article:' . $name);
            }

            $url .= '/articles/' . $articlesByTypes[$type][$name];
        }

        return $url;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        $options = ['is_safe' => ['html']];

        return [
            'currency'                   => new TwigFunction('currency', [$this, 'currency'], $options),
            'currency_symbol_with_price' => new TwigFunction('currency_symbol_with_price', [$this, 'currencySymbolWithPrice'], $options),
            'user_cash'                  => new TwigFunction('user_cash', [$this, 'cashDocuments'], $options),
            'client_config'              => new TwigFunction('client_config', [$this, 'getClientConfig']),
            'filter_begin_date'          => new TwigFunction('filter_begin_date', [$this, 'getFilterBeginDate']),
            'currentWorkShift'           => new TwigFunction('currentWorkShift', [$this, 'currentWorkShift']),
            'mbh_timezone_offset_get'    => new TwigFunction('mbh_timezone_offset_get', [$this, 'timezoneOffsetGet'], $options),
            'get_authority_organ'        => new TwigFunction('get_authority_organ', [$this, 'getAuthorityOrganById'], $options),
            'get_country'                => new TwigFunction('get_country', [$this, 'getCountryByTld'], $options),
            'get_region'                 => new TwigFunction('get_region', [$this, 'getRegionById'], $options),
            'get_city'                   => new TwigFunction('get_city', [$this, 'getCityById'], $options),
            'get_client'                 => new TwigFunction('get_client', [$this, 'getClient'], $options),
            'is_russian_client'          => new TwigFunction('is_russian_client', [$this, 'isRussianClient'], $options),
            'get_service'                => new TwigFunction('get_service', [$this, 'getBillingService'], $options),
            'get_current_hotel'          => new TwigFunction('get_current_hotel', [$this, 'getCurrentHotel'], $options),
            'get_front_settings'         => new TwigFunction('get_front_settings', [$this, 'getSettingsDataForFrontend'], $options),
            'get_imperial_city'          => new TwigFunction('get_imperial_city', [$this, 'getImperialAddressCity'], $options),
            'get_imperial_street'        => new TwigFunction('get_imperial_street', [$this, 'getImperialAddressStreet'], $options),
            'get_twig_data'              => new TwigFunction('get_twig_data', [$this, 'getTwigData'], $options),
            'get_field_name'             => new TwigFunction('get_field_name', [$this, 'getFieldTitleByName'], $options),
            'is_mb_user'                 => new TwigFunction('is_mb_user', [$this, 'isMBUser'], $options),
            'get_properties'             => new TwigFunction('get_properties', [$this, 'getMethodsForTemplate'], $options),
            'get_guide_article_url'      => new TwigFunction('get_guide_article_url', [$this, 'getGuideArticleUrl'], $options),
            'get_cash_price'             => new TwigFunction('get_cash_price', [$this, 'getCashPrice'], $options),
            'get_cashless_price'         => new TwigFunction('get_cashless_price', [$this, 'getCashlessPrice'], $options),
            'get_prepayment_price'       => new TwigFunction('get_prepayment_price', [$this, 'getCashPrepayment'], $options),
            'get_surcharge_price'        => new TwigFunction('get_surcharge_price', [$this, 'getCashSurcharge'], $options),
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

    public function getTokenParsers()
    {
        return [
            'wrapinline' => new TwigWrapInLineTokenParser(),
            'escapebackslash' => new TwigBackslashEscapeTokenParser()
        ];
    }

    public function getTests()
    {
        return [
            'instanceofMortal'       => new \Twig_SimpleTest('instanceofMortal', [$this, 'isInstanceofMortal']),
            'instanceofOrganization' => new \Twig_SimpleTest('instanceofOrganization', [$this, 'isInstanceofOrganization']),
        ];
    }

    /**
     * @return bool
     */
    public function isInstanceofMortal($obj): bool
    {
        return $obj instanceof Mortal;
    }

    /**
     * @return bool
     */
    public function isInstanceofOrganization($obj): bool
    {
        return $obj instanceof Organization;
    }
}
