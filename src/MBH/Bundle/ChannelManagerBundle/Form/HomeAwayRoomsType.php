<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;


use MBH\Bundle\ChannelManagerBundle\Document\HomeAwayConfig;
use MBH\Bundle\ChannelManagerBundle\Services\HomeAway\HomeAway;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class HomeAwayRoomsType extends AbstractType
{
    /** @var TranslatorInterface */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var HomeAwayConfig $config */
        $config = $options['config'];

        $roomTypes = $config->getHotel()->getRoomTypes();

        foreach ($roomTypes as $roomType) {
            if (!$roomType->getIsEnabled()) {
                continue;
            }
            $syncRoom = $config->getSyncRoomByRoomType($roomType);

            $exampleUrl = 'example_homeaway_calendar_url';
            $help = $this->translator->trans('forms.homeaway_rooms_type.sync_url.help', ['%exampleUrl%' => $exampleUrl]);

            $builder
                ->add($roomType->getId(), TextType::class, [
                    'label' => $roomType->getName(),
                    'group' => 'forms.homeaway_rooms_type.sync_urls_group',
                    'required' => false,
                    'data' => $syncRoom !== null ? $syncRoom->getSyncUrl() : '',
                    'constraints' => [new Callback([$this, 'validateSyncUrl'])],
                    'help' => $help
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'config' => null,
            'constraints' => [new Callback([$this, 'checkIsNotEmpty'])],
        ]);
    }

    public function validateSyncUrl(?string $syncUrl, ExecutionContextInterface $context): void
    {
        $regexp = sprintf('/^https:\/\/.+?\.%s\..*/', HomeAway::NAME);
//        if ($syncUrl !== null && !preg_match($regexp, $syncUrl)) {
//            $context->addViolation('validator.airbnb_rooms_type.sync_url', ['%homeawayName%' => HomeAway::NAME]);
//            $context->addViolation('validator.airbnb_rooms_type.sync_url', ['%airbnbName%' => HomeAway::NAME]);
//        }
    }

    public function checkIsNotEmpty(array $data, ExecutionContextInterface $context): void
    {
        $notEmptySyncData = array_filter($data, static function ($syncUrl) {
            return !empty($syncUrl);
        });
        if (empty($notEmptySyncData)) {
            $context->addViolation('validator.airbnb_rooms_type.sync_urls');
        }
    }

    public function getBlockPrefix(): string
    {
        return 'mbhchannel_manager_bundle_homeaway_room_form';
    }

}
