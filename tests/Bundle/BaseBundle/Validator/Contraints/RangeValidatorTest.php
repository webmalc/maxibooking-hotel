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

    public function getInvalidValue(): array
    {
        $package1 = $this->getMockBuilder(\MBH\Bundle\PackageBundle\Document\Package::class)
            ->getMock();

        $package2 = $this->getMockBuilder(\MBH\Bundle\PackageBundle\Document\Package::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBegin','getEnd'])
            ->getMock();
        $package2
            ->expects($this->any())
            ->method('getEnd')
            ->will($this->returnValue(new DateTime()));

        $package3 = $this->getMockBuilder(\MBH\Bundle\PackageBundle\Document\Package::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBegin','getEnd'])
            ->getMock();
        $package3
            ->expects($this->any())
            ->method('getBegin')
            ->will($this->returnValue(new DateTime('+10 days')));
        $package3
            ->expects($this->any())
            ->method('getEnd')
            ->will($this->returnValue(new DateTime('-10 days')));

        return [
            [$package1],
            [$package2],
            [$package3],
        ];
    }

    /**
     * @param $package
     * @dataProvider getInvalidValue
     */
    public function testInvalidValue($package)
    {
        $this->validator->validate(
            $package,
            new \MBH\Bundle\BaseBundle\Validator\Constraints\Range()
        );

        $this->assertYesViolation();
    }

    public function testValidValue()
    {
        $package = $this->getMockBuilder(\MBH\Bundle\PackageBundle\Document\Package::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBegin','getEnd'])
            ->getMock();

        $package->expects($this->any())
            ->method('getBegin')
            ->will($this->returnValue(new DateTime('-10 days')));
        $package->expects($this->any())
            ->method('getEnd')
            ->will($this->returnValue(new DateTime('+10 days')));


        $this->validator->validate(
            $package,
            new \MBH\Bundle\BaseBundle\Validator\Constraints\Range()
        );

        $this->assertNoViolation();
    }
}