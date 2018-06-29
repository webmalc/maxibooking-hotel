<?php
/**
 * Created by PhpStorm.
 * Date: 15.06.18
 */

namespace MBH\Bundle\BaseBundle\Lib\Test;


use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;

abstract class ValidatorTestCase extends KernelTestCase
{
    /**
     * @var \Symfony\Component\Validator\ConstraintValidator
     */
    protected $validator;

    protected $context;

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

    protected function assertNoViolation()
    {
        $this->assertViolation(0);
    }

    protected function assertYesViolation()
    {
        $this->assertViolation(1);
    }

    protected function assertViolation(int $amountViolations)
    {
        $this->assertSame(
            $amountViolations,
            $violationsCount = count($this->context->getViolations()),
            sprintf($amountViolations . ' violation expected. Got %u.', $violationsCount)
        );
    }

    private function createContext()
    {
        $translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')->getMock();
        $validator = $this->getMockBuilder('Symfony\Component\Validator\Validator\ValidatorInterface')->getMock();

        $context = new ExecutionContext($validator, $this->root, $translator);
        $context->setConstraint($this->constraint);

        return $context;
    }

    abstract protected function createValidator(): ConstraintValidatorInterface;

}