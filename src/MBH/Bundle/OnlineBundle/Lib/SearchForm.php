<?php
/**
 * Created by PhpStorm.
 * Date: 01.06.18
 */

namespace MBH\Bundle\OnlineBundle\Lib;


use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use MBH\Bundle\OnlineBundle\Document\PaymentFormConfig;
use MBH\Bundle\OnlineBundle\Validator\Constraints as CustomAssert;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

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
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var DocumentManager
     */
    private $dm;

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
     * @return null|string
     */
    public function getNumberOrder(): ?string
    {
        return $this->numberOrder;
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
        /** @var PaymentFormConfig $config */
        $config = $this->dm->getRepository('MBHOnlineBundle:PaymentFormConfig')
            ->find($this->getConfigId());

        return $config->isFieldUserNameIsVisible();
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
    }

    public function reCaptchaIsEnabled(): bool
    {
        /** @var PaymentFormConfig $config */
        $config = $this->dm->getRepository('MBHOnlineBundle:PaymentFormConfig')
            ->find($this->getConfigId());
        if ($config === null) {
            $logger = $this->container->get('logger');
            $logger->addError('not found id for PaymentFormConfig');

            return true;
        }

        return $config->isEnabledReCaptcha();
    }

    public function search(): SearchFormResult
    {
        /** @var Package $package */
        $package = $this->dm->getRepository('MBHPackageBundle:Package')
            ->findOneBy([
                'numberWithPrefix' => $this->getNumberOrder(),
            ]);

        $result = new SearchFormResult($this->container);

        if ($package === null) {
            return $result;
        }

        $order = $package->getOrder();

        if (!$this->isPayer($order)) {
            return $result;
        }

        $result->orderIsFound();

        if ($order->getIsPaid()) {
            return $result;
        }

        $result->setTotal($package->getPrice() - $order->getPaid());
        $result->setOrderId($order->getId());

        return $result;

    }

    /**
     * Проверка на существование плательщика
     *
     * @param Order $order
     * @return bool
     */
    private function isPayer(Order $order): bool
    {
        $criteria = [];

        if ($this->isEmail()) {
            $criteria['email'] = $this->getPhoneOrEmail();
        } else {
            $criteria['phone'] = $this->getPhoneOrEmail();
        }

        $payer = null;

        if ($order->getOrganization() !== null) {
            $this->addCriteriaNaming($criteria);
            $payer = $this->dm->getRepository('MBHPackageBundle:Organization')
                ->findOneBy($criteria);
            if ($payer !== null) {
                $hiIsOwner = $order->getOrganization() === $payer;
            }
        } else {
            $this->addCriteriaNaming($criteria, false);
            $tourist = $this->dm->getRepository('MBHPackageBundle:Tourist');
            if ($this->isEmail()) {
                $payer = $tourist->findOneBy($criteria);
            } else {
                /** @var Builder $qb */
                $qb = $tourist->createQueryBuilder();
                if ($this->isNameNeed($criteria)) {
                    $qb->addAnd($qb->expr()->field('lastName')->equals($criteria['lastName']));
                }
                $phone = Tourist::cleanPhone($criteria['phone']);
                $qb->addOr($qb->expr()->field('mobilePhone')->equals($phone));
                $qb->addOr($qb->expr()->field('phone')->equals($phone));
                $payer = $qb->getQuery()->getSingleResult();
            }
            if ($payer !== null) {
                $hiIsOwner = $order->getMainTourist() === $payer;
            }
        }

        if ($payer === null || !$hiIsOwner) {
            return false;
        }

        return true;
    }

    /**
     * @param array $criteria
     * @return bool
     */
    private function isNameNeed(array $criteria): bool
    {
        return !empty($criteria['lastName']);
    }

    /**
     * @param $criteria
     * @param bool $isOrganization
     */
    private function addCriteriaNaming(&$criteria ,bool $isOrganization = true): void
    {
        if ($this->getUserName() !== null) {
            $c = ['$regex' => $this->getUserName(), '$options' => 'i'];
            if ($isOrganization) {
                $criteria['name'] = $c;
            } else {
                $criteria['lastName'] = $c;
            }
        }
    }

    /**
     * @return bool
     */
    private function isEmail(): bool
    {
        return strpos($this->getPhoneOrEmail(), '@') !== false;
    }
}