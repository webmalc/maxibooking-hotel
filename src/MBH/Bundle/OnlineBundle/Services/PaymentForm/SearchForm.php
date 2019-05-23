<?php
/**
 * Created by PhpStorm.
 * Date: 01.06.18
 */

namespace MBH\Bundle\OnlineBundle\Services\PaymentForm;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\OnlineBundle\Document\PaymentFormConfig;
use MBH\Bundle\OnlineBundle\Validator\Constraints as CustomAssert;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Lib\PayerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SearchForm
{
    /**
     * @var string
     * @CustomAssert\PhoneOrEmail()
     */
    private $phoneOrEmail;

    /**
     * @var string
     */
    private $numberPackage;

    /**
     * @var string|null
     */
    private $userName;

    /**
     * @var string
     */
    private $configId;

    /**
     * @var string
     */
    private $selectedHotelId;

    /**
     * @var Hotel[]|array
     */
    private $hotels;

    /**
     * @var PaymentFormConfig
     */
    private $paymentFormConfig;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var PaymentFormManager
     */
    private $paymentFormManger;

    /**
     * @var SearchFormResult
     */
    private $searchFormResult;

    public function __construct(
        DocumentManager $dm,
        PaymentFormManager $paymentFormManager,
        SearchFormResult $searchFormResult
    )
    {
        $this->dm = $dm;
        $this->paymentFormManger = $paymentFormManager;
        $this->searchFormResult = $searchFormResult;
    }

    /**
     * @return null|string
     */
    public function getPhoneOrEmail(): ?string
    {
        return $this->phoneOrEmail;
    }

    /**
     * @param null|string $phoneOrEmail
     */
    public function setPhoneOrEmail(?string $phoneOrEmail): void
    {
        $this->phoneOrEmail = trim($phoneOrEmail);
    }

    /**
     * @return null|string
     */
    public function getNumberPackage(): ?string
    {
        return $this->numberPackage;
    }

    /**
     * @param null|string $numberPackage
     */
    public function setNumberPackage(?string $numberPackage): void
    {
        $this->numberPackage = trim($numberPackage);
    }

    /**
     * @return null|string
     */
    public function getUserName(): ?string
    {
        return $this->userName;
    }

    /**
     * @param null|string $userName
     */
    public function setUserName(?string $userName): void
    {
        $this->userName = trim($userName);
    }

    /**
     * @return bool
     */
    public function isUserNameVisible(): bool
    {
        return $this->getPaymentFormConfig()->isFieldUserNameIsVisible();
    }

    private function loadPaymentFormConfig(): void
    {
        $this->paymentFormConfig = $this->paymentFormManger->findOneById($this->getConfigId());
    }

    /**
     * @return PaymentFormConfig
     */
    public function getPaymentFormConfig(): PaymentFormConfig
    {
        return $this->paymentFormConfig;
    }

    /**
     * @return string
     */
    public function getConfigId(): ?string
    {
        return $this->configId;
    }

    /**
     * @param string $configId
     */
    public function setConfigId(string $configId): void
    {
        $this->configId = $configId;

        $this->loadPaymentFormConfig();

        $this->setHotels($this->getPaymentFormConfig()->getHotels()->toArray());
    }

    /**
     * @return array|Hotel[]
     */
    public function getHotels(): array
    {
        return $this->hotels;
    }

    /**
     * @param array|Hotel[] $hotels
     */
    private function setHotels(array $hotels): void
    {
        $this->hotels = $hotels;
    }

    /**
     * @return string
     */
    public function getSelectedHotelId(): ?string
    {
        return $this->selectedHotelId;
    }

    /**
     * @param string[] $selectedHotelId
     */
    public function setSelectedHotelId(?string $selectedHotelId): void
    {
        $this->selectedHotelId = $selectedHotelId;
    }

    public function reCaptchaIsEnabled(): bool
    {
        return $this->getPaymentFormConfig()->isEnabledReCaptcha();
    }

    public function search(): SearchFormResult
    {
        /** TODO добавить логер */
        /** @var Package $package */
        $package = $this->dm->getRepository('MBHPackageBundle:Package')
            ->findOneBy([
                'numberWithPrefix' => $this->getNumberPackage(),
            ]);

        $result = $this->searchFormResult;

        if ($package === null) {
            return $result;
        }

        if ($package->getRoomType()->getHotel()->getId() !== $this->getSelectedHotelId()) {
            return $result;
        }

        $this->order = $package->getOrder();

        if ($this->order === null) {
            return $result;
        }

        if (!$this->isPayer()) {
            return $result;
        }

        $result->orderIsFound();

        if ($this->order->getIsPaid()) {
            return $result;
        }

        $result->setTotal($package->getPrice() - $this->order->getPaid());
        $result->setOrderId($this->order->getId());

        return $result;
    }

    /**
     * Проверка на существование плательщика
     *
     * @return bool
     */
    private function isPayer(): bool
    {
        /* возможно лучше другой параметр использовать и дополнительный.
           типа искать вместе или одного совпадения достаточно.
           Сейчас только вместе.
         */
        if ($this->isUserNameVisible()) {
            if (!$this->checkName()) {
                return false;
            };
        }

        if ($this->order->getOrganization() !== null) {
            return $this->checkOrganization();
        }

        return $this->checkTourist();
    }

    /**
     * @return bool
     */
    private function checkName(): bool
    {
        $name = $this->getUserName();
        $check = function ($srcName) use ($name) {
            return preg_match('@' . $name . '@iu', $srcName);
        };
        if ($this->order->getOrganization() !== null) {
            return $check($this->order->getOrganization()->getName());
        }

        return $check($this->order->getMainTourist()->getFullName());
    }

    /**
     * @return bool
     */
    private function checkOrganization(): bool
    {
        $org = $this->order->getOrganization();

        if ($this->isEmail()) {
            return $this->checkEmail($org);
        }

        return $org->getPhone() === Tourist::cleanPhone($this->getPhoneOrEmail());
    }

    /**
     * @return bool
     */
    private function checkTourist(): bool
    {
        $t = $this->order->getMainTourist();

        if ($t === null) {
            return false;
        }

        if ($this->isEmail()) {
            return $this->checkEmail($t);
        }

        $phone = $this->dexNumber(Tourist::cleanPhone($this->getPhoneOrEmail()));

        if ($this->dexNumber($t->getPhone(true)) === $phone || $this->dexNumber($t->getMobilePhone(true)) === $phone) {
            return true;
        }

        return false;
    }

    /**
     * @param PayerInterface $payer
     * @return bool
     */
    private function checkEmail(PayerInterface $payer): bool
    {
        return $payer->getEmail() === $this->getPhoneOrEmail();
    }

    /**
     * @return bool
     */
    private function isEmail(): bool
    {
        return strpos($this->getPhoneOrEmail(), '@') !== false;
    }

    /**
     * @param string $phone
     * @return string
     */
    private function dexNumber(string $phone): string
    {
        return substr($phone, -10);
    }
}
