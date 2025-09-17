<?php

namespace App\Models;

use App\Models\Components\Page;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checkpoint extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'zone_id', 'page_id'];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
