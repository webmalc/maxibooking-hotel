<?php
/**
 * Created by PhpStorm.
 * Date: 10.10.18
 */

namespace MBH\Bundle\PriceBundle\Services;


use MBH\Bundle\PriceBundle\Lib\PriceCacheSkippingDate;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class PriceCacheResultUpdate
{
    private const TEXT_NO_DATA_UPDATE = 'Нет данных для обновления.';
    private const TEXT_REMOVE = 'Удалено записей: %s.';
    private const TEXT_CREATE = 'Создано записей: %s .';
    private const TEXT_UPDATE = 'Обновлено записей: %s.';
    private const TEXT_WEEKDAYS = 'На указанные даты: %s, нет выбранных дней недели.';
    private const TEXT_SAME = 'Т.к. предложенные цены совпадают с раннее установленными, изменения не были применены для дат: %s.';
    private const TEXT_ERROR = 'Некорректные данные для следующих дней: %s.';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var int
     */
    private $amountRemove = 0;

    /**
     * @var int
     */
    private $amountUpdate = 0;

    /**
     * @var int
     */
    private $amountCreate = 0;

    /**
     * @var PriceCacheSkippingDate[]
     */
    private $holderSkippedDaysAtUpdate = [];

    /**
     * @var PriceCacheSkippingDate[]
     */
    private $holderSkippedDaysAtCreate = [];

    /**
     * PriceCacheResultUpdate constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Request $request
     * @param bool $prepareData
     */
    public function addFlashBag(Request $request, bool $prepareData = false): void
    {
        /** @var Session $session */
        $session = $request->getSession();

        if($prepareData) {
            $this->prepareData();
        }

        if ($this->haveSomeThingToShow()) {
            $flashBag = $session->getFlashBag();

            $this->addMsgToFlashBag($flashBag, $this->makeMsgForUpdate());
            $this->addMsgToFlashBag($flashBag, $this->makeMsgForCreate());

            if ($this->haveRemoves()) {
                $flashBag->add('success', sprintf(self::TEXT_REMOVE, $this->getAmountRemove()));
            }
        } else {
            $session->getFlashBag()->add('info', self::TEXT_NO_DATA_UPDATE);
        }
    }

    /**
     * @param FlashBagInterface $flashBag
     * @param array $rawMsg
     */
    private function addMsgToFlashBag(FlashBagInterface $flashBag, array $rawMsg): void
    {
        if ($rawMsg !== []) {
            $flashBag->add(
                isset($rawMsg['new']) && count($rawMsg) === 1 ? 'success' : 'warning',
                implode("<br>", $rawMsg)
            );
        }
    }

    /**
     * @return array
     */
    private function makeMsgForUpdate(): array
    {
        $fullMsg = [];

        $sameDates = $this->getGroupAtUpdate(PriceCacheSkippingDate::REASON_SAME);
        $weekdaysDates = $this->getGroupAtUpdate(PriceCacheSkippingDate::REASON_WEEKDAYS);
        $errorDates = $this->getGroupAtUpdate(PriceCacheSkippingDate::REASON_ERROR);

        if ($this->haveUpdates()) {
            $fullMsg['new'] = sprintf(self::TEXT_UPDATE, $this->getAmountUpdate());
        }
        if ($sameDates !== []) {
            $fullMsg[] = sprintf(self::TEXT_SAME, implode(', ', $sameDates));
        }

        $this->commonMsg($weekdaysDates, $errorDates, $fullMsg);

        return $fullMsg;
    }

    /**
     * @param array $weekdaysDates
     * @param array $errorDates
     * @param array $fullMsg
     */
    private function commonMsg(array $weekdaysDates, array $errorDates, array &$fullMsg): void
    {
        if ($weekdaysDates !== []) {
            $fullMsg[] = sprintf(self::TEXT_WEEKDAYS, implode(', ', $weekdaysDates));
        }

        if ($errorDates !== []) {
            $fullMsg[] = sprintf(self::TEXT_ERROR, implode(', ', $errorDates));
        }
    }

    /**
     * @return array
     */
    private function makeMsgForCreate(): array
    {
        $fullMsg = [];

        $weekdaysDates = $this->getGroupAtCreate(PriceCacheSkippingDate::REASON_WEEKDAYS);
        $errorDates = $this->getGroupAtCreate(PriceCacheSkippingDate::REASON_ERROR);

        if ($this->haveCreates()) {
            $fullMsg['new'] = sprintf(self::TEXT_CREATE, $this->getAmountCreate());
        }

        $this->commonMsg($weekdaysDates, $errorDates, $fullMsg);

        return $fullMsg;
    }

    /**
     * @return array
     */
    protected function getHolderSkippedDaysAtUpdate(): array
    {
        return $this->holderSkippedDaysAtUpdate;
    }

    /**
     * @return array
     */
    protected function getHolderSkippedDaysAtCreate(): array
    {
        return $this->holderSkippedDaysAtCreate;
    }

    /**
     * @return int
     */
    protected function getAmountRemove(): int
    {
        return $this->amountRemove;
    }

    /**
     * @param int $amountRemove
     */
    public function setAmountRemove(int $amountRemove): void
    {
        $this->amountRemove = $amountRemove;
    }

    /**
     * @param PriceCacheSkippingDate $reason
     */
    public function addSkippedDaysAtUpdate(PriceCacheSkippingDate $reason): void
    {
        $this->holderSkippedDaysAtUpdate[] = $reason;
    }

    /**
     * @param PriceCacheSkippingDate $reason
     */
    public function addSkippedDaysAtCreate(PriceCacheSkippingDate $reason): void
    {
        $this->holderSkippedDaysAtCreate[] = $reason;
    }

    /**
     * @return int
     */
    protected function getAmountUpdate(): int
    {
        return $this->amountUpdate;
    }

    /**
     * @param int $amountUpdate
     */
    public function setAmountUpdate(int $amountUpdate): void
    {
        $this->amountUpdate = $amountUpdate;
    }

    /**
     * @return int
     */
    protected function getAmountCreate(): int
    {
        return $this->amountCreate;
    }

    /**
     * @param int $amountCreate
     */
    public function setAmountCreate(int $amountCreate): void
    {
        $this->amountCreate = $amountCreate;
    }

    /**
     * @return bool
     */
    protected function haveSomeThingToShow(): bool
    {
        return $this->haveRemoves()
            || $this->thereWasChanged()
            || $this->haveErrorsAtCreate()
            || $this->haveErrorsAtUpdate();
    }

    /**
     * @return bool
     */
    protected function thereWasChanged(): bool
    {
        return $this->amountCreate > 0 || $this->amountUpdate > 0;
    }

    /**
     * @return bool
     */
    protected function haveErrorsAtCreate(): bool
    {
        return $this->getHolderSkippedDaysAtCreate() !== [];
    }

    /**
     * @return bool
     */
    protected function haveErrorsAtUpdate(): bool
    {
        return $this->getHolderSkippedDaysAtUpdate() !== [];
    }

    /**
     * @return bool
     */
    protected function haveRemoves(): bool
    {
        return $this->amountRemove > 0;
    }

    /**
     * @return bool
     */
    protected function haveCreates(): bool
    {
        return $this->amountCreate > 0;
    }

    /**
     * @return bool
     */
    protected function haveUpdates(): bool
    {
        return $this->amountUpdate > 0;
    }

    /**
     * Для генератора, т.к. там нельзя одновременно и удалить запись и обновить
     */
    private function prepareData(): void
    {
        if ($this->getAmountUpdate() !== 0 && $this->getAmountUpdate() === $this->getAmountRemove()) {
            $this->amountRemove = 0;
        }
    }

    /**
     * @param string $reason
     * @return array
     */
    protected function getGroupAtCreate(string $reason): array
    {
        return $this->groupingReasons($this->holderSkippedDaysAtCreate, $reason);
    }

    /**
     * @param string $reason
     * @return array
     */
    protected function getGroupAtUpdate(string $reason): array
    {
        return $this->groupingReasons($this->holderSkippedDaysAtUpdate, $reason);
    }

    /**
     * @param array $holders
     * @param string $reason
     * @return array
     */
    protected function groupingReasons(array $holders, string $reason): array
    {
        $dates = [];

        /** @var PriceCacheSkippingDate $holder */
        foreach ($holders as $holder) {
            if ($holder->getReasons() === $reason) {
                $dates[] = $holder->getDate()->format('d.m.Y');
            }
        }

        return $dates;
    }
}