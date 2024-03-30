<?php
/**
 * @author: Vova Lando <vova.lando@gmail.com>
 * @package: LoanPaymentsCalculator
 * @subpackage:
 * @created: 15/06/2017 11:04
 */

namespace cog\LoanPaymentsCalculator\DateProvider\DateDetermineStrategy;

use DateInterval;
use DateTime;

class ExactDayOfMonthStrategy implements DateDetermineStrategyInterface
{
    public function calculateNextDate(DateTime $startDate): DateTime
    {
        return (clone $startDate)->add(new DateInterval('P1M'));
    }
}
