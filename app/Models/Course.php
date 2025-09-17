<?php

namespace App\Models;

use App\Models\Other\Level;
use App\Models\Other\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Course extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'creator_id',
        'description',
        'level_id',
        'status_id',
    ];
    protected $hidden = ['enrolled_users'];
    // Define the relationship with the course creator
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    // Define the relationship with course administrators
    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_admin', 'course_id', 'admin_id');
    }

    // Define the relationship with enrolled users
    public function enrolledUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_enroll', 'course_id', 'user_id')->withTimestamps();
    }

    // Define the relationship with the course level
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    // Define the relationship with the course status
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    // Get all zones for the Course
    public function zones(): HasMany
    {
        return $this->hasMany(Zone::class);
    }

    // Define the relationship with course reviews
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Calculate the average stars and total reviews for the Course.
     *
     * @return array ['average_stars', 'total_reviews']
     */
    public function stars(): array
    {
        $totalReviews = $this->reviews()->count();
        $totalStars = $this->reviews()->sum('star');

        if ($totalReviews > 0) {
            $averageStars = $totalStars / $totalReviews;
            return [
                'average_stars' => $averageStars,
                'total_reviews' => $totalReviews,
            ];
        }

        return [
            'average_stars' => 0,
            'total_reviews' => 0,
        ];
    }

    public function isAdminForCourse()
    {
        $userId = Auth::id(); // Get the currently logged-in user's ID

        // Check if there's a record in the course_admin table for the given course_id and user_id
        return DB::table('course_admin')
            ->where('course_id', $this->id)
            ->where('admin_id', $userId)
            ->exists();
    }
    public function refreshZonesLevel() {
        $zones = $this->zones->sortBy('level');
        foreach ($zones as $zone) {
            $first = $zone->lastNodes()->map(function ($node) {
                return $node->children;
            })->flatten();
            foreach ($first as $f) {
                $childZone = $f->zone;
                if ($childZone->level > $zone->level +1 ) {
                    $childZone->level = $zone->level + 1;
                    $childZone->save();
                }
            }
        }
        return $zones;
    }

}
