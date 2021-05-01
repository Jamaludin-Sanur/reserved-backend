<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class News extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'description', 'urlImage'];

    public function user()
    {
      return $this->belongsTo(User::class);
    }
}
