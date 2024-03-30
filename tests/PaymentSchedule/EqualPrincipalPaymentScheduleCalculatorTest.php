<?php
/**
 * @author: Vova Lando <vova.lando@gmail.com>
 * @package: LoanPaymentsCalculator
 * @subpackage:
 * @created: 31/08/2017 16:55
 */

namespace cog\LoanPaymentsCalculator\PaymentSchedule;

use cog\LoanPaymentsCalculator\DateProvider\DateDetermineStrategy\ExactDayOfMonthStrategy;
use cog\LoanPaymentsCalculator\DateProvider\DateProvider;
use cog\LoanPaymentsCalculator\DateProvider\HolidayProvider\WeekendsProvider;
use cog\LoanPaymentsCalculator\Payment\Payment;
use cog\LoanPaymentsCalculator\Schedule\Schedule;
use DateTime;
use PHPUnit\Framework\TestCase;

class EqualPrincipalPaymentScheduleCalculatorTest extends TestCase
{
    public function testCreateFixedPrincipalPaymentSchedule(): void
    {
        $startDate = new DateTime('2016-08-08');
        $principalAmount = 500;
        $numberOfPeriods = 5;
        $dateProvider = new DateProvider(new ExactDayOfMonthStrategy(), new WeekendsProvider(), true);
        $schedule = new Schedule($startDate, $numberOfPeriods, $dateProvider);
        $schedulePeriods = $schedule->generatePeriods();

        $paymentSchedule = new EqualPrincipalPaymentScheduleCalculator($schedulePeriods, $principalAmount, 0.001368925394);
        $payments = $paymentSchedule->calculateSchedule();
        $paymentPrincipal = (float) ($principalAmount / $numberOfPeriods);

        $this->assertCount($numberOfPeriods, $payments);
        $this->assertSame(64.476386057399992, $paymentSchedule->getTotalInterest());
        for ($i = 0; $i < $numberOfPeriods; $i++) {
            $this->assertSame($paymentPrincipal, $payments[$i]->getPrincipal());
        }

        //$this->printSchedule($payments);
    }

    public function testOneMonthFixedPrincipalPaymentSchedule(): void
    {
        $startDate = new DateTime('2016-08-08');
        $dateProvider = new DateProvider(new ExactDayOfMonthStrategy(), new WeekendsProvider(), true);
        $schedule = new Schedule($startDate, 1, $dateProvider);
        $schedulePeriods = $schedule->generatePeriods();

        $paymentSchedule = new EqualPrincipalPaymentScheduleCalculator($schedulePeriods, 500, 0.001368925394);
        $payments = $paymentSchedule->calculateSchedule();
        $this->assertCount(1, $payments);
        $this->assertSame($startDate, $payments[0]->getPeriod()->startDate);
        $this->assertSame(21.218343606999998, $paymentSchedule->getTotalInterest());

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
            print("Principal left: " . $iValue->getPrincipalBalanceLeft() . PHP_EOL);
        }
        print(PHP_EOL);
    }
}
