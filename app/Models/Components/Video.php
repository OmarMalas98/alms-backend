<?php

namespace App\Models\Components;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;
    protected $fillable = ['component_id','url'];
    protected $hidden = ['id'];

    public function component()
    {
        return $this->belongsTo(Component::class);
    }
}
