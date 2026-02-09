<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserImport extends Model
{
    protected $table = 'user_imports';

    protected $fillable = [
        'user_id',
        'total_users',
        'successful_users',
        'failed_users',
        'errors',
        'original_filename',
    ];

    protected $casts = [
        'errors' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
