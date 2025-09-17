<?php

namespace App\Models;

use App\Models\Other\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    // Define the fields that can be mass-assigned
    protected $fillable = [
        'name', 'email', 'password', 'role_id'
    ];

    // Define the fields that should be hidden when serialized
    protected $hidden = [
        'password',
    ];

    public function courses()
    {
        return $this->hasMany(Course::class,'creator_id');
    }

    public function role(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function questionAttempts()
    {
        return $this->hasMany(QuestionAttempt::class);
    }

    public function achievedObjectives()
    {
        return $this->hasMany(AchievedObjective::class);
    }

    public function achievedZones()
    {
        return $this->hasMany(AchievedZones::class);
    }
    /**
     * Define the relationship with enrolled courses.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function enrolledCourses()
    {
        return $this->belongsToMany(Course::class, 'course_enroll', 'user_id', 'course_id')->withTimestamps();
    }

    /**
     * The finishedCourses that belong to the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function finishedCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class,'finished_courses', 'user_id', 'course_id')->withTimestamps();
    }

    // Return the primary key of the user for the JWT identifier
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    // Return any custom claims to include in the JWT
    public function getJWTCustomClaims()
    {
        return [];
    }
    public function coursesCount()
    {
        $courses = $this->courses;
        $coursesCount = $courses->count();
        return $coursesCount;

    }

    public function usersCount()
    {
        $courseIds = $this->Courses->pluck('id')->toArray();

        return User::whereIn('id', function ($query) use ($courseIds) {
            $query->select('user_id')
                ->from('course_enroll')
                ->whereIn('course_id', $courseIds)
                ->distinct();
        })->count();
    }

    public function enrolledUsers()
    {
        $courseIds = $this->Courses->pluck('id')->toArray();

        return User::whereIn('id', function ($query) use ($courseIds) {
            $query->select('user_id')
                ->from('course_enroll')
                ->whereIn('course_id', $courseIds)
                ->distinct();
        })->get();
    }
}
