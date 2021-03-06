<?php

namespace MBH\Bundle\CashBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\CashBundle\Document\CardType;

class CardTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $existingCardTypes = $manager->getRepository('MBHCashBundle:CardType')->findAll();

        foreach (CardType::getCardCodes() as $code) {
            $cardType = new CardType();
            $cardType->setCardCode($code);

            foreach (CardType::getCardCategories() as $cardCategory) {
                if (!$this->isCardTypeExists($existingCardTypes, $cardType)) {
                    $cardType = new CardType();
                    $cardType->setCardCode($code);
                    $cardType->setCardCategory($cardCategory);
                    $manager->persist($cardType);
                    $this->setReference($cardCategory . '_' . $code . '_cardType', $cardType);
                }
            }
        }

        $manager->flush();
    }

    /**
     * Проверяет, существует ли в базе данных данный тип карты
     *
     * @param $existingCardTypes
     * @param CardType $cardType
     * @return bool
     */
    private function isCardTypeExists($existingCardTypes, CardType $cardType)
    {
        foreach ($existingCardTypes as $existingCardType) {
            /** @var CardType $existingCardType */
            if ($existingCardType->getCardCode() == $cardType->getCardCode()
                && $existingCardType->getCardCategory() == $cardType->getCardCategory()) {
                return true;
            }
        }

        return false;
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