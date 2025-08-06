<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'UserCode',
        'FullName',
        'Gender',
        'BirthDate',
        'Address',
        'Username',
        'Password',
        'role_id',
    ];

    protected $hidden = [
        'Password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'Password' => 'hashed',
        ];
    }

    public function getAuthPassword()
    {
        return $this->Password;
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function predictions()
    {
        return $this->hasMany(Prediction::class);
    }
}
