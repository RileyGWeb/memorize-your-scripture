<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\Auditable;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'login_streak',
        'last_login_date',
        'verified_user',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
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
            'last_login_date' => 'date',
            'verified_user' => 'boolean',
        ];
    }

    /**
     * Get the memorized verses for the user.
     */
    public function memoryBank()
    {
        return $this->hasMany(MemoryBank::class);
    }

    /**
     * Get the memorize later items for the user.
     */
    public function memorizeLater()
    {
        return $this->hasMany(MemorizeLater::class);
    }

    /**
     * Update the user's login streak based on current date.
     */
    public function updateLoginStreak(): void
    {
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        
        // If this is the first login ever
        if ($this->last_login_date === null) {
            $this->login_streak = 1;
            $this->last_login_date = $today;
        }
        // If last login was yesterday, increment streak
        elseif ($this->last_login_date && $this->last_login_date->toDateString() === $yesterday) {
            $this->login_streak += 1;
            $this->last_login_date = $today;
        }
        // If last login was today, don't change anything
        elseif ($this->last_login_date && $this->last_login_date->toDateString() === $today) {
            // No change needed
            return;
        }
        // If last login was more than 1 day ago, reset streak
        else {
            $this->login_streak = 1;
            $this->last_login_date = $today;
        }
        
        $this->save();
    }

    /**
     * Get the login streak for display purposes.
     * Returns null if streak is 0 or 1 (don't show streaks less than 2).
     */
    public function getLoginStreakForDisplay(): ?int
    {
        return $this->login_streak >= 2 ? $this->login_streak : null;
    }

    /**
     * Mark the user as verified.
     * This can be called when they verify email or memorize their first verse.
     */
    public function markAsVerified(): void
    {
        if (!$this->verified_user) {
            $this->verified_user = true;
            $this->save();
        }
    }
}
