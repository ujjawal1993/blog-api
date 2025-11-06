<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory; 

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;
    protected $appends = [];
    protected $fillable = ['user_id','title','description','image'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function getImageAttribute($value)
    {
        if ($value) {
            return url('storage/' . $value);
        }
        return url('images/default-blog.jpg');
    }

    public function likes(){
        return $this->morphMany(Like::class, 'likeable');
    }
}
