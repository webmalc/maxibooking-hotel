<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Utils;
use MBH\Bundle\ChannelManagerBundle\Document\ICalServiceConfig;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ICalServiceRoomsType extends AbstractType
{
    private $dm;
    private $router;
    private $translator;

    public function __construct(DocumentManager $dm, Router $router, TranslatorInterface $translator)
    {
        $this->dm = $dm;
        $this->router = $router;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var ICalServiceConfig $config */
        $config = $options['config'];

        $roomTypes = $config->getHotel()->getRoomTypes();

        foreach ($roomTypes as $roomType) {
            if (!$roomType->getIsEnabled()) {
                continue;
            }
            $syncRoom = $config->getSyncRoomByRoomType($roomType);

            $help = $this->translator->trans('forms.ical_service_rooms_type.sync_url.help', [
                '%exampleUrl%' => $options['exampleRoomUrl'],
                '%channelManager%' => $options['channelManager']
            ]);

            $builder
                ->add($roomType->getId(), TextType::class, [
                    'label' => $roomType->getName(),
                    'group' => $this->translator->trans('forms.ical_service_rooms_type.sync_urls_group', [
                        '%channelManager%' => $options['channelManager']
                    ]),
                    'required' => false,
                    'data' => !is_null($syncRoom) ? $syncRoom->getSyncUrl() : '',
                    'constraints' => [new Callback(function (?string $syncUrl, ExecutionContextInterface $context) use ($options) {
                        if (!is_null($syncUrl) && !Utils::startsWith($syncUrl, $options['syncUrlBegin'])) {
                            $context->addViolation('validator.airbnb_rooms_type.sync_url');
                        }
                    })],
                    'help' => $help
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'config' => null,
            'constraints' => [new Callback([$this, 'checkIsNotEmpty'])],
            'exampleRoomUrl' => null,
            'syncUrlBegin' => null,
            'channelManager' => null
        ]);
    }

    public function checkIsNotEmpty(array $data, ExecutionContextInterface $context)
    {
        $notEmptySyncData = array_filter($data, function ($syncUrl) {
            return !empty($syncUrl);
        });
        if (empty($notEmptySyncData)) {
            $context->addViolation('validator.airbnb_rooms_type.sync_urls');
        }
    }

    public function getBlockPrefix()
    {
        return 'mbhchannel_manager_bundle_airbnb_room_form';
    }
}
