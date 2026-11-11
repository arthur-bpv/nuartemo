<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LegacyDatabaseMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_events_are_preserved_and_imported(): void
    {
        Schema::drop('events');
        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('color');
            $table->dateTime('start');
            $table->dateTime('end')->nullable();
        });

        DB::table('events')->insert([
            'id' => 42,
            'title' => 'Evento do sistema antigo',
            'color' => '#228B22',
            'start' => '2026-11-20 14:00:00',
            'end' => '2026-11-20 16:00:00',
        ]);

        $migration = require database_path('migrations/2026_11_11_000000_create_events_table.php');
        $migration->up();

        $this->assertTrue(Schema::hasTable('legacy_events'));
        $this->assertDatabaseHas('events', [
            'id' => 42,
            'title' => 'Evento do sistema antigo',
            'start_at' => '2026-11-20 14:00:00',
            'end_at' => '2026-11-20 16:00:00',
        ]);
    }
}
