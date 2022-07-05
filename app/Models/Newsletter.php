<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class Newsletter extends Model
{
    use HasFactory;
    
    protected $table = 'nft_watcher_newsletters';
    protected $connection = 'mongodb';


    protected $fillable = [
        'id',
        'email',
        'updated_at',
        'created_at',
    ];
}
