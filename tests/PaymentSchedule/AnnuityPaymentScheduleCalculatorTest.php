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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AnnuityPaymentScheduleCalculatorTest extends TestCase
{
    public static function annuityPaymentScheduleDataProvider(): array
    {
        return [
            [new DateTime('2016-08-08'), 500, 5, 113.25680760233848, 0.001368925394],
            [new DateTime('2016-08-08'), 500, 1, 521.21834360699995, 0.001368925394],
            [new DateTime('2024-05-01'), 499000, 360, 3432.4921123041, 0.000195205479452],
        ];
    }

    #[DataProvider('annuityPaymentScheduleDataProvider')]
    public function testCreateAnnuityPaymentSchedule(
        DateTime $startDate,
        float $principalAmount,
        int $numberOfPeriods,
        float $paymentAmount,
        float $dailyInterestRate
    ): void {
        $dateProvider = new DateProvider(new ExactDayOfMonthStrategy(), new WeekendsProvider(), true);
        $schedule = new Schedule($startDate, $numberOfPeriods, $dateProvider);
        $schedulePeriods = $schedule->generatePeriods();
        $paymentSchedule = new AnnuityPaymentScheduleCalculator(
            $schedulePeriods,
            $principalAmount,
            $dailyInterestRate
        );
        $payments = $paymentSchedule->calculateSchedule();

        $this->assertCount(
            $numberOfPeriods, $schedulePeriods
        );
        for ($i = 0; $i < $numberOfPeriods; $i++) {
            $this->assertEqualsWithDelta(
                $paymentAmount,
                $payments[$i]->getPrincipal() + $payments[$i]->getInterest(),
                0.01
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

        $this->assertCount(
            $numberOfPeriods, $schedulePeriods
        );
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
