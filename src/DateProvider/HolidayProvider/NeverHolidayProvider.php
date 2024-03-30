<?php
/**
 * @author: Vova Lando <vova.lando@gmail.com>
 * @package: LoanPaymentsCalculator
 * @subpackage:
 * @created: 07/09/2017 13:52
 */

namespace cog\LoanPaymentsCalculator\DateProvider\HolidayProvider;

use DateTime;

class NeverHolidayProvider implements HolidayProvider
{
    /**
     * @codeCoverageIgnore
     */
    public function isHoliday(DateTime $date): bool
    {
        return false;
    }
}
