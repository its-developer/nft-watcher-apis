<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class NftWatcherProject extends Model
{
    use HasFactory;

    protected $table = 'nft_watcher_projects';
    protected $connection = 'mongodb';

    protected $fillable = [
        'id',
        'email',
        'project_name',
        'agree_to_pay_listing_fee',
        'opensea_url',
        'website_url',
        'project_twitter_url',
        'project_discrod_url',
        'short_description',
        'project_status',
        'items_number_in_collection',
        'collection_blockchain',
        'collection_contract_address',
        'token_standard',
        'questionAnswer',
        'project_sale_start_date',
        'project_sale_end_date',
        'project_reveal_date',
        'project_unit_price',
        'updated_at',
        'created_at',
    ];
}
