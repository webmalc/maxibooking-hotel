<?php


namespace MBH\Bundle\PriceBundle\Form;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\PriceBundle\Form\DataTransformer\SpecialsToStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class SpecialsTransformedType extends AbstractType
{

    /** @var DocumentManager */
    private $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $transformer = new SpecialsToStringTransformer($this->dm);
        $builder->addModelTransformer($transformer);
    }

    /**
     * @return null|string
     */
    public function getParent(): ?string
    {
        return HiddenType::class;
    }


}