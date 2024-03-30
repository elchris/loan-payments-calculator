<?php
/**
 * @author: Vova Lando <vova.lando@gmail.com>
 * @package: LoanPaymentsCalculator
 * @subpackage:
 * @created: 15/06/2017 10:27
 */

namespace cog\LoanPaymentsCalculator\Schedule;

use cog\LoanPaymentsCalculator\DateProvider\DateDetermineStrategy\ExactDayOfMonthStrategy;
use cog\LoanPaymentsCalculator\DateProvider\DateProvider;
use cog\LoanPaymentsCalculator\DateProvider\HolidayProvider\WeekendsProvider;
use DateTime;
use PHPUnit\Framework\TestCase;

class ScheduleTest extends TestCase
{
    public function testCreateSimpleSchedule(): void
    {
        $now = new DateTime();
        $dateProvider = new DateProvider(new ExactDayOfMonthStrategy(), new WeekendsProvider(), true);
        $schedule = new Schedule($now, 12, $dateProvider);
        $periods = $schedule->generatePeriods();
        $this->assertCount(12, $periods);
    }
}
