<?php

use MBH\Bundle\OnlineBundle\Validator\Constraints\PhoneOrEmail;
use MBH\Bundle\OnlineBundle\Validator\Constraints\PhoneOrEmailValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * Created by PhpStorm.
 * Date: 14.06.18
 */

class PhoneOrEmailValidatorTest extends TestCase
{
    /**
     * @var \Symfony\Component\Validator\ConstraintValidator
     */
    private $validator;

    private $context;

    /**
     * @var string
     */
    protected $root = 'root';

    protected $constraint;

    public function setUp()
    {
        // Initialize the context with some constraint so that we can
        // successfully build a violation.
        $this->constraint = new NotNull();

        $this->context = $this->createContext();
        $this->validator = $this->createValidator();
        $this->validator->initialize($this->context);
    }

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
            ['maxi@maxi'],
            ['@ya.ru'],
            ['exampleexample.co.uk'],
            ['sdv'],
        ];
    }

    protected function assertNoViolation()
    {
        $this->assertViolation(0);
    }

    protected function assertYesViolation()
    {
        $this->assertViolation(1);
    }

    private function assertViolation(int $amountViolations)
    {
        $this->assertSame(
            $amountViolations,
            $violationsCount = count($this->context->getViolations()),
            sprintf($amountViolations . ' violation expected. Got %u.', $violationsCount)
        );
    }

    private function createValidator(): ConstraintValidatorInterface
    {
        return new PhoneOrEmailValidator();
    }

    private function createContext()
    {
        $translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')->getMock();
        $validator = $this->getMockBuilder('Symfony\Component\Validator\Validator\ValidatorInterface')->getMock();

        $context = new ExecutionContext($validator, $this->root, $translator);
        $context->setConstraint($this->constraint);

        return $context;
    }
}