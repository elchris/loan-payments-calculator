<?php
/**
 * @author: Vova Lando <vova.lando@gmail.com>
 * @package: LoanPaymentsCalculator
 * @subpackage: DateProvider
 * @created: 14/06/2017 15:04
 */

namespace cog\LoanPaymentsCalculator\DateProvider\DateDetermineStrategy;

use DateTime;

/**
 * Interface DateDetermineStrategyInterface
 */
interface DateDetermineStrategyInterface
{
    public function calculateNextDate(DateTime $startDate): DateTime;
}
