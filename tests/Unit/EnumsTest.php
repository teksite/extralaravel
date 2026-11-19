<?php

namespace Teksite\Extralaravel\Tests\Unit;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Teksite\Extralaravel\Enums\Currencies;
use Teksite\Extralaravel\Enums\MobilePatterns;
use Teksite\Extralaravel\Enums\TimeZones;

final class EnumsTest extends TestCase
{
    public function test_mobile_numbers_are_normalized_and_validated(): void
    {
        $this->assertSame('09123456789', MobilePatterns::normalize('۰۹۱۲۳۴۵۶۷۸۹'));
        $this->assertTrue(MobilePatterns::IRAN->validate('09123456789'));
        $this->assertTrue(MobilePatterns::IRAN->validate('+989123456789'));
        $this->assertTrue(MobilePatterns::IRAN->validate('00989123456789'));
        $this->assertFalse(MobilePatterns::IRAN->validate('abc'));
    }

    public function test_canonical_numbers(): void
    {
        $this->assertSame('989123456789', MobilePatterns::canonical('09123456789', 'iran'));
        $this->assertSame('989123456789', MobilePatterns::canonical('+989123456789'));
        $this->assertNull(MobilePatterns::canonical('09123456789'));
    }

    public function test_country_lookup_is_forgiving(): void
    {
        $this->assertSame(MobilePatterns::UNITED_KINGDOM, MobilePatterns::country('united kingdom'));
        $this->assertNull(MobilePatterns::country('atlantis'));
    }

    public function test_every_timezone_case_resolves_a_standard_offset(): void
    {
        foreach (TimeZones::cases() as $case) {
            $this->assertNotNull(TimeZones::getOffset($case), "No offset for {$case->name}");
        }
    }

    public function test_misspelled_timezone_cases_remain_available_as_aliases(): void
    {
        $this->assertSame(TimeZones::Canada_East_Saskatchewan, TimeZones::Canada_East_Saskatchewa);
        $this->assertSame(TimeZones::Antarctica_DumontDUrville, TimeZones::Antarctica_DumontDUrvil);
    }

    public function test_current_offset_follows_daylight_saving_time(): void
    {
        $this->assertSame('+3:30', TimeZones::getOffset('Asia/Tehran'));
        $this->assertSame('-8:00', TimeZones::getOffset('America/Los_Angeles'));
        $this->assertSame('-7:00', TimeZones::getCurrentOffset('America/Los_Angeles', new DateTimeImmutable('2026-07-01')));
        $this->assertSame('-8:00', TimeZones::getCurrentOffset('America/Los_Angeles', new DateTimeImmutable('2026-01-15')));
        $this->assertNull(TimeZones::getCurrentOffset('Mars/Base'));
    }

    public function test_currency_names_are_valid_utf8(): void
    {
        foreach (Currencies::cases() as $case) {
            $this->assertTrue(mb_check_encoding((string) $case->value, 'UTF-8'), "Invalid encoding in {$case->name}");
            $this->assertStringNotContainsString('Ã', (string) $case->value);
        }
    }
}
