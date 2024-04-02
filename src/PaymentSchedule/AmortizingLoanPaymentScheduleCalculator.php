<?php

namespace cog\LoanPaymentsCalculator\PaymentSchedule;

use cog\LoanPaymentsCalculator\Payment\Payment;

class AmortizingLoanPaymentScheduleCalculator implements PaymentScheduleCalculator
{
    private array $schedulePeriods;
    private float $principalAmount;
    private float $annualInterestRate;
    private int $numberOfPeriods;
    /** @var Payment[] $extraPayments */
    private array $extraPayments;

    public function __construct(
        array $schedulePeriods,
        float $principalAmount,
        float $annualInterestRate,
        array $extraPayments = []
    ) {
        $this->schedulePeriods = $schedulePeriods;
        $this->principalAmount = $principalAmount;
        $this->annualInterestRate = $annualInterestRate;
        $this->numberOfPeriods = count($schedulePeriods);
        $this->extraPayments = $extraPayments;
    }

    public static function withExtraPayments(
        array $schedulePeriods,
        float $principalAmount,
        float $annualInterestRate,
        array $extraPayments
    ): self {
        return new self(
            $schedulePeriods,
            $principalAmount,
            $annualInterestRate,
            $extraPayments
        );
    }

    /**
     * @return Payment[]
     */
    public function calculateSchedule(): array
    {
        $payments = [];
        $monthlyInterestRate = $this->annualInterestRate / 12 / 100; // Convert annual interest rate to monthly
        $monthlyPayment = $this->calculateMonthlyPayment($monthlyInterestRate);

        $remainingPrincipal = $this->principalAmount;
        foreach ($this->extraPayments as $extraPayment) {
            $remainingPrincipal -= $extraPayment->getPrincipal();
        }
        for ($i = 1; $i <= $this->numberOfPeriods; $i++) {
            if ($remainingPrincipal <= 0) {
                break;
            }
            $payment = new Payment($this->schedulePeriods[$i - 1]);
            $paymentInterest = $remainingPrincipal * $monthlyInterestRate;
            $payment->setInterest($paymentInterest);

            $paymentPrincipal = $monthlyPayment - $paymentInterest;
            $payment->setPrincipal($paymentPrincipal);

            $remainingPrincipal -= $paymentPrincipal;
            $payment->setPrincipalBalanceLeft($remainingPrincipal);

            $payments[] = $payment;
        }

        return $payments;
    }

    private function calculateMonthlyPayment(float $monthlyInterestRate): float
    {
        // Formula for calculating monthly payment for an amortizing loan
        return ($this->principalAmount * $monthlyInterestRate) / (1 - ((1 + $monthlyInterestRate) ** -$this->numberOfPeriods));
    }
}
