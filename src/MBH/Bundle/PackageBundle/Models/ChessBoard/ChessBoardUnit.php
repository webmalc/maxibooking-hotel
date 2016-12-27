<?php

namespace MBH\Bundle\PackageBundle\Models\ChessBoard;

use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageAccommodation;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class ChessBoardUnit implements \JsonSerializable
{
    /** @var Package */
    private $package;
    /** @var  PackageAccommodation */
    private $accommodation;
    /** @var  AuthorizationChecker $rightsChecker */
    private $rightsChecker;

    const LEFT_RELATIVE_POSITION = 'left';
    const RIGHT_RELATIVE_POSITION = 'right';
    const MIDDLE_RELATIVE_POSITION = 'middle';
    const FULL_PACKAGE_ACCOMMODATION = 'full';

    public function __construct(AuthorizationChecker $rightsChecker)
    {
        $this->rightsChecker = $rightsChecker;
    }

    /**
     * @param Package $package
     * @param PackageAccommodation|null $accommodation
     * @return ChessBoardUnit
     */
    public function setInitData(Package $package, ?PackageAccommodation $accommodation = null)
    {
        $this->package = $package;
        $this->accommodation = $accommodation;

        return $this;
    }

    public function __toArray(): array
    {
        $array = [
            'id' => $this->accommodation ? $this->accommodation->getId() : $this->package->getId(),
            'number' => $this->package->getNumberWithPrefix(),
            'price' => $this->package->getPrice(),
            'begin' => $this->getBeginDate()->format('d.m.Y'),
            'end' => $this->getEndDate()->format('d.m.Y'),
            'roomTypeId' => $this->getRoomTypeId(),
            'paidStatus' => $this->package->getPaidStatus(),
            'packageBegin' => $this->package->getBegin()->format('d.m.Y'),
            'packageEnd' => $this->package->getEnd()->format('d.m.Y'),
            'isCheckIn' => $this->package->getIsCheckIn(),
            'isCheckOut' => $this->package->getIsCheckOut(),
            'isLocked' => $this->package->getIsLocked(),
            'viewPackage' => $this->hasViewPackageRights($this->package),
            'removePackage' => $this->hasRemovePackageRights($this->package),
            'updatePackage' => $this->hasUpdatePackageRights($this->package)
        ];

        if ($this->accommodation) {
            $array['updateAccommodation'] = $this->hasUpdateAccommodationRights($this->accommodation);
        }
        if ($this->package->getPayer()) {
            $array['payer'] = $this->package->getPayer()->getShortName();
        }
        if ($this->accommodation) {
            $array['accommodation'] = $this->accommodation->getRoom()->getId();
        }
        if ($this->accommodation) {
            $array['position'] = $this->getAccommodationRelativePosition($this->accommodation, $this->package);
        }
        if ($this->accommodation) {
            $array['packageId'] = $this->package->getId();
        }

        return $array;
    }

    public function getBeginDate()
    {
        return $this->accommodation ? $this->accommodation->getBegin() : $this->package->getBegin();
    }

    public function getEndDate()
    {
        if ($this->accommodation) {
            return $this->accommodation->getEnd();
        }
        if ((count($this->package->getAccommodations()) > 0)
            && $this->package->getLastEndAccommodation()->getTimestamp() < $this->package->getEnd()->getTimestamp()) {
            return $this->package->getLastEndAccommodation();
        }

        return $this->package->getEnd();
    }

    public function getRoomTypeId()
    {
        return $this->accommodation ?
            $this->accommodation->getRoom()->getRoomType()->getId() : $this->package->getRoomType()->getId();
    }

    /**
     * Получение относительного положения размещения по отношению к остальным размещениям брони
     * Размещение может занимать полное время брони, быть первым размещением, последним размещением или промежуточным
     *
     * @param PackageAccommodation $accommodation
     * @param Package $package
     * @return string
     */
    private function getAccommodationRelativePosition(PackageAccommodation $accommodation, Package $package)
    {
        $packageBeginString = $package->getBegin()->format('d.m.Y');
        $lastPackageAccommodationEndString = $package->getLastEndAccommodation()->format('d.m.Y');
        $accommodationBeginString = $accommodation->getBegin()->format('d.m.Y');
        $accommodationEndString = $accommodation->getEnd()->format('d.m.Y');

        if ($accommodationBeginString == $packageBeginString
            && $accommodationEndString == $lastPackageAccommodationEndString
        ) {
            return self::FULL_PACKAGE_ACCOMMODATION;
        }
        if ($accommodationBeginString == $packageBeginString
            && $accommodationEndString != $lastPackageAccommodationEndString
        ) {
            return self::LEFT_RELATIVE_POSITION;
        }
        if ($accommodationEndString == $lastPackageAccommodationEndString
            && $accommodationBeginString != $packageBeginString
        ) {
            return self::RIGHT_RELATIVE_POSITION;
        }

        return self::MIDDLE_RELATIVE_POSITION;
    }

    private function hasUpdateAccommodationRights(PackageAccommodation $accommodation)
    {
        return ($this->rightsChecker->isGranted('ROLE_PACKAGE_ACCOMMODATION')
        && ($this->rightsChecker->isGranted('EDIT', $accommodation)
            || $this->rightsChecker->isGranted('ROLE_PACKAGE_EDIT_ALL')) ? true : false);
    }

    private function hasUpdatePackageRights(Package $package)
    {
        return ($this->rightsChecker->isGranted('ROLE_PACKAGE_EDIT')
        && ($this->rightsChecker->isGranted('ROLE_PACKAGE_EDIT_ALL')
            || $this->rightsChecker->isGranted('EDIT', $package)) ? true : false);
    }

    private function hasRemovePackageRights(Package $package)
    {
        return ($this->rightsChecker->isGranted('ROLE_PACKAGE_DELETE')
        && ($this->rightsChecker->isGranted('DELETE', $package)
            || $this->rightsChecker->isGranted('ROLE_PACKAGE_DELETE_ALL')) ? true : false);
    }

    private function hasViewPackageRights(Package $package)
    {
        return ($this->rightsChecker->isGranted('ROLE_PACKAGE_VIEW_ALL')
        || ($this->rightsChecker->isGranted('VIEW', $package)
            && $this->rightsChecker->isGranted('ROLE_PACKAGE_VIEW')) ? true : false);
    }

    /**
     * @return array
     */
    public function jsonSerialize() : array
    {
        return $this->__toArray();
    }
}