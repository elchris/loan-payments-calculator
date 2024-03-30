<?php
/**
 * @author: Vova Lando <vova.lando@gmail.com>
 * @package: LoanPaymentsCalculator
 * @subpackage:
 * @created: 06/09/2017 13:31
 */

namespace cog\LoanPaymentsCalculator\PaymentSchedule;

use cog\LoanPaymentsCalculator\DateProvider\DateDetermineStrategy\ExactDayOfMonthStrategy;
use cog\LoanPaymentsCalculator\DateProvider\DateProvider;
use cog\LoanPaymentsCalculator\DateProvider\HolidayProvider\WeekendsProvider;
use cog\LoanPaymentsCalculator\Payment\Payment;
use cog\LoanPaymentsCalculator\Schedule\Schedule;
use DateTime;
use PHPUnit\Framework\TestCase;

class AnnuityPaymentScheduleCalculatorTest extends TestCase
{
    public function testCreateAnnuityPaymentSchedule(): void
    {
        $startDate = new DateTime('2016-08-08');
        $principalAmount = 500;
        $numberOfPeriods = 5;
        $paymentAmount = 113.25680760233848;
        $dateProvider = new DateProvider(new ExactDayOfMonthStrategy(), new WeekendsProvider(), true);
        $schedule = new Schedule($startDate, $numberOfPeriods, $dateProvider);
        $schedulePeriods = $schedule->generatePeriods();

        $paymentSchedule = new AnnuityPaymentScheduleCalculator($schedulePeriods, $principalAmount, 0.001368925394);
        $payments = $paymentSchedule->calculateSchedule();

        $this->assertSame($numberOfPeriods, $numberOfPeriods);
        for ($i = 0; $i < $numberOfPeriods; $i++) {
            $this->assertEqualsWithDelta(
                $paymentAmount,
                $payments[$i]->getPrincipal() + $payments[$i]->getInterest(),
                0.001
            );
        }

        //$this->printSchedule($payments);
    }

    public function testCreateOneMonthAnnuityPaymentSchedule(): void
    {
        $startDate = new DateTime('2016-08-08');
        $principalAmount = 500;
        $numberOfPeriods = 1;
        $paymentAmount = 521.21834360699995;
        $dateProvider = new DateProvider(new ExactDayOfMonthStrategy(), new WeekendsProvider(), true);
        $schedule = new Schedule($startDate, $numberOfPeriods, $dateProvider);
        $schedulePeriods = $schedule->generatePeriods();

        $paymentSchedule = new AnnuityPaymentScheduleCalculator($schedulePeriods, $principalAmount, 0.001368925394);
        $payments = $paymentSchedule->calculateSchedule();

        $this->assertSame($numberOfPeriods, $numberOfPeriods);
        $this->assertSame($paymentAmount, $payments[0]->getPrincipal() + $payments[0]->getInterest());
        //$this->printSchedule($payments);
    }

    /**
     * @param Payment[] $payments
     */
    private function printSchedule(array $payments): void
    {
        print(PHP_EOL);
        foreach ($payments as $i => $iValue) {
            print("------------------------- Payment #" . $i . " -------------------------" . PHP_EOL);
            print("DueDate: " . $iValue->getPeriod()->endDate->format('Y-m-d') . PHP_EOL);
            print("Period in days: " . $iValue->getPeriod()->daysLength . PHP_EOL);
            print("Payment Principal: " . $iValue->getPrincipal() . PHP_EOL);
            print("Payment Interest: " . $iValue->getInterest() . PHP_EOL);
            print("Total Payment: " . ($iValue->getPrincipal() + $iValue->getInterest()) . PHP_EOL);
            print("Principal left: " . $iValue->getPrincipalBalanceLeft() . PHP_EOL);
        }
        print(PHP_EOL);
    }
}
