<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class ProjectPage extends Model
{
    use HasFactory;
    
    protected $table = 'nft_watcher_project_page';
    protected $connection = 'mongodb';


    protected $fillable = [
        'id',
        'title',
        'updated_at',
        'created_at',
    ];
}
