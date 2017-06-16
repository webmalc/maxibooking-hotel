<?php

namespace MBH\Bundle\PackageBundle\DocumentGenerator\Xls\Type;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\DataTransformer\EntityToIdTransformer;
use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\DataCollectorTranslator;

/**
 * Class NoticeStayPlaceXlsType

 */
class NoticeStayPlaceXlsType extends AbstractType
{
    private $dm;
    /** @var  DataCollectorTranslator */
    private $translator;

    public function __construct(DocumentManager $dm, DataCollectorTranslator $translator) {
        $this->dm = $dm;
        $this->translator = $translator;
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
                $citizenshipName = $citizenship ? $citizenship->getName() : $this->translator->trans('form.notice_stay_place_xls_type.not_specified');
                $tourists[$tourist->getId()] = $tourist->getFullName() . ' (' . $citizenshipName . ')';
            }
        }
        $builder->add('tourist',  InvertChoiceType::class, [
            'required' => true,
            'label' => 'form.task.tourist',
            'choices' => $tourists,
            'attr' => ['style' => 'width:250px'],
            'label_attr' => ['class' => 'col-md-4'],
        ]);
        $builder->get('tourist')->addModelTransformer(new EntityToIdTransformer($this->dm, 'MBH\Bundle\PackageBundle\Document\Tourist'));

        $builder->add('user', HiddenType::class);
        $builder->get('user')->addModelTransformer(new EntityToIdTransformer($this->dm, 'MBH\Bundle\UserBundle\Document\User'));
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'tourists' => [],
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getBlockPrefix()
    {
        return 'notice_stay_place_xls';
    }
}