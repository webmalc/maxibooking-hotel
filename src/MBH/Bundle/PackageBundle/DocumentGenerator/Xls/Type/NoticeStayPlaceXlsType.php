<?php

namespace MBH\Bundle\PackageBundle\DocumentGenerator\Xls\Type;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\DataTransformer\EntityToIdTransformer;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class NoticeStayPlaceXlsType
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class NoticeStayPlaceXlsType extends AbstractType
{
    /**
     * @var DocumentManager
     */
    private $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $tourists = [];

        /** @var Tourist $tourist */
        foreach($options['tourists'] as $tourist) {
            if($tourist) {
                $citizenship = $tourist->getCitizenship();
                if($citizenship === null || ($citizenship && $citizenship->getName() != "Россия")) {
                    $tourists[$tourist->getId()] = $tourist->getFullName() . ' (' . ($citizenship ? $citizenship->getName() : 'Не указано') . ')';
                }
            }
        }
        $builder->add('tourist', 'choice', [
            'required' => true,
            'label' => 'form.task.tourist',
            'choices' => $tourists,
            'attr' => ['style' => 'width:250px'],
            'label_attr' => ['class' => 'col-md-4'],
        ]);
        $builder->get('tourist')->addModelTransformer(new EntityToIdTransformer($this->dm, 'MBH\Bundle\PackageBundle\Document\Tourist'));
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'tourists' => []
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'notice_stay_place_xls';
    }
}