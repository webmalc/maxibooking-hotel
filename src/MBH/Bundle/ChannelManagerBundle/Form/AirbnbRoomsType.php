<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Utils;
use MBH\Bundle\ChannelManagerBundle\Document\AirbnbConfig;
use MBH\Bundle\ChannelManagerBundle\Services\Airbnb\Airbnb;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class AirbnbRoomsType extends AbstractType
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
        $roomTypes = $this->dm
            ->getRepository('MBHHotelBundle:RoomType')
            ->findBy(['isEnabled' => true]);

        /** @var AirbnbConfig $config */
        $config = $options['config'];

        foreach ($roomTypes as $roomType) {
            $syncRoom = $config->getSyncRoomByRoomType($roomType);
            $syncUrl = $this->router->generate('airbnb_room_calendar', ['id' => $roomType->getId()], Router::ABSOLUTE_URL);
            $help = !is_null($syncRoom)
                ? $syncUrl
                : '';

            $builder
                ->add($roomType->getId(), TextType::class, [
                    'label' => $roomType->getName(),
                    //TODO: Переименовать
                    'group' => 'forms.airbnb_rooms_type.sync_urls_group',
                    'required' => false,
                    'data' => !is_null($syncRoom) ? $syncRoom->getSyncUrl() : '',
                    'constraints' => [new Callback([$this, 'validateSyncUrl'])],
                    'help' => $help
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'config' => null,
            'constraints' => [new Callback([$this, 'checkIsNotEmpty'])],
        ]);
    }

    public function validateSyncUrl(?string $syncUrl, ExecutionContextInterface $context)
    {
        if (!is_null($syncUrl) && !Utils::startsWith($syncUrl, Airbnb::SYNC_URL_BEGIN)) {
            $context->addViolation('validator.airbnb_rooms_type.sync_url');
        }
    }

    public function checkIsNotEmpty(array $data, ExecutionContextInterface $context)
    {
        $notEmptySyncData = array_filter($data, function ($syncUrl) {
            return !empty($syncUrl);
        });
        if (empty($notEmptySyncData)) {
            $context->addViolation('Укажите URL для синхронизации хотя бы одного типа номера');
        }
    }

    public function getBlockPrefix()
    {
        return 'mbhchannel_manager_bundle_airbnb_room_form';
    }
}
