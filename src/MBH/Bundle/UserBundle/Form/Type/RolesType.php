<?php

namespace MBH\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RolesType extends AbstractType
{
    /**
     * @var array
     */
    private $roles = [];

    public function __construct($roles)
    {
        foreach ($roles as $key => $val) {
            if (is_array($val)) {
                foreach ($val as $role) {
                    $this->roles[$key . '__GROUP'][$role] = $role;
                }
                $this->roles[$key . '__GROUP'][$key] = $key;
            } else {
                $this->roles[$key] = $val;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => $this->roles,
            'exists' => ['ROLE_ADMIN']
        ));
    }

    public function getParent()
    {
        return 'choice';
    }

    public function getName()
    {
        return 'roles';
    }
}