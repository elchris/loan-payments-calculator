<?php

namespace cog\LoanPaymentsCalculator\PaymentSchedule;

use cog\LoanPaymentsCalculator\DateProvider\DateDetermineStrategy\ExactDayOfMonthStrategy;
use cog\LoanPaymentsCalculator\DateProvider\DateProvider;
use cog\LoanPaymentsCalculator\DateProvider\HolidayProvider\NeverHolidayProvider;
use cog\LoanPaymentsCalculator\Payment\Payment;
use cog\LoanPaymentsCalculator\Schedule\Schedule;
use DateTime;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AmortizingLoanPaymentScheduleCalculatorTest extends TestCase
{
    public static function loanDataProvider(): array
    {
        return [
            [
                new DateTime('2024-05-01'), //loanStartDate
                499000.00, //principal
                360, //numberOfPeriods
                7.125, //annualInterestRate
                3361.8554312727, //expectedTotalMonthlyPayment
                353, //expectedTotalPayments if extra payments are present
            ],
            [
                new DateTime('2024-06-01'), //loanStartDate
                15500.00, //principal
                48, //numberOfPeriods
                18.00, //annualInterestRate
                455.31249392652, //expectedTotalMonthlyPayment,
                36, //expectedTotalPayments if extra payments are present
            ],
            [
                new DateTime('2024-06-01'), //loanStartDate
                16585.00, //principal
                48, //numberOfPeriods
                18.00, //annualInterestRate
                487.18436850137, //expectedTotalMonthlyPayment
                37, //expectedTotalPayments if extra payments are present
            ],
        ];
    }

    #[DataProvider('loanDataProvider')]
    public function testCalculateSchedule(
        DateTime $startDate,
        float $principalAmount,
        int $numberOfPeriods,
        float $annualInterestRate,
        float $expectedTotalMonthlyPayment,
    ): void {
        //Given
        $schedulePeriods = $this->getPeriods(
            $startDate,
            $numberOfPeriods
        );

        // When
        $calculator = new AmortizingLoanPaymentScheduleCalculator(
            $schedulePeriods,
            $principalAmount,
            $annualInterestRate,
        );
        $payments = $calculator->calculateSchedule();

        // Then
        // Assert the number of payments
        $this->assertCount($numberOfPeriods, $payments);

        $this->assertPaymentsAreValid(
            $payments,
            $expectedTotalMonthlyPayment,
            $numberOfPeriods
        );
    }

    #[DataProvider('loanDataProvider')]
    public function testCalculateScheduleWithExtraPayments(
        DateTime $startDate,
        float $principalAmount,
        int $numberOfPeriods,
        float $annualInterestRate,
        float $expectedTotalMonthlyPayment,
        int $expectedTotalPayments
    ): void {
        //Given
        $schedulePeriods = $this->getPeriods(
            $startDate,
            $numberOfPeriods
        );

        $paymentOne = 1000.00;
        $paymentTwo = 2000.00;

        // When
        $calculator = new AmortizingLoanPaymentScheduleCalculator(
            $schedulePeriods,
            $principalAmount,
            $annualInterestRate,
        );
        $calculator->addExtraPrincipalPayment($paymentOne);
        $calculator->addExtraPrincipalPayment($paymentTwo);
        $payments = $calculator->calculateSchedule();

        self::assertGreaterThan(
            0,
            count($payments)
        );
        $totalPrincipalPaid = 0;
        $totalInterestPaid = 0;
        foreach ($payments as $payment) {
            $totalInterestPaid += $payment->getInterest();
            $totalPrincipalPaid += $payment->getPrincipal();
        }
        self::assertEqualsWithDelta(
            $principalAmount,
            $totalPrincipalPaid + $paymentOne + $paymentTwo,
            0.01
        );

        // Then
        // Assert the number of payments
        $this->assertCount($expectedTotalPayments, $payments);

        $this->assertPaymentsAreValid(
            $payments,
            $expectedTotalMonthlyPayment,
            $numberOfPeriods,
            $expectedTotalPayments
        );
    }

    /**
     * @param Payment[] $payments
     */
    private function assertPaymentsAreValid(
        array $payments,
        float $expectedTotalMonthlyPayment,
        int $numberOfPeriods,
        int $expectedTotalPayments = null
    ): void {
        $hasExtraPrincipalPayments = true;
        if (!$expectedTotalPayments) {
            $expectedTotalPayments = $numberOfPeriods;
            $hasExtraPrincipalPayments = false;
        }
        $currentInterestPayment = null;
        $currentPrincipalPayment = null;
        $currentPrincipalBalance = null;

        $paymentCounter = 0;
        $paymentComparisonCount = 0;
        foreach ($payments as $payment) {
            $paymentCounter++;
            $this->assertGreaterThan(0, $payment->getPrincipal());
            $this->assertGreaterThan(0, $payment->getInterest());
            $isNotLastPayment = $paymentCounter <= ((count($payments) - 1));
            if (!$hasExtraPrincipalPayments || $isNotLastPayment) {
                self::assertEqualsWithDelta(
                    $expectedTotalMonthlyPayment,
                    $payment->getTotalPayment(),
                    0.01
                );
            } else {
                self::assertLessThan(
                    $expectedTotalMonthlyPayment,
                    $payment->getTotalPayment()
                );
            }
            self::assertSame(
                $payment->getPrincipal() + $payment->getInterest(),
                $payment->getTotalPayment()
            );
            self::assertSame(
                $payment->getPrincipal() + $payment->getInterest(),
                $payment->getTotalPayment()
            );
            if ($currentInterestPayment && $currentPrincipalPayment && $currentPrincipalBalance) {
                $paymentComparisonCount++;
                self::assertLessThan($currentInterestPayment, $payment->getInterest());
                if (!$hasExtraPrincipalPayments || $isNotLastPayment) {
                    self::assertGreaterThan($currentPrincipalPayment, $payment->getPrincipal());
                } else {
                    self::assertLessThan($currentPrincipalPayment, $payment->getPrincipal());
                }
                self::assertLessThan($currentPrincipalBalance, $payment->getPrincipalBalanceLeft());
            }
            $currentInterestPayment = $payment->getInterest();
            $currentPrincipalPayment = $payment->getPrincipal();
            $currentPrincipalBalance = $payment->getPrincipalBalanceLeft();
        }
        self::assertSame($expectedTotalPayments - 1, $paymentComparisonCount);
    }

    private function getPeriods(DateTime $startDate, int $numberOfPeriods): array
    {
        $dateProvider = new DateProvider(
            new ExactDayOfMonthStrategy(),
            new NeverHolidayProvider(),
            true
        );
        $schedule = new Schedule(
            $startDate,
            $numberOfPeriods,
            $dateProvider
        );
        return $schedule->generatePeriods();
    }
}
