<?php
/**
 * Created by PhpStorm.
 * Date: 30.08.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank;

/**
 * Class RegisterRequest
 * @package MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank
 *
 * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:register
 */
class RegisterRequest implements \JsonSerializable
{
    const URL_REGISTER = 'https://3dsec.sberbank.ru/payment/rest/register.do';

    const PAGE_VIEW_DESKTOP = 'DESKTOP';
    const PAGE_VIEW_MOBILE = 'MOBILE';

    /**
     * Логин служебной учётной записи продавца
     *
     * @var string
     */
    private $userName;

    /**
     * Пароль служебной учётной записи продавца.
     *
     * @var string
     */
    private $password;

    /**
     * Значение, которое используется для аутентификации продавца при отправке запросов в платёжный шлюз.
     * При передаче этого параметра параметры userName и pаssword передавать не нужно.
     *
     * @var string
     */
    private $token;

    /**
     * Номер (идентификатор) заказа в системе магазина,
     * уникален для каждого магазина в пределах системы - до 30 символов
     *
     * @var string
     */
    private $orderNumber;

    /**
     * Сумма платежа в минимальных единицах валюты (копейки, центы и т. п.).
     *
     * @var integer
     */
    private $amount;

    /**
     * НЕ ИСПОЛЬЗУЕТСЯ
     *
     * Код валюты платежа ISO 4217. Единственное допустимое значение - 643.
     *
     * @var integer
     */
    private $currency = 643;

    /**
     * Адрес, на который требуется перенаправить пользователя в случае успешной оплаты.
     * Адрес должен быть указан полностью, включая используемый протокол (например, https://test.ru вместо test.ru).
     * В противном случае пользователь будет перенаправлен по адресу следующего вида: http://<адрес_платёжного_шлюза>/<адрес_продавца>.
     *
     * @var null|string
     */
    private $returnUrl;

    /**
     * Адрес, на который требуется перенаправить пользователя в случае неуспешной оплаты.
     * Адрес должен быть указан полностью, включая используемый протокол (например, https://test.ru вместо test.ru).
     * В противном случае пользователь будет перенаправлен по адресу следующего вида: http://<адрес_платёжного_шлюза>/<адрес_продавца>.
     *
     * @var null|string
     */
    private $failUrl;

    /**
     * Описание заказа в свободной форме.
     * В процессинг «Сбербанка» для включения в финансовую отчётность продавца передаются только первые 24 символа этого поля.
     *
     * @var string
     */
    private $description;

    /**
     * НЕ ИСПОЛЬЗУЕТСЯ
     *
     * Язык в кодировке ISO 639-1.
     * Если не указан, будет использован язык, указанный в настройках магазина как язык по умолчанию.
     *
     * @var string
     */
    private $language;

    /**
     * По значению данного параметра определяется,
     * какие страницы платёжного интерфейса должны загружаться для клиента.
     * Возможны следующие значения.
     *  DESKTOP
     *  MOBILE
     *
     * @var string
     */
    private $pageView = self::PAGE_VIEW_DESKTOP;

    /**
     * НЕ ИСПОЛЬЗУЕТСЯ
     *
     * Номер (идентификатор) клиента в системе продавца - до 255 символов.
     * Используется для реализации функциональности связок.
     * Может присутствовать, если продавцу разрешено создание связок.
     *
     * @var string
     */
    private $clientId;

    /**
     * НЕ ИСПОЛЬЗУЕТСЯ
     *
     * Чтобы зарегистрировать заказ от имени дочернего продавца, укажите его логин в этом параметре.
     *
     * @var string
     */
    private $merchantLogin;

    /**
     * НЕ ИСПОЛЬЗУЕТСЯ
     *
     * Блок для передачи дополнительных параметров продавца.
     * Поля дополнительной информации для последующего хранения, передаются в следующем виде:
     *  {name1:value1,…,nameN:valueN}
     * Эти поля могут быть переданы в процессинг банка для последующего отображения в реестрах.
     * Включение этой функциональности возможно по согласованию с банком в период интеграции.
     *
     * @var string
     */
    private $jsonParams;

    /**
     * Продолжительность жизни заказа в секундах.
     * Если параметр не задан, будет использовано значение,
     * указанное в настройках продавца или время по умолчанию (1200 секунд = 20 минут).
     * Если в запросе присутствует параметр expirationDate, то значение параметра sessionTimeoutSecs не учитывается.
     *
     * @var integer
     */
    private $sessionTimeoutSecs;

    /**
     * НЕ ИСПОЛЬЗУЕТСЯ
     *
     * Дата и время окончания жизни заказа. Формат: yyyy-MM-ddTHH:mm:ss.
     * Если этот параметр не передаётся в запросе,
     * то для определения времени окончания жизни заказа используется sessionTimeoutSecs.
     *
     * @var string
     */
    private $expirationDate;

    /**
     * НЕ ИСПОЛЬЗУЕТСЯ
     *
     * Идентификатор созданной ранее связки.
     * Может использоваться, только если у продавца есть разрешение на работу со связками.
     *
     * @var string
     */
    private $bindingId;

    /**
     * Блок, содержащий корзину товаров заказа.
     *
     * @var null|OrderBundle
     */
    private $orderBundle;

    /**
     * Система налогообложения, доступны следующие значения:
     *
     * 0 - общая;
     * 1 - упрощённая, доход;
     * 2 - упрощённая, доход минус расход;
     * 3 - единый налог на вменённый доход;
     * 4 - единый сельскохозяйственный налог;
     * 5 - патентная система налогообложения.
     *
     * @var integer
     */
    private $taxSystem;

    /**
     * НЕ ИСПОЛЬЗУЕТСЯ
     *
     * @var string
     */
    private $features;

    /**
     * @param string $userName
     */
    public function setUserName(string $userName): void
    {
        $this->userName = $userName;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * @param string $orderNumber
     */
    public function setOrderNumber(string $orderNumber): void
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @param int $amount
     */
    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @param null|string $returnUrl
     */
    public function setReturnUrl(?string $returnUrl): void
    {
        $this->returnUrl = $returnUrl;
    }

    /**
     * @param null|string $failUrl
     */
    public function setFailUrl(?string $failUrl): void
    {
        $this->failUrl = $failUrl;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @param string $pageView
     */
    public function setPageView(string $pageView): void
    {
        $this->pageView = $pageView;
    }

    /**
     * @param int $sessionTimeoutSecs
     */
    public function setSessionTimeoutSecs(int $sessionTimeoutSecs): void
    {
        $this->sessionTimeoutSecs = $sessionTimeoutSecs;
    }

    /**
     * @param OrderBundle $orderBundle
     */
    public function setOrderBundle(OrderBundle $orderBundle): void
    {
        $this->orderBundle = $orderBundle;
    }

    /**
     * @param int $taxSystem
     */
    public function setTaxSystem(int $taxSystem): void
    {
        $this->taxSystem = $taxSystem;
    }

    public function jsonSerialize()
    {
        $data = [];

        if ($this->token === null) {
            $data['userName'] = $this->userName;
            $data['password'] = $this->password;
        } else {
            $data['token'] = $this->token;
        }

        $data['orderNumber'] = $this->orderNumber;
        $data['amount'] = $this->amount;

        if ($this->returnUrl !== null) {
            $data['returnUrl'] = $this->returnUrl;
        }

        if ($this->failUrl !== null) {
            $data['failUrl'] = $this->failUrl;
        }

        $data['description'] = $this->description;
        $data['pageView'] = $this->pageView;
        $data['sessionTimeoutSecs'] = $this->sessionTimeoutSecs;

        if ($this->orderBundle !== null) {
            $data['orderBundle'] = $this->orderBundle;
            $data['taxSystem'] = $this->taxSystem;
        }

        return $data;
    }


}