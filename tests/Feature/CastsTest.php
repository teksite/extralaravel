<?php

namespace Teksite\Extralaravel\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Teksite\Extralaravel\Casts\IpCast;
use Teksite\Extralaravel\Casts\JalaliDateCast;
use Teksite\Extralaravel\Casts\SlugCast;
use Teksite\Extralaravel\Tests\TestCase;

final class CastsTest extends TestCase
{
    private function model(): Model
    {
        return new class () extends Model {
            protected $guarded = [];

            protected $casts = [
                'slug'    => SlugCast::class,
                'ip'      => IpCast::class,
                'born_at' => JalaliDateCast::class,
            ];
        };
    }

    public function test_slug_cast_slugifies_text_and_keeps_persian(): void
    {
        $model = $this->model();

        $model->slug = 'My Awesome Post';
        $this->assertSame('my-awesome-post', $model->getAttributes()['slug']);

        $model->slug = 'سلام دنیا';
        $this->assertSame('سلام-دنیا', $model->getAttributes()['slug']);

        $model->slug = null;
        $this->assertNull($model->getAttributes()['slug']);
    }

    public function test_slug_cast_rejects_non_strings(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->model()->slug = ['not', 'a', 'string'];
    }

    public function test_ip_cast_stores_valid_addresses_only(): void
    {
        $model = $this->model();

        $model->ip = '203.0.113.5';
        $this->assertSame('203.0.113.5', $model->ip);

        $model->ip = '2001:db8::1';
        $this->assertSame('2001:db8::1', $model->ip);
    }

    public function test_ip_cast_rejects_arbitrary_text(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->model()->ip = "1.1.1.1\nINJECTED";
    }

    public function test_jalali_cast_converts_jalali_input_to_gregorian_when_the_locale_matches(): void
    {
        app()->setLocale('fa');
        $model = $this->model();

        $model->born_at = '1403/05/10';
        $this->assertSame('2024-07-31', $model->getAttributes()['born_at']);

        $model->born_at = '۱۴۰۳/۰۵/۱۰ 14:30:00';
        $this->assertSame('2024-07-31 14:30:00', $model->getAttributes()['born_at']);
    }

    public function test_jalali_cast_leaves_gregorian_input_untouched(): void
    {
        app()->setLocale('fa');
        $model = $this->model();

        $model->born_at = '2024-07-31 14:30:00';

        $this->assertSame('2024-07-31 14:30:00', $model->getAttributes()['born_at']);
    }

    public function test_jalali_cast_rejects_impossible_jalali_dates(): void
    {
        app()->setLocale('fa');

        $this->expectException(InvalidArgumentException::class);

        $this->model()->born_at = '1403/13/40';
    }

    public function test_jalali_cast_formats_stored_dates_as_jalali_for_the_configured_locale(): void
    {
        app()->setLocale('fa');
        $model = $this->model();

        $model->setRawAttributes(['born_at' => '2024-07-31 14:30:00']);
        $this->assertSame('1403/05/10 14:30:00', $model->born_at);

        $model->setRawAttributes(['born_at' => '2024-07-31']);
        $this->assertSame('1403/05/10', $model->born_at);
    }

    public function test_jalali_cast_does_nothing_for_other_locales(): void
    {
        app()->setLocale('en');
        $model = $this->model();

        $model->setRawAttributes(['born_at' => '2024-07-31 14:30:00']);

        $this->assertSame('2024-07-31 14:30:00', $model->born_at);
    }

    public function test_jalali_cast_still_honors_the_legacy_misspelled_config_key(): void
    {
        config(['extralaravel.jalalit_date_locales' => ['en']]);
        app()->setLocale('en');
        $model = $this->model();

        $model->setRawAttributes(['born_at' => '2024-07-31 14:30:00']);

        $this->assertSame('1403/05/10 14:30:00', $model->born_at);
    }
}
