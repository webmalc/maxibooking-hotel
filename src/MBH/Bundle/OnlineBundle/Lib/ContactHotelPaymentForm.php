<?php
/**
 * Created by PhpStorm.
 * Date: 01.02.19
 */

namespace MBH\Bundle\OnlineBundle\Lib;


use MBH\Bundle\HotelBundle\Document\ContactInfo;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\Translation\TranslatorInterface;

class ContactHotelPaymentForm
{
    /**
     * @var string
     */
    private $text = '';

    /**
     * @var SearchForm
     */
    private $searchForm;

    /**
     * @var TranslatorInterface
     */
    private $trans;

    public function __construct(PaymentSystemHelper $paymentSystemsHelper)
    {
        $this->searchForm = $paymentSystemsHelper->getSearchForm();
        $this->trans = $paymentSystemsHelper->getTrans();

        $this->generateTextIfNotSetPaymentSystems();
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    public function __toString()
    {
        return $this->text;
    }

    private function generateTextIfNotSetPaymentSystems(): void
    {
        $hotels = $this->searchForm->getHotels();
        $selectedHotelId = $this->searchForm->getSelectedHotelId();
        $amountHotels = count($hotels);

        if ($amountHotels > 1 && $selectedHotelId === null) {
            $this->setTextWithoutContactInfo();
            return;
        }

        /** @var Hotel $selectedHotel */
        if ($amountHotels === 1) {
            $selectedHotel = $hotels[0];
        } else {
            $selectedHotel = array_filter(
                                 $hotels,
                                 function (Hotel $hotel) use ($selectedHotelId) {
                                     return $hotel->getId() === $selectedHotelId;
                                 }
                             )[0];
        }

        /** @var ContactInfo $contactInfo */
        $contactInfo = $selectedHotel->getContactInformation();

        if ($contactInfo !== null) {
            $phone = $contactInfo->getPhoneNumber();
            $email = $contactInfo->getEmail();
            $text = [];

            if ($phone !== null) {
                $text[] = sprintf(
                    $this->trans->trans('api.payment_form.not_set_payment_system.with_contact_phone'),
                    $phone
                );
            }

            if ($email !== null) {
                if ($text !== []) {
                    $text[] = $this->trans->trans('api.payment_form.not_set_payment_system.with_contact_or');
                }

                $text[] = sprintf(
                    $this->trans->trans('api.payment_form.not_set_payment_system.with_contact_email'),
                    $email
                );
            }

            if ($text !== []) {
                $this->setTextWithContactInfo($text);
                return;
            }
        }

        $this->setTextWithoutContactInfo();
    }

    private function setTextWithContactInfo(array $contactInfo): void
    {
        $this->text = sprintf(
            $this->trans->trans('api.payment_form.not_set_payment_system.with_contact'),
            implode(' ', $contactInfo)
        );
    }

    private function setTextWithoutContactInfo(): void
    {
        $this->text = $this->trans->trans('api.payment_form.not_set_payment_system.without_contact');
    }
}