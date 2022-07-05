<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class SectionsContent extends Model
{
    use HasFactory;
    
    protected $table = 'sections_content';
    protected $connection = 'mongodb';

    protected $fillable = [
        'id',
        'name',
        'key',
        'content',
        'updated_at',
        'created_at',
    ];
}
