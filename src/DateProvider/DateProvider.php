<?php

/**
 * @author: Vova Lando <vova.lando@cashongo.co.uk>
 * @package: LoanPaymentsCalculator
 * @subpackage: DateProvider
 * @created: 14/06/2017 14:55
 */

namespace cog\LoanPaymentsCalculator\DateProvider;

use cog\LoanPaymentsCalculator\DateProvider\DateDetermineStrategy\DateDetermineStrategyInterface;
use cog\LoanPaymentsCalculator\DateProvider\HolidayProvider\HolidayProvider;
use DateTime;

class DateProvider
{
    private DateDetermineStrategyInterface $dateDetermineStrategy;
    private HolidayProvider $holidayProvider;
    private bool $shiftForward;

    public function __construct(
        DateDetermineStrategyInterface $dateDetermineStrategy,
        HolidayProvider $holidayProvider,
        bool $shiftForward
    ) {
        $this->dateDetermineStrategy = $dateDetermineStrategy;
        $this->holidayProvider = $holidayProvider;
        $this->shiftForward = $shiftForward;
    }

    public function calculate(DateTime $startDate): DateTime
    {
        $calculatedDate = $this->dateDetermineStrategy->calculateNextDate($startDate);
        if ($this->holidayProvider->isHoliday($calculatedDate)) {
            $calculatedDate = $this->shiftForward ?
                $this->getNextBusinessDay($calculatedDate) :
                $this->getPreviousBusinessDay($calculatedDate);
        }

        return $calculatedDate;
    }

    private function getNextBusinessDay(DateTime $date): DateTime
    {
        do {
            $date->modify('+1 day');
        } while ($this->holidayProvider->isHoliday($date));

        return $date;
    }

    private function getPreviousBusinessDay(DateTime $date): DateTime
    {
        do {
            $date->modify('-1 day');
        } while ($this->holidayProvider->isHoliday($date));

        return $date;
    }
}
