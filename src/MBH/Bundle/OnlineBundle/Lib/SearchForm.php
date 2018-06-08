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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class SearchForm
{
    /**
     * @var string
     * @CustomAssert\ContainsPhoneOrEmail()
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

    public function search()
    {
        /** @var Package $package */
        $package = $this->dm->getRepository('MBHPackageBundle:Package')
            ->findOneBy([
                'numberWithPrefix' => $this->getNumberOrder(),
            ]);

        if ($package === null) {
            return false;
        }

        $order = $package->getOrder();

        if (!$this->isPayer($order)) {
            return false;
        }

        if ($order->getIsPaid()) {
            return [
                'needIsPaid' => false,
                'data'       => 'order has been paid',

            ];
        }

        return [
            'needIsPaid' => true,
            'data'       => [
                'total'     => $package->getPrice() - $order->getPaid(),
                'packageId' => $package->getId(),
            ],
        ];

    }

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
            if ($this->getUserName() !== null) {
                $criteria['name'] = $this->getUserName();
            }
            $payer = $this->dm->getRepository('MBHPackageBundle:Organization')
                ->findOneBy($criteria);
            if ($payer !== null) {
                $hiIsOwner = $order->getOrganization() === $payer;
            }
        } else {
            if ($this->getUserName() !== null) {
                $criteria['lastName'] = $this->getUserName();
            }
            $tourist = $this->dm->getRepository('MBHPackageBundle:Tourist');
            if ($this->isEmail()) {
                $payer = $tourist->findOneBy($criteria);
            } else {
                /** @var Builder $qb */
                $qb = $tourist->createQueryBuilder();
                if (!empty($criteria['name'])) {
                    $qb->addAnd($qb->expr()->field('lastName')->equals($criteria['lastName']));
                }
                $qb->addOr($qb->expr()->field('mobilePhone')->equals($criteria['phone']));
                $qb->addOr($qb->expr()->field('phone')->equals($criteria['phone']));
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

    private function isEmail(): bool
    {
        return strpos($this->getPhoneOrEmail(), '@') !== false;
    }
}