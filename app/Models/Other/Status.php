<?php

namespace App\Models\Other;

use App\Models\Assignment;
use App\Models\Content\Lesson;
use App\Models\Content\Module;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;
    protected $fillable=['name'];

    public function courses(){
        return $this->hasMany(Course::class);
     }

     public function modules(){
        return $this->hasMany(Module::class);
     }

     public function lessons(){
        return $this->hasMany(Lesson::class);
     }
    public function assessments(){
        return $this->hasMany(Assignment::class);
    }
}
