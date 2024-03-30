<?php

/**
 * @author: Vova Lando <vova.lando@gmail.com>
 * @package: LoanPaymentsCalculator
 * @subpackage: Tests
 * @created: 14/06/2017 16:01
 */

namespace cog\LoanPaymentsCalculator\DateProvider\HolidayProvider;

use DateTime;
use PHPUnit\Framework\TestCase;

class WeekendsProviderTest extends TestCase
{
    public function testSaturdayIsHoliday(): void
    {
        $date = new DateTime('2017-06-17');
        $weekendsProvider = new WeekendsProvider();
        $this->assertTrue($weekendsProvider->isHoliday($date));
    }

    public function testSundayIsHoliday(): void
    {
        $date = new DateTime('2017-06-18');
        $weekendsProvider = new WeekendsProvider();
        $this->assertTrue($weekendsProvider->isHoliday($date));
    }

    public function testMondayIsNotHoliday(): void
    {
        $date = new DateTime('2017-06-19');
        $weekendsProvider = new WeekendsProvider();
        $this->assertFalse($weekendsProvider->isHoliday($date));
    }
}
