<?php
/**
 * Created by PhpStorm.
 * Date: 24.08.18
 */

namespace MBH\Bundle\ClientBundle\Form\PaymentSystem;


use MBH\Bundle\ClientBundle\Lib\PaymentSystemDocument;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

abstract class PaymentSystemType extends AbstractType
{
    /**
     * @return PaymentSystemDocument
     */
    abstract public static function getSourceDocument(): PaymentSystemDocument ;

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['embedded'] = true;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        /** @var PaymentSystemDocument $class */
        $class = static::getSourceDocument();

        $resolver->setDefaults([
            'data_class' => $class::className(),
        ]);
    }

    /**
     * @param array $data
     * @return array
     */
    protected function addCommonAttributes(array $data = []): array
    {
        $common = [
            'group'       => 'no-group',
            'required'    => false,
            'constraints' => [new NotBlank()],
            'attr' => [
                'data-required' => true
            ]
        ];

        return array_merge_recursive($data, $common);
    }
}