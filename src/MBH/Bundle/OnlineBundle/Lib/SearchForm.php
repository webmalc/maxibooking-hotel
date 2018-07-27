<?php
/**
 * Created by PhpStorm.
 * Date: 01.06.18
 */

namespace MBH\Bundle\OnlineBundle\Lib;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\OnlineBundle\Document\PaymentFormConfig;
use MBH\Bundle\OnlineBundle\Validator\Constraints as CustomAssert;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Lib\PayerInterface;
use MBH\Bundle\PackageBundle\Services\OrderManager;
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
    private $numberOrder;

    /**
     * @var string|null
     */
    private $userName;

    /**
     * @var string
     */
    private $configId;

    /**
     * @var PaymentFormConfig | null
     */
    private $config = null;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var Order
     */
    private $order;

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
        if ($container !== null) {
            $this->dm = $container->get('doctrine.odm.mongodb.document_manager');
        }
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
        $this->phoneOrEmail = $phoneOrEmail;
    }

    /**
     * @return string
     */
    public function getNumberOrder(): string
    {
        return trim($this->numberOrder);
    }

    /**
     * @param null|string $numberOrder
     */
    public function setNumberOrder(?string $numberOrder): void
    {
        $this->numberOrder = $numberOrder;
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
        $this->userName = $userName;
    }

    /**
     * @return bool
     */
    public function isUserNameVisible(): bool
    {
        return $this->getConfig()->isFieldUserNameIsVisible();
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
    public function setConfigId(string $configId): bool
    {
        $this->configId = $configId;

        return $this->initConfig();
    }

    public function reCaptchaIsEnabled(): bool
    {
        return $this->getConfig()->isEnabledReCaptcha();
    }

    public function search(): SearchFormResult
    {
        /** @var Package $package */
        $package = $this->dm->getRepository('MBHPackageBundle:Package')
            ->findOneBy([
                'numberWithPrefix' => $this->getNumberOrder(),
            ]);

        $result = new SearchFormResult($this->container);
        $result->setSearchForm($this);

        if ($package === null) {

            $package = $this->dm->getRepository('MBHPackageBundle:Package')
                ->findOneBy([
                    'numberWithPrefix' => $this->getNumberOrder() . OrderManager::RELOCATE_SUFFIX,
                ]);

            if ($package === null) {
                return $result;
            }
        }

        $this->order = $package->getOrder();

        if ($this->order === null) {
            return $result;
        }

        if (!$this->isPayer()) {
            return $result;
        }

        $result->orderIsFound();

        if ($this->getConfig()->isEnabledMaxAmountLimit() && $this->order->getIsPaid()) {
            return $result;
        }

        $result->setTotal($package->getPrice() - $this->order->getPaid());
        $result->setPackageId($package->getId());

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
        } else {
            return $check($this->order->getMainTourist()->getFullName());
        }
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
    public function initConfig(PaymentFormConfig $config = null): bool
    {
        if ($config === null) {
            $config = $this->dm->getRepository('MBHOnlineBundle:PaymentFormConfig')
                ->find($this->getConfigId());

            if ($config === null) {
                $logger = $this->container->get('logger');
                $logger->addError('not found id for PaymentFormConfig');

                return false;
            }
        }
        $this->configId = $config->getId();
        $this->config = $config;

        return true;
    }

    /**
     * @return PaymentFormConfig
     */
    private function getConfig(): PaymentFormConfig
    {
        return $this->config;
    }

    /**
     * @return bool
     */
    private function checkTourist(): bool
    {
        $t = $this->order->getMainTourist();

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