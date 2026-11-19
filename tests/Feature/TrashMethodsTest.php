<?php

namespace Teksite\Extralaravel\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use LogicException;
use Teksite\Extralaravel\Tests\TestCase;
use Teksite\Extralaravel\Traits\TrashMethods;

final class TrashMethodsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('trash_posts', static function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->softDeletes();
        });

        foreach (['a', 'b', 'c', 'd'] as $title) {
            TrashPost::create(['title' => $title]);
        }

        // Trash everything except the first post.
        TrashPost::whereKeyNot(1)->get()->each->delete();
    }

    public function test_counts_and_paginates_only_trashed_records(): void
    {
        $manager = new TrashPostManager();

        $this->assertSame(3, $manager->trashCount());
        $this->assertSame(3, $manager->getTrashes(2)->total());
        $this->assertCount(2, $manager->getTrashes(2)->items());
    }

    public function test_restore_one_and_many(): void
    {
        $manager = new TrashPostManager();

        $this->assertSame(1, $manager->restoreOne(2));
        $this->assertSame(1, $manager->restore([3]));
        $this->assertSame(0, $manager->restore([1]), 'Active records are not part of the trash.');
        $this->assertSame(1, $manager->trashCount());
    }

    public function test_ids_are_normalized_before_querying(): void
    {
        $manager = new TrashPostManager();

        $this->assertSame(0, $manager->restore([null, '']));
        $this->assertSame(3, $manager->trashCount());
    }

    public function test_large_id_lists_are_chunked(): void
    {
        $manager = new class () extends TrashPostManager {
            protected function trashChunkSize(): int
            {
                return 1;
            }
        };

        $this->assertSame(3, $manager->restore([2, 3, 4]));
    }

    public function test_wipe_one_and_all(): void
    {
        $manager = new TrashPostManager();

        $this->assertSame(1, $manager->wipeOne(2));
        $this->assertSame(2, $manager->wipeAll());
        $this->assertSame(0, $manager->trashCount());
        $this->assertSame(1, TrashPost::count());
    }

    public function test_restore_all(): void
    {
        $this->assertSame(3, (new TrashPostManager())->restoreAll());
        $this->assertSame(4, TrashPost::count());
    }

    public function test_restore_one_does_not_call_an_overridden_restore_action(): void
    {
        $manager = new class () extends TrashPostManager {
            public function restore(int|string|array $ids = []): int
            {
                return 999;
            }
        };

        $this->assertSame(1, $manager->restoreOne(2));
        $this->assertSame(2, $manager->trashCount());
    }

    public function test_restore_with_events_fires_model_events_while_plain_restore_does_not(): void
    {
        $fired = 0;
        TrashPost::restored(static function () use (&$fired): void {
            $fired++;
        });

        $manager = new TrashPostManager();

        $this->assertSame(1, $manager->restoreOne(2));
        $this->assertSame(0, $fired, 'The bulk restore must not fire events.');

        $this->assertSame(1, $manager->restoreOneWithEvents(3));
        $this->assertSame(1, $fired);

        $this->assertSame(1, $manager->restoreAllWithEvents());
        $this->assertSame(2, $fired);
        $this->assertSame(0, $manager->trashCount());

        TrashPost::flushEventListeners();
    }

    public function test_wipe_with_events_fires_model_events_and_only_touches_trashed_records(): void
    {
        $fired = 0;
        TrashPost::forceDeleted(static function () use (&$fired): void {
            $fired++;
        });

        $manager = new TrashPostManager();

        $this->assertSame(1, $manager->wipeOneWithEvents(2));
        $this->assertSame(0, $manager->wipeWithEvents([1]), 'Active records are not part of the trash.');
        $this->assertSame(2, $manager->wipeAllWithEvents());
        $this->assertSame(3, $fired);
        $this->assertSame(1, TrashPost::count());

        TrashPost::flushEventListeners();
    }

    public function test_events_variants_ignore_empty_id_lists(): void
    {
        $manager = new TrashPostManager();

        $this->assertSame(0, $manager->restoreWithEvents([null, '']));
        $this->assertSame(3, $manager->trashCount());
    }

    public function test_page_size_is_capped_and_never_below_one(): void
    {
        $manager = new TrashPostManager();

        $this->assertSame(250, $manager->getTrashes(100000)->perPage());
        $this->assertSame(1, $manager->getTrashes(0)->perPage());
    }

    public function test_models_without_soft_deletes_are_rejected(): void
    {
        $manager = new class () {
            use TrashMethods;

            protected function getModelClass(): string
            {
                return PlainModel::class;
            }
        };

        $this->expectException(LogicException::class);

        $manager->trashCount();
    }
}

class TrashPost extends Model
{
    use SoftDeletes;

    protected $table = 'trash_posts';

    public $timestamps = false;

    protected $guarded = [];
}

class PlainModel extends Model
{
}

class TrashPostManager
{
    use TrashMethods;

    protected function getModelClass(): string
    {
        return TrashPost::class;
    }
}
