<?php
/**
 * Date: 20.03.19
 */

namespace MBH\Bundle\OnlineBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfig;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DataForSearchForm
{
    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var FormConfig
     */
    private $formConfig;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var array|Hotel[]
     */
    private $hotels;

    /**
     * @var Router
     */
    private $router;

    public function __construct(DocumentManager $dm, Helper $helper, Router $router, RequestStack $request)
    {
        $this->dm = $dm;
        $this->helper = $helper;
        $this->router = $router;
        $this->request = $request->getCurrentRequest();
    }

    public function setFormConfig(FormConfig $formConfig): self
    {
        $this->formConfig = $formConfig;

        return $this;
    }

    /**
     * @param FormConfig $formConfig
     * @param Request|null $request
     * @return array|Hotel[]
     */
    public function getHotels(): array
    {
        if ($this->hotels === null) {
            $hotelsQb = $this->dm->getRepository('MBHHotelBundle:Hotel')
                ->createQueryBuilder()
                ->sort('fullTitle', 'asc');

            $configHotelsIds = $this->helper->toIds($this->formConfig->getHotels());

            $hotels = [];
            /** @var Hotel $hotel */
            foreach ($hotelsQb->getQuery()->execute() as $hotel) {
                if ($configHotelsIds && !in_array($hotel->getId(), $configHotelsIds)) {
                    continue;
                }

                foreach ($hotel->getTariffs() as $tariff) {
                    if ($tariff->getIsOnline()) {
                        $hotels[] = $hotel;
                        break;
                    }
                }
            }

            $this->hotels = $hotels;
        }

        return $this->hotels;
    }

    public function getRoomTypes(): array
    {
        $choices = $this->formConfig->getRoomTypeChoices()->toArray();

        if (count($choices) === 0) {
            /** @var Hotel $hotel */
            $roomTypes = [];
            foreach ($this->getHotels() as $hotel) {
                $roomTypes[] = $hotel->getRoomTypes()->toArray();
            }

            $choices = array_merge(...$roomTypes);
        }

        $this->filterRoomTypeIfIssetHotelInRequest($choices);

        return $choices;
    }

    public function getConfigForLoadAllIframe(): array
    {
        return [
            'urls'       => [
                'search'         => $this->getUrlSearchIframe(),
                'calendar'       => $this->getUrlCalendarIframe(),
                'additionalForm' => $this->getUrlAdditionalIframe(),
            ],
            'size' => [
                'search' => [
                    'width' => $this->formConfig->isFullWidth() ? '100%' : $this->formConfig->getFrameWidth(),
                    'height' => $this->formConfig->getFrameHeight()
                ],
                'calendar' => [
                    'width' => $this->formConfig->getCalendarFrameWidth(),
                    'height' => $this->formConfig->getCalendarFrameHeight()
                ],
                'additionalForm' => [
                    'width' => $this->formConfig->getAdditionalFormFrameWidth(),
                    'height' => $this->formConfig->getAdditionalFormFrameHeight()
                ]
            ],
            'metric' => [
                'yaCounterId' => $this->getYandexCounterId(),
                'googleCounterId' => $this->getGoogleCounterId()
            ]
        ];
    }

    private function getYandexCounterId(): string
    {
        $configYandex = $this->formConfig->getYandexAnalyticConfig();
        if ($configYandex !== null && $configYandex->getIsEnabled()) {
            return $configYandex->getId();
        }

        return '';
    }

    private function getGoogleCounterId(): string
    {
        $configGoogle = $this->formConfig->getGoogleAnalyticConfig();
        if ($configGoogle !== null && $configGoogle->getIsEnabled()) {
            return $configGoogle->getId();
        }

        return '';
    }

    public function getUrlSearchIframe(): string
    {
        return $this->generateUrl(FormConfig::ROUTER_NAME_SEARCH_IFRAME, $this->addParametersHotel());
    }

    public function getUrlCalendarIframe(): string
    {
        return $this->generateUrl(FormConfig::ROUTER_NAME_CALENDAR_IFRAME);
    }

    public function getUrlForScriptLoadAllIframe(): string
    {
        return $this->generateUrl(FormConfig::ROUTER_NAME_LOAD_ALL_IFRAME, [], false);
    }

    public function getUrlAdditionalIframe(): ?string
    {
        if ($this->formConfig->isUseAdditionalForm()) {
            return $this->generateUrl(FormConfig::ROUTER_NAME_ADDITIONAL_IFRAME, $this->addParametersHotel());
        }

        return null;
    }

    private function addParametersHotel(array $parameters = []): array
    {
        $hotelId = $this->request->get('hotel');

        if (!empty($hotelId)) {
            $parameters['hotel'] = $hotelId;
        }

        return $parameters;
    }

    private function generateUrl(string $routerName, array $parameters = [], bool $useLocale = true): string
    {
        $defaultParameters = [
            'formConfigId' => $this->formConfig->getId(),
        ];

        if ($useLocale) {
            $defaultParameters['locale'] = $this->request->getLocale();
        }

        return $this->router->generate(
            $routerName,
            array_merge(
                $defaultParameters,
                $parameters
            )
            ,
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    private function filterRoomTypeIfIssetHotelInRequest(array &$chooseRoomType): void
    {
        if ($this->request === null) {
            return;
        }

        $hotelId = $this->request->get('hotel');

        if (empty($hotelId)) {
            return;
        }

        /** @var RoomType $roomType */
        foreach ($chooseRoomType as $index => $roomType) {
            if ($roomType->getHotel()->getId() !== $hotelId) {
                unset($chooseRoomType[$index]);
            }
        }

    }
}