<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class AdvertisePricingSlider extends Model
{
    use HasFactory;
    
    protected $table = 'nft_watcher_advertise_pricing_slider';
    protected $connection = 'mongodb';


    protected $fillable = [
        'id',
        'image_url',
        'updated_at',
        'created_at',
    ];
}
