<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    use HasFactory;

    protected $fillable = [
        'opname_code',
        'status',
        'adjustment_type',
        'opname_date',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
        'rejection_note',
    ];

    protected $casts = [
        'opname_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(StockOpnameItem::class);
    }
    public function adjustments()
    {
        return $this->hasMany(StockAdjustment::class);
    }
}
