<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Notifications\CustomVerifyEmail;
use App\Notifications\CustomResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasPushSubscriptions;


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
        'user_name'

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

}
