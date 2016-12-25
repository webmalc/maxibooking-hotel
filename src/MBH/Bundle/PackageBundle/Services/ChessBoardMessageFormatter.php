<?php

namespace MBH\Bundle\PackageBundle\Services;

use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageAccommodation;
use Symfony\Component\Translation\DataCollectorTranslator;

class ChessBoardMessageFormatter
{
    private $translator;
    private $successfulMessages = [];
    private $errorMessages = [];

    public function __construct(DataCollectorTranslator $translator)
    {
        $this->translator = $translator;
    }

    public function addSuccessfulMessage($messageId, array $params = [])
    {
        $this->successfulMessages[] = $this->translator->trans($messageId, $params);
    }

    public function addErrorMessage($messageId, array $params = [])
    {
        $this->errorMessages[] = $this->translator->trans($messageId, $params);
    }

    public function addErrorRemoveAccommodationMessage()
    {
        $this->addErrorMessage('controller.chessboard.accommodation_not_last_remove.error');
    }

    public function addSuccessRemoveAccommodationMessage(PackageAccommodation $accommodation)
    {
        /** @var Package $package */
        $package = $accommodation->getPackage();
        $this->addSuccessfulMessage('controller.chessboard.accommodation_remove.success', [
            '%packageId%' => $package->getName(),
            '%payerInfo%' => $this->getPayerInfo($package),
            '%begin%' => $accommodation->getBegin()->format('d.m.Y'),
            '%end%' => $accommodation->getEnd()->format('d.m.Y'),
        ]);
    }

    public function addSuccessPackageUpdateMessage()
    {
        $this->addSuccessfulMessage('controller.chessboard.package_update.success');
    }

    public function addSuccessUpdateAccommodationMessage()
    {
        $this->addSuccessfulMessage('controller.chessboard.accommodation_update.success');
    }

    public function addSuccessAddAccommodationMessage(PackageAccommodation $newAccommodation)
    {
        $package = $newAccommodation->getPackage();
        $this->addSuccessfulMessage('controller.chessboard.accommodation_divide.success', [
            '%packageId%' => $package->getName(),
            '%payerInfo%' => $this->getPayerInfo($package),
            '%begin%' => $newAccommodation->getBegin()->format('d.m.Y'),
            '%end%' => $newAccommodation->getEnd()->format('d.m.Y'),
            '%roomName%' => $newAccommodation->getRoom()->getName()
        ]);
    }

    public function addErrorDivideAccommodationMessage()
    {
        $this->addErrorMessage('controller.chessboard.accommodation_divide.error');
    }

    public function getMessages()
    {
        return [
            'success' => $this->successfulMessages,
            'errors' => $this->errorMessages
        ];
    }

    private function getPayerInfo(Package $package)
    {
        $payerInfo = '';
        if ($package->getPayer()) {
            $payerInfo .= "плательщик \"{$package->getPayer()->getName()}\"";
        }

        return $payerInfo;
    }
}