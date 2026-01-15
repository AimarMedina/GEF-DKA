<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    public $incrementing = false;
    protected $fillable = [
        'nombre',
        'apellidos',
        'email',
        'n_tel',
        'password',
        'tipo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'contrasenna',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(){
        static::creating(function ($user){
            $start = match ($user->tipo){
                'admin' => 10000,
                'alumno' => 20000,
                'tutor' => 30000,
                'instructor' => 40000
            };

            $lastNumber = self::where('tipo', $user->tipo)->max('id');

            $user->id = $lastNumber ? $lastNumber +1 : $start +1;
        });
        //  static::created(function ($user) {
        //      match ($user->tipo) {
        //          'alumno'     => Alumno::create(['ID_Usuario' => $user->id]),
        //          'instructor' => Instructor::create(['ID_Usuario' => $user->id]),
        //          'tutor'      => Tutor::create(['ID_Usuario' => $user->id]),
        //          default      => null,
        //      };
        //  });

    }

    public function alumno(){
        return $this->hasOne(Alumno::class,'ID_Usuario');
    }
    public function tutor(){
        return $this->hasOne(Tutor::class,'ID_Usuario');
    }
    public function instructor(){
        return $this->hasOne(Instructor::class,'ID_Usuario');
    }
}
