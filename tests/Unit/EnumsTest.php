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

    public function test_extended_country_data_is_consistent(): void
    {
        $isoCodes = [];

        foreach (MobilePatterns::cases() as $country) {
            $isoCodes[] = $country->iso();

            foreach ([$country->example(), $country->example(true)] as $example) {
                $this->assertTrue($country->validate($example), "{$country->name}: {$example}");
            }

            $this->assertSame($country->example(true), $country->toE164($country->example()));
            $this->assertSame($country->example(), $country->toNational($country->example(true)));
        }

        $this->assertSame($isoCodes, array_values(array_unique($isoCodes)), 'ISO codes must be unique.');
    }

    public function test_lookups_by_iso_dial_code_and_alias(): void
    {
        $this->assertSame(MobilePatterns::IRAN, MobilePatterns::fromIso('ir'));
        $this->assertSame(MobilePatterns::UNITED_KINGDOM, MobilePatterns::fromIso('UK'));
        $this->assertSame(MobilePatterns::UNITED_KINGDOM, MobilePatterns::country('uk'));
        $this->assertNull(MobilePatterns::fromIso('ZZ'));
        $this->assertSame([MobilePatterns::IRAN], MobilePatterns::fromDialCode('+98'));
        $this->assertSame([MobilePatterns::USA, MobilePatterns::CANADA], MobilePatterns::fromDialCode(1));
        $this->assertSame([], MobilePatterns::fromDialCode(''));
    }

    public function test_detection_and_comparison_helpers(): void
    {
        $this->assertSame(MobilePatterns::UNITED_KINGDOM, MobilePatterns::detectCountry('+447400123456'));
        $this->assertSame([MobilePatterns::USA, MobilePatterns::CANADA], MobilePatterns::detectCountries('+12015550123'));
        $this->assertTrue(MobilePatterns::validateAny('+989123456789'));
        $this->assertFalse(MobilePatterns::validateAny('hello'));
        $this->assertTrue(MobilePatterns::validateAny('09123456789', ['iran', 'bogus']));
        $this->assertSame('+989123456789', MobilePatterns::e164('09123456789', 'iran'));
        $this->assertTrue(MobilePatterns::areSame('0912 345 6789', '+98 912 345 6789', 'iran'));
        $this->assertFalse(MobilePatterns::areSame('09123456789', '09123456789'), 'A national number needs a country.');
    }

    public function test_trunk_prefixes_other_than_zero_are_handled(): void
    {
        $this->assertTrue(MobilePatterns::RUSSIA->validate('89123456789'));
        $this->assertSame('79123456789', MobilePatterns::canonical('89123456789', 'RU'));
        $this->assertSame('36201234567', MobilePatterns::canonical('06201234567', 'hu'));
    }

    public function test_masking_and_options(): void
    {
        $this->assertSame('+989******789', MobilePatterns::mask('+989123456789'));
        $this->assertSame('***', MobilePatterns::mask('123'));
        $this->assertSame('Iran', MobilePatterns::options()['IR']);
        $this->assertSame("\u{1F1EE}\u{1F1F7}", MobilePatterns::IRAN->flag());
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
