<?php

namespace cog\LoanPaymentsCalculator\PaymentSchedule;

use cog\LoanPaymentsCalculator\DateProvider\DateDetermineStrategy\ExactDayOfMonthStrategy;
use cog\LoanPaymentsCalculator\DateProvider\DateProvider;
use cog\LoanPaymentsCalculator\DateProvider\HolidayProvider\NeverHolidayProvider;
use cog\LoanPaymentsCalculator\Payment\Payment;
use cog\LoanPaymentsCalculator\Period\Period;
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
                353, //expectedTotalPayments
            ],
            [
                new DateTime('2024-06-01'), //loanStartDate
                15500.00, //principal
                48, //numberOfPeriods
                18.00, //annualInterestRate
                455.31249392652, //expectedTotalMonthlyPayment,
                36, //expectedTotalPayments
            ],
            [
                new DateTime('2024-06-01'), //loanStartDate
                16585.00, //principal
                48, //numberOfPeriods
                18.00, //annualInterestRate
                487.18436850137, //expectedTotalMonthlyPayment
                37, //expectedTotalPayments
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

        $paymentOne = new Payment(
            new Period(
                new DateTime('2024-06-10'),
                new DateTime('2024-06-10')
            )
        );
        $paymentOne->setPrincipal(1000.00);
        $paymentOne->setInterest(0.00);
        $paymentTwo = new Payment(
            new Period(
                new DateTime('2024-06-20'),
                new DateTime('2024-06-20')
            )
        );
        $paymentTwo->setPrincipal(2000.00);
        $paymentTwo->setInterest(0.00);

        // When
        $calculator = AmortizingLoanPaymentScheduleCalculator::withExtraPayments(
            $schedulePeriods,
            $principalAmount,
            $annualInterestRate,
            [
                $paymentOne,
                $paymentTwo,
            ]
        );
        $payments = $calculator->calculateSchedule();

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
        if (!$expectedTotalPayments) {
            $expectedTotalPayments = $numberOfPeriods;
        }
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
        self::assertSame($expectedTotalPayments - 1, $count);
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
