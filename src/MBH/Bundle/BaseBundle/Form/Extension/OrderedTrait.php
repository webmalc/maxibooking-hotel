<?php

namespace MBH\Bundle\BaseBundle\Form\Extension;


use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Class OrderedTrait
 * @link http://stackoverflow.com/questions/24484581/symfony2-order-form-fields
 */
trait OrderedTrait
{
    abstract function getFieldsOrder();

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /** @var FormView[] $fields */
        $fields = [];
        foreach ($this->getFieldsOrder() as $field) {
            if ($view->offsetExists($field)) {
                $fields[$field] = $view->offsetGet($field);
                $view->offsetUnset($field);
            }
        }

        $view->children = $fields + $view->children;

    }
}