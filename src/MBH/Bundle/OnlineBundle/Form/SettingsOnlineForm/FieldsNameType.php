<?php
/**
 * Date: 13.03.19
 */

namespace MBH\Bundle\OnlineBundle\Form\SettingsOnlineForm;


use MBH\Bundle\BaseBundle\Form\FormWithMultiLangFields;
use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FieldsName;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;


class FieldsNameType extends FormWithMultiLangFields
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach (array_keys(FieldsName::MAP_FIELDS) as $property) {
            $this->addMultiLangField($builder, TextType::class, $property, [
                'group'    => 'views.form.index.settings.fields_name.group',
                'required' => false,
                'label'    => sprintf('views.api.form.%s.label', FieldsName::MAP_FIELDS[$property]),
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FieldsName::class,
            'translation_domain' => 'MBHOnlineBundle'
        ]);
    }


}