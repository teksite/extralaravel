<?php

namespace Teksite\Extralaravel\Tests\Feature;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Teksite\Extralaravel\Rules\CodeMeliRule;
use Teksite\Extralaravel\Rules\MobileRule;
use Teksite\Extralaravel\Rules\NeverPassRule;
use Teksite\Extralaravel\Rules\NoHtmlRule;
use Teksite\Extralaravel\Rules\PasswordRule;
use Teksite\Extralaravel\Tests\TestCase;

final class RulesTest extends TestCase
{
    private function validator(mixed $value, ValidationRule $rule, string $attribute = 'field'): \Illuminate\Validation\Validator
    {
        return Validator::make([$attribute => $value], [$attribute => [$rule]]);
    }

    public function test_national_code_accepts_valid_codes_in_any_digit_script(): void
    {
        $this->assertTrue($this->validator('0499370899', new CodeMeliRule())->passes());
        $this->assertTrue($this->validator('۰۴۹۹۳۷۰۸۹۹', new CodeMeliRule())->passes());
    }

    public function test_national_code_rejects_bad_checksums_repeated_digits_and_wrong_types(): void
    {
        $this->assertTrue($this->validator('0499370898', new CodeMeliRule())->fails());
        $this->assertTrue($this->validator('1111111111', new CodeMeliRule())->fails());
        $this->assertTrue($this->validator('12345', new CodeMeliRule())->fails());
        $this->assertTrue($this->validator(['0499370899'], new CodeMeliRule())->fails());
    }

    public function test_national_code_error_uses_the_attribute_name(): void
    {
        $message = $this->validator('1', new CodeMeliRule(), 'national_code')->errors()->first('national_code');

        $this->assertSame('The national code must be a valid Iranian national code.', $message);
    }

    public function test_mobile_rule_defaults_to_iran(): void
    {
        $this->assertTrue($this->validator('09123456789', new MobileRule())->passes());
        $this->assertTrue($this->validator('۰۹۱۲۳۴۵۶۷۸۹', new MobileRule())->passes());
        $this->assertTrue($this->validator('abc', new MobileRule())->fails());
        $this->assertTrue($this->validator(str_repeat('9', 100), new MobileRule())->fails());
    }

    public function test_mobile_rule_supports_multiple_countries_and_reports_them(): void
    {
        $rule = new MobileRule(['USA', 'iran', 'USA']);

        $this->assertTrue($this->validator('09123456789', $rule)->passes());
        $this->assertTrue($this->validator('2025550123', $rule)->passes());

        $message = $this->validator('12', new MobileRule('usa'), 'phone')->errors()->first('phone');
        $this->assertStringContainsString('United States', $message);
    }

    public function test_mobile_rule_rejects_unknown_countries(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new MobileRule('atlantis');
    }

    public function test_no_html_rule(): void
    {
        $this->assertTrue($this->validator('plain text', new NoHtmlRule())->passes());
        $this->assertTrue($this->validator('<b>bold</b>', new NoHtmlRule())->fails());
        $this->assertTrue($this->validator('<script>alert(1)</script>', new NoHtmlRule())->fails());
    }

    public function test_never_pass_rule_always_fails_for_present_values(): void
    {
        $this->assertTrue($this->validator('anything', new NeverPassRule())->fails());
    }

    public function test_password_rule_accepts_a_strong_password(): void
    {
        $this->assertTrue($this->validator('Abcdef1!', new PasswordRule())->passes());
        // Underscores and other symbols outside the old fixed list count as symbols.
        $this->assertTrue($this->validator('Abcdef1_', new PasswordRule())->passes());
    }

    public function test_password_rule_reports_each_failed_requirement_separately(): void
    {
        $errors = $this->validator('abc', new PasswordRule(), 'password')->errors()->get('password');

        $this->assertContains('The password must be at least 8 characters.', $errors);
        $this->assertContains('The password must contain at least one uppercase letter.', $errors);
        $this->assertContains('The password must contain at least one number.', $errors);
        $this->assertContains('The password must contain at least one symbol.', $errors);
        $this->assertNotContains('The password must contain at least one lowercase letter.', $errors);
    }

    public function test_password_rule_does_not_treat_persian_letters_as_symbols(): void
    {
        $this->assertTrue($this->validator('Abcdef1ی', new PasswordRule())->fails());
    }

    public function test_password_rule_enforces_the_maximum_length(): void
    {
        $this->assertTrue($this->validator(str_repeat('Aa1!', 40), new PasswordRule())->fails());
        $this->assertTrue($this->validator(str_repeat('Aa1!', 40), new PasswordRule(8, 200))->passes());
    }

    public function test_password_limits_follow_the_configuration(): void
    {
        config(['extralaravel.password.min_length' => 12]);

        $this->assertTrue($this->validator('Abcdef1!', new PasswordRule())->fails());
        $this->assertTrue($this->validator('Abcdef1!Abcdef1!', new PasswordRule())->passes());
    }
}
