<?php

namespace cog\LoanPaymentsCalculator\PaymentSchedule;

use cog\LoanPaymentsCalculator\DateProvider\DateDetermineStrategy\ExactDayOfMonthStrategy;
use cog\LoanPaymentsCalculator\DateProvider\DateProvider;
use cog\LoanPaymentsCalculator\DateProvider\HolidayProvider\WeekendsProvider;
use cog\LoanPaymentsCalculator\Schedule\Schedule;
use DateTime;
use PHPUnit\Framework\TestCase;

class AmortizingLoanPaymentScheduleCalculatorTest extends TestCase
{
    public function testCalculateSchedule(): void
    {
        // Given
        $principalAmount = 499000;
        $numberOfPeriods = 360;
        $annualInterestRate = 7.125;
        $startDate = new DateTime('2024-05-01');
        $expectedTotalMonthlyPayment = 3361.8554312727;

        $dateProvider = new DateProvider(new ExactDayOfMonthStrategy(), new WeekendsProvider(), true);
        $schedule = new Schedule($startDate, $numberOfPeriods, $dateProvider);
        $schedulePeriods = $schedule->generatePeriods();

        // When
        $calculator = new AmortizingLoanPaymentScheduleCalculator(
            $schedulePeriods,
            $principalAmount,
            $annualInterestRate,
            $numberOfPeriods
        );
        $payments = $calculator->calculateSchedule();

        // Then
        // Assert the number of payments
        $this->assertCount($numberOfPeriods, $payments);

        $currentInterestPayment = null;
        $currentPrincipalPayment = null;
        $currentPrincipalBalance = null;
        $count = 0;

        foreach ($payments as $payment) {
            $this->assertGreaterThan(0, $payment->getPrincipal());
            $this->assertGreaterThan(0, $payment->getInterest());
            self::assertEqualsWithDelta(
                $payment->getTotalPayment(),
                $expectedTotalMonthlyPayment,
                0.01
            );
            self::assertSame(
                $payment->getPrincipal() + $payment->getInterest(),
                $payment->getTotalPayment()
            );
            if ($currentInterestPayment && $currentPrincipalPayment && $currentPrincipalBalance) {
                $count++;
                $this->assertLessThan($currentInterestPayment, $payment->getInterest());
                $this->assertGreaterThan($currentPrincipalPayment, $payment->getPrincipal());
                $this->assertLessThan($currentPrincipalBalance, $payment->getPrincipalBalanceLeft());
            }
            $currentInterestPayment = $payment->getInterest();
            $currentPrincipalPayment = $payment->getPrincipal();
            $currentPrincipalBalance = $payment->getPrincipalBalanceLeft();
        }
        self::assertSame(359, $count);
    }
}
