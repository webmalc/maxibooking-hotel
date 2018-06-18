<?php

use MBH\Bundle\BaseBundle\Lib\Test\ValidatorTestCase;
use MBH\Bundle\OnlineBundle\Validator\Constraints\PhoneOrEmail;
use MBH\Bundle\OnlineBundle\Validator\Constraints\PhoneOrEmailValidator;
use Symfony\Component\Validator\ConstraintValidatorInterface;

/**
 * Created by PhpStorm.
 * Date: 14.06.18
 */

class PhoneOrEmailValidatorTest extends ValidatorTestCase
{
    /**
     * @dataProvider getValidString
     */
    public function testValidString($value)
    {
        $this->validator->validate($value, new PhoneOrEmail());

        $this->assertNoViolation();
    }

    public function getValidString(): array
    {
        return [
            ['maxi@maxi.com'],
            ['ya@ya.ru'],
            ['example@example.co.uk'],
            ['+7(123)123-456-78'],
            ['95689565sdv'],
            ['95689565@tel.com'],
        ];
    }

    /**
     * @dataProvider getInvalidString
     */
    public function testInvalidString($value)
    {
        $this->validator->validate($value, new PhoneOrEmail());

        $this->assertYesViolation();
    }

    public function getInvalidString(): array
    {
        return [
            [''],
            ['maxi@maxi'],
            ['@ya.ru'],
            ['exampleexample.co.uk'],
            ['sdv'],
        ];
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        return new PhoneOrEmailValidator();
    }
}