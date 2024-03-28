<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Notifications\CustomVerifyEmail;
use App\Notifications\CustomResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone_number',
        'profile_picture',
        'user_name',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @param  string  $id
     * @return void
     */

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean'
    ];

    public function sendEmailVerificationNotification()
    {

        $this->notify(new CustomVerifyEmail());
    }


    public function sendPasswordResetNotification($token)
    {
        $url = 'http://localhost:5173/reset-password?token=' . $token;

        // You can pass additional data to your notification if needed
        $this->notify(new CustomResetPassword($url));
    }


    public function Jobs()
    {
        return $this->hasMany('App\Models\MezmurModel\Job'::class);
    }


    public function Offers()
    {
        return $this->hasMany('App\Models\MezmurModel\Offer'::class);
    }

    public function Events()
    {
        return $this->hasMany('App\Models\MezmurModel\Event'::class);
    }


    public function artistProfile()
    {
        return $this->hasOne(ArtistProfile::class);
    }
}