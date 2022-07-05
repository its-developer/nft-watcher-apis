<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class Collections extends Model
{
    use HasFactory;
    
    protected $table = 'nft_watcher_collections';
    protected $connection = 'mongodb';


    protected $fillable = [
        'id',
        'slug',
        'name',
        'description',
        'image_url',
        'featured',
        'stats',
        'collection_name',
        'updated_at',
        'created_at',
    ];
}
