<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\CourseEnrolled;
use App\Models\Course;
use Illuminate\Support\Facades\DB;

class EnrolledInCourse implements Rule
{
    protected $courseId;

    public function __construct($courseId)
    {
        $this->courseId = $courseId;
    }

    public function passes($attribute, $value)
    {
        $userId = auth()->user()->id;

        // Check if the student is enrolled in the course
        return DB::table('course_enroll')
            ->where('user_id', '=', $userId)
            ->where('course_id', '=', $this->courseId)
            ->exists();
    }

    public function message()
    {
        return 'The student is not enrolled in the selected course.';
    }
}
