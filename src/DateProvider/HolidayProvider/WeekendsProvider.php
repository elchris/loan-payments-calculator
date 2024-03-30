<?php
/**
 * @author: Vova Lando <vova.lando@gmail.com>
 * @package: LoanPaymentsCalculator
 * @subpackage: DateProvider
 * @created: 14/06/2017 15:35
 */

namespace cog\LoanPaymentsCalculator\DateProvider\HolidayProvider;

use DateTime;

/**
 * Class WeekendsProvider - provides functionality to determine weekend based on php DateTime
 */
class WeekendsProvider implements HolidayProvider
{
    public function isHoliday(DateTime $date): bool
    {
        return $date->format('N') >= 5;
    }
}
