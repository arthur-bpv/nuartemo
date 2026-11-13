<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    protected $table = 'reservations';

    protected $fillable = [
        'starts_at',
        'ends_at',
        'status',
        'purpose',
        'approved_by',
        'approved_at',
        'checked_out_at',
        'returned_at',
        'notes',
        'user_id',
        'key_id',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'approved_by' => 'integer',
            'approved_at' => 'datetime',
            'checked_out_at' => 'datetime',
            'returned_at' => 'datetime',
            'user_id' => 'integer',
            'key_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function key(): BelongsTo
    {
        return $this->belongsTo(Key::class);
    }
}
