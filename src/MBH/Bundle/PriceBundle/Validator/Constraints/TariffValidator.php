<?php

namespace MBH\Bundle\PriceBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TariffValidator extends ConstraintValidator
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface 
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function validate($object, Constraint $constraint)
    {
        if($object->getBegin() >= $object->getEnd()) {
            $this->context->addViolation($constraint->beginEndMessage);
        }
        
        if (!$object->getIsDefault()) {
            return true;
        }
        
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->container->get('doctrine_mongodb')->getManager();
        $qb = $dm->getRepository('MBHPriceBundle:Tariff')->createQueryBuilder('q');

        $entities = $qb->field('id')->notEqual($object->getId())
                       ->field('isDefault')->equals(true)
                       ->field('hotel.id')->equals($object->getHotel()->getId())
                       ->addOr($qb->expr()->field('begin')->range($object->getBegin(), $object->getEnd()))
                       ->addOr($qb->expr()->field('end')->range($object->getBegin(), $object->getEnd()))
                       ->addOr(
                               $qb->expr()
                                  ->field('end')->gte($object->getEnd())
                                  ->field('begin')->lte($object->getBegin())
                        )
                       ->getQuery()
                       ->execute()
        ;

        if (count($entities)) {
            $message = '';
            foreach ($entities as $entity) {
                $message .= '<li>#' . $entity->getId() . 
                           ': ' . $entity->getName() . 
                           ' ' . $entity->getBegin()->format('d.m.Y') . 
                           ' - ' . $entity->getEnd()->format('d.m.Y') .
                           '</li>';
            }
            
            $this->context->addViolation($constraint->message, ['%tariffs%' => $message]);
        }
        
        
        return true;
    }

}
