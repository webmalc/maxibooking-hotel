<?php

/**
 * Created by PhpStorm.
 * Date: 15.06.18
 */


use MBH\Bundle\BaseBundle\Lib\Test\ValidatorTestCase;
use MBH\Bundle\BaseBundle\Validator\Constraints\RangeValidator;
use Symfony\Component\Validator\ConstraintValidatorInterface;

class RangeValidatorTest extends ValidatorTestCase
{
    protected function createValidator(): ConstraintValidatorInterface
    {
        return new RangeValidator();
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testException()
    {
        $this->validator->validate(
            'test',
            new \Symfony\Component\Validator\Constraints\Range(['min'=> 1, 'max' => 2])
        );
    }

    public function testNullIsValid()
    {
        $this->validator->validate(
            new \MBH\Bundle\PackageBundle\Document\Package(),
            new \MBH\Bundle\BaseBundle\Validator\Constraints\Range()
        );

        $this->assertNoViolation();
    }

    /**
     * @param $package
     */
    public function testInvalidValue()
    {
        $package = new \MBH\Bundle\PackageBundle\Document\Package();
        $package->setBegin(new DateTime('+10 days'));
        $package->setEnd(new DateTime('-10 days'));

        $this->validator->validate(
            $package,
            new \MBH\Bundle\BaseBundle\Validator\Constraints\Range()
        );

        $this->assertYesViolation();
    }

    public function testValidValue()
    {
        $package = new \MBH\Bundle\PackageBundle\Document\Package();
        $package->setBegin(new DateTime('-10 days'));
        $package->setEnd(new DateTime('+10 days'));


        $this->validator->validate(
            $package,
            new \MBH\Bundle\BaseBundle\Validator\Constraints\Range()
        );

        $this->assertNoViolation();
    }
}