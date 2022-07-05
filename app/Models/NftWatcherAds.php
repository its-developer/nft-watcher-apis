<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class NftWatcherAds extends Model
{
    use HasFactory;
    
    protected $table = 'nft_watcher_ads';
    protected $connection = 'mongodb';


    protected $fillable = [
        'id',
        'name',
        'image',
        'redirect_to',
        'updated_at',
        'created_at',
    ];
}
