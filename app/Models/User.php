<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function createdTransactions()
    {
        return $this->hasMany(StockTransaction::class, 'created_by');
    }

    public function approvedTransactions()
    {
        return $this->hasMany(StockTransaction::class, 'approved_by');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function hasAnyRole(string ...$roles): bool
    {
        return in_array($this->role, $roles);
    }
    public function approvedStockAdjustments()
    {
        return $this->hasMany(
            StockAdjustment::class,
            'approved_by'
        );
    }
}