<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Refer extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'referer_id',
        'registered_user_id',

    ];

    public function getRegisteredUser()
    {
        $this->belongsTo(User::class, 'registered_user_id', 'id');
    }
}