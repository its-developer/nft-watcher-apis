<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class AdvertiseFAQ extends Model
{
    use HasFactory;
    
    protected $table = 'nft_watcher_advertise_faqs';
    protected $connection = 'mongodb';


    protected $fillable = [
        'id',
        'question',
        'answer',
        'updated_at',
        'created_at',
    ];
}
