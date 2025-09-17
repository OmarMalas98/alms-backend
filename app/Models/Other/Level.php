<?php

namespace App\Models\Other;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;
    protected $fillable=['name','description'];
    protected $hidden = ['id','created_at', 'updated_at'];

    public function courses(){
        return $this->hasMany(Course::class);
     }

}
