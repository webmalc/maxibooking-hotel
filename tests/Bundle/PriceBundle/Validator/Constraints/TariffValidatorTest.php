<?php

use MBH\Bundle\PriceBundle\Validator\Constraints\Tariff;

/**
 * Created by PhpStorm.
 * Date: 18.06.18
 */

class TariffValidatorTest extends \MBH\Bundle\BaseBundle\Lib\Test\ValidatorTestCase
{
    protected function createValidator(): \Symfony\Component\Validator\ConstraintValidatorInterface
    {
        return new \MBH\Bundle\PriceBundle\Validator\Constraints\TariffValidator();
    }

    public function testNullIsValid()
    {
        $this->validator->validate(new \MBH\Bundle\PriceBundle\Document\Tariff(), new Tariff());

        $this->assertNoViolation();
    }

    public function getInvalidData(): array
    {
        $tariff_1 = new \MBH\Bundle\PriceBundle\Document\Tariff();

        $tariff_1->setInfantAge(10);
        $tariff_1->setChildAge(5);

        $tariff_2 = new \MBH\Bundle\PriceBundle\Document\Tariff();

        $tariff_2->setBegin(new DateTime());
        $tariff_2->setEnd(new DateTime('-10 days'));

        return [
            [$tariff_1],
            [$tariff_2],
        ];
    }

    /**
     * @param $data
     * @dataProvider getInvalidData
     */
    public function testInvalidData($data)
    {
        $this->validator->validate($data, new Tariff());

        $this->assertYesViolation();
    }


    public function testValidData()
    {
        $tariff = new \MBH\Bundle\PriceBundle\Document\Tariff();
        $tariff->setBegin(new DateTime('-10 days'));
        $tariff->setEnd(new DateTime());

        $this->validator->validate($tariff, new Tariff());

        $this->assertNoViolation();
    }
}