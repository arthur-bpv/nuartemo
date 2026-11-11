<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasLegacyEvents = Schema::hasTable('events')
            && Schema::hasColumn('events', 'start')
            && ! Schema::hasColumn('events', 'start_at');

        if ($hasLegacyEvents) {
            if (Schema::hasTable('legacy_events')) {
                throw new RuntimeException('A tabela legacy_events já existe; a migração foi interrompida para evitar perda de dados.');
            }

            Schema::rename('events', 'legacy_events');
        }

        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 120);
            $table->char('color', 7);
            $table->dateTime('start_at')->index();
            $table->dateTime('end_at')->nullable();
            $table->timestamps();
        });

        if ($hasLegacyEvents) {
            DB::table('legacy_events')->orderBy('id')->chunkById(200, function ($legacyEvents): void {
                $timestamp = now();
                $events = $legacyEvents->map(fn (object $event): array => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'color' => $event->color,
                    'start_at' => $event->start,
                    'end_at' => $event->end,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ])->all();

                DB::table('events')->insert($events);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('events');

        if (Schema::hasTable('legacy_events')) {
            Schema::rename('legacy_events', 'events');
        }
    }
};
