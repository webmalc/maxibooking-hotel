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

    public function getInvalidData(): array
    {
        $tariff_1 = new \MBH\Bundle\PriceBundle\Document\Tariff();

        $tariff_2 = new \MBH\Bundle\PriceBundle\Document\Tariff();

        $tariff_2->setInfantAge(10);
        $tariff_2->setChildAge(5);

        $tariff_3 = new \MBH\Bundle\PriceBundle\Document\Tariff();

        $tariff_3->setBegin(new DateTime());
        $tariff_3->setEnd(new DateTime('-10 days'));

        return [
            [['tariff' => $tariff_1, 'amountViolation' => 1]],
            [['tariff' => $tariff_2, 'amountViolation' => 2]],
            [['tariff' => $tariff_3, 'amountViolation' => 1]],
        ];
    }

    /**
     * @param $data
     * @dataProvider getInvalidData
     */
    public function testInvalidData($data)
    {
        $this->validator->validate($data['tariff'], new Tariff());

        $this->assertViolation($data['amountViolation']);
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