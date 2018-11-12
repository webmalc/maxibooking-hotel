<?php


namespace Tests\Bundle\SearchBundle\Services;


use MBH\Bundle\BaseBundle\Lib\Test\WebTestCase;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\SearchBundle\Lib\Combinations\ChildrenAgesCombination;
use MBH\Bundle\SearchBundle\Lib\Combinations\CombinationCreator;
use MBH\Bundle\SearchBundle\Lib\Combinations\CombinationInterface;
use MBH\Bundle\SearchBundle\Lib\Combinations\NoChildrenAgesCombination;
use MBH\Bundle\SearchBundle\Lib\Events\GuestCombinationEvent;
use MBH\Bundle\SearchBundle\Services\GuestCombinator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GuestCombinatorTest extends WebTestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testGetCombinations(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->exactly(5))->method('dispatch')->willReturnCallback(
            function () {
                $event = func_get_arg(1);
                /** @var GuestCombinationEvent $event */
                $tariff = $event->getTariff();
                if ($tariff->getId() === 'child_free') {
                    $event->setCombinationType(CombinationCreator::WITH_CHILDREN_AGES);
                }
                $event->setPriority(1);
            }
        );

        $reflectionTariff = new \ReflectionClass(Tariff::class);
        $tariff1 = new Tariff();
        $childTariff = new Tariff();
        $tariff3 = new Tariff();
        $tariff4 = new Tariff();
        $tariff5 = new Tariff();



        $propertyId = $reflectionTariff->getProperty('id');
        $propertyId->setAccessible(true);
        $propertyId->setValue($childTariff, 'child_free');
        $propertyId->setValue($tariff1, 'no_child1');
        $propertyId->setValue($tariff3, 'no_child3');
        $propertyId->setValue($tariff4, 'no_child4');
        $propertyId->setValue($tariff5, 'no_child5');
        $tariffs = [
            $tariff1,
            $childTariff,
            $tariff3,
            $tariff4,
            $tariff5,
        ];

        $combinationCreator = $this->createMock(CombinationCreator::class);
        $combinationCreator->expects($this->exactly(2))->method('getCombinationType')->willReturnMap([
            [CombinationCreator::NO_CHILDREN_AGES , new NoChildrenAgesCombination()],
            [CombinationCreator::WITH_CHILDREN_AGES, new ChildrenAgesCombination()]

        ]);


        $combinator = new GuestCombinator($dispatcher, $combinationCreator);
        $actual = $combinator->getCombinations($tariffs);
        $this->assertContainsOnlyInstancesOf(CombinationInterface::class, $actual);
        foreach ($actual as $combinationType) {
            if ($combinationType instanceof NoChildrenAgesCombination) {
                $this->assertCount(4, $combinationType->getTariffIds());
                $this->assertEquals(['no_child1', 'no_child3', 'no_child4', 'no_child5'], $combinationType->getTariffIds());
            }
            if ($combinationType instanceof ChildrenAgesCombination) {
                $this->assertCount(1, $combinationType->getTariffIds());
                $this->assertEquals(['child_free'], $combinationType->getTariffIds());
            }
        }
    }
}