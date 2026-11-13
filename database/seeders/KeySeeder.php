<?php

namespace Database\Seeders;

use App\Models\Key;
use Illuminate\Database\Seeder;

class KeySeeder extends Seeder
{
    public function run(): void
    {
        $keys = [
            [
                'name' => 'Chave da sala principal',
                'description' => 'Acesso à sala principal do NUARTE.',
                'active' => true,
            ],
            [
                'name' => 'Chave do depósito',
                'description' => 'Acesso ao depósito.',
                'active' => true,
            ],
        ];

        foreach ($keys as $key) {
            Key::query()->updateOrCreate(
                ['name' => $key['name']],
                [
                    'description' => $key['description'],
                    'active' => $key['active'],
                ],
            );
        }
    }
}