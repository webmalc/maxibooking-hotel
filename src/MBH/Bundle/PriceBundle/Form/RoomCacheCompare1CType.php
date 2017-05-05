<?php

namespace MBH\Bundle\PriceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class RoomCacheCompare1CType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('file', FileType::class, array(
                    'label' => 'XML файл сверки',
                    'required' => true,
                    'constraints' => [
                        new NotBlank(),
                        new File([
                            'maxSize' => '10M',
                            'mimeTypes' => ['text/plain', 'application/xml', 'text/xml'] ] ),
                        new Callback([$this, 'checkFile'])]
                ));
    }

    public function checkFile(UploadedFile $data, ExecutionContextInterface $context)
    {
        $use_errors = libxml_use_internal_errors(true);
        if (!simplexml_load_file($data->getRealPath())) {
            $context->addViolation('Неправильный формат файла сверки.');
        }
        libxml_clear_errors();
        libxml_use_internal_errors($use_errors);
    }

    public function getName()
    {
        return 'mbh_bundle_pricebundle_room_cache_compare_1c_type';
    }

}
