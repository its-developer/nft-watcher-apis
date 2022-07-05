<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class NftWatcherDrop extends Model
{
    use HasFactory;
    
    protected $table = 'nft_watcher_drops';
    protected $connection = 'mongodb';


    protected $fillable = [
        'id',
        'is_featured',
        'title',
        'description',
        'image',
        'base_url_for_get_image',
        'launched_date',
        'blockchain_value',
        'blockchain_contact_address',
        'market_place_url',
        'contact_email',
        'website_url',
        'unit_price',
        'opensea_url',
        'total_supply',
        'twitter_url',
        'discord_url',
        'telegram_url',
        'reddit_url',
        'instagram_url',
        'votes',
        'reaction_one',
        'reaction_two',
        'reaction_three',
        'reaction_four',
        'reaction_five',
        'reaction_six',
        'is_presale',
        'presale_date',
        'updated_at',
        'created_at',
    ];

}
