<?php
/**
 * @author: Vova Lando <vova.lando@gmail.com>
 * @package: LoanPaymentsCalculator
 * @subpackage: Payment
 * @created: 05/09/2017 10:55
 */

namespace cog\LoanPaymentsCalculator\Payment;

use cog\LoanPaymentsCalculator\Period\Period;

class Payment
{
    /**
     * @var Period
     */
    private Period $period;

    /**
     * @var float
     */
    private float $principal;

    /**
     * @var float
     */
    private float $interest;
    private mixed $fees;
    private float $principalBalanceLeft;

    /**
     * Payment constructor.
     * @param Period $period
     */
    public function __construct(Period $period)
    {
        $this->period = $period;
    }

    public function getPeriod(): Period
    {
        return $this->period;
    }

    public function setPeriod(Period $period): void
    {
        $this->period = $period;
    }

    public function getPrincipal(): float
    {
        return $this->principal;
    }

    public function setPrincipal(float $principal): void
    {
        $this->principal = $principal;
    }

    public function getInterest(): float
    {
        return $this->interest;
    }

    public function setInterest(float $interest): void
    {
        $this->interest = $interest;
    }

    public function getFees(): mixed
    {
        return $this->fees;
    }

    public function setFees(mixed $fees): void
    {
        $this->fees = $fees;
    }

    public function getPrincipalBalanceLeft(): float
    {
        return $this->principalBalanceLeft;
    }

    public function setPrincipalBalanceLeft(float $principalBalanceLeft): void
    {
        $this->principalBalanceLeft = $principalBalanceLeft;
    }
}
