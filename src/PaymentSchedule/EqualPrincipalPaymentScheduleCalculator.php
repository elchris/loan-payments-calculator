<?php
/**
 * @author: Vova Lando <vova.lando@gmail.com>
 * @package: LoanPaymentsCalculator
 * @subpackage:
 * @created: 05/09/2017 16:56
 */

namespace cog\LoanPaymentsCalculator\PaymentSchedule;

use cog\LoanPaymentsCalculator\Payment\Payment;
use cog\LoanPaymentsCalculator\Period\Period;

/**
 * Class EqualPrincipalPaymentScheduleCalculator
 * @package cog\LoanPaymentsCalculator\PaymentSchedule
 */
class EqualPrincipalPaymentScheduleCalculator implements PaymentScheduleCalculator
{
    /**
     * @var Period[]
     */
    private array $schedulePeriods;
    private float $principalAmount;
    private float $dailyInterestRate;
    private float $totalInterest;

    /**
     * PaymentSchedule constructor.
     * @param Period[] $schedulePeriods
     */
    public function __construct(
        array $schedulePeriods,
        float $principalAmount,
        float $dailyInterestRate
    ) {
        $this->schedulePeriods = $schedulePeriods;
        $this->principalAmount = $principalAmount;
        $this->dailyInterestRate = $dailyInterestRate;
        $this->totalInterest = 0.0;
    }

    public function getTotalInterest(): float
    {
        return $this->totalInterest;
    }

    public function setTotalInterest(float $totalInterest): void
    {
        $this->totalInterest = $totalInterest;
    }

    /**
     * @inheritdoc
     */
    public function calculateSchedule(): array
    {
        /**
         * @var Payment[] $payments
         */
        $payments = [];
        $numberOfPeriods = count($this->schedulePeriods);
        $paymentPrincipal = $this->principalAmount / $numberOfPeriods;
        $totalPrincipalToPay = $this->principalAmount;

        foreach ($this->schedulePeriods as $i => $iValue) {
            $payment = new Payment($this->schedulePeriods[$i]);
            // Payment principal
            $payment->setPrincipal($paymentPrincipal);
            // Payment interest
            $paymentInterest = $this->calculatePaymentInterest($totalPrincipalToPay, $this->dailyInterestRate, $payment->getPeriod()->daysLength);
            $payment->setInterest($paymentInterest);
            // Payment totals
            $totalPrincipalToPay -= $paymentPrincipal;
            $payment->setPrincipalBalanceLeft($totalPrincipalToPay);

            $payments[] = $payment;
            $this->totalInterest += $paymentInterest;
        }

        return $payments;
    }

    private function calculatePaymentInterest(float $remainingPrincipalAmount, float $dailyInterestRate, int $periodInDays): float
    {
        return $remainingPrincipalAmount * $dailyInterestRate * $periodInDays;
    }
}
