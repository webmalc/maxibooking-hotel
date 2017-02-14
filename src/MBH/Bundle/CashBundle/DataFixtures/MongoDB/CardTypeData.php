<?php

namespace MBH\Bundle\CashBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\CashBundle\Document\CardType;

class CardTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    const cardCodes = [
        'VISA',
        'AMEX',
        'DINERS',
        'JCB',
        'JAL',
        'DELTA',
        'VISA_ELECTRON',
        'LASER',
        'CARTA_SI',
        'MASTERCARD',
        'DISCOVER',
        'CARTE_BLANCHE',
        'ENROUTE',
        'MAESTRO_UK',
        'SOLO',
        'DANKORT',
        'CARTE_BLEU',
        'MAESTRO_INTERNATIONAL',
    ];

    const CREDIT_CARD_CATEGORY = 'CREDIT';
    const DEBIT_CARD_CATEGORY = 'DEBIT';

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach (self::cardCodes as $code) {
            $cardType = new CardType();
            $cardType->setCardCode($code);
            $creditCardType = clone $cardType;

            $cardType->setCardCategory(self::DEBIT_CARD_CATEGORY);
            $creditCardType->setCardCategory(self::CREDIT_CARD_CATEGORY);

            $manager->persist($creditCardType);
            $manager->persist($cardType);
            $manager->flush();

            $this->setReference(self::CREDIT_CARD_CATEGORY. '_' . $code . '_cardType', $creditCardType);
            $this->setReference(self::DEBIT_CARD_CATEGORY. '_' . $code . '_cardType', $cardType);
        }
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 9996;
    }
}