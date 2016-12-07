<?php

namespace MBH\Bundle\UserBundle\Validator\Constraints;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UserValidator extends ConstraintValidator
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface 
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param object $user
     * @param Constraint $constraint
     * @return bool
     */
    public function validate($user, Constraint $constraint)
    {
        if ($user->getDefaultNoticeDoc()) {
            $dm = $this->container->get('doctrine_mongodb')->getManager();
            $old = $dm->getRepository('MBHUserBundle:User')->findOneBy(['defaultNoticeDoc' => true, 'id' => ['$ne' => $user->getId()]]);
            if ($old) {
                $this->context->addViolation($constraint->message, ['%user%' => $old]);
            }
        }

        return true;
    }

}
