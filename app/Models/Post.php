<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    public $timestamp=false;
    protected $table = 'posts';
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'digital_data'
    ];
    use HasFactory;
}
