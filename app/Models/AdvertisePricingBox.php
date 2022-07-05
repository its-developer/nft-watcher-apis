<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class AdvertisePricingBox extends Model
{
    use HasFactory;
    
    protected $table = 'nft_watcher_advertise_pricing_box';
    protected $connection = 'mongodb';


    protected $fillable = [
        'id',
        'title',
        'price',
        'description',
        'redirect_url',
        'updated_at',
        'created_at',
    ];
}
