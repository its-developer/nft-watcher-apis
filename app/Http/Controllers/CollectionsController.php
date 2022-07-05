<?php

namespace App\Http\Controllers;

use App\Models\Collections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CollectionsController extends Controller
{
    public function index(Request $request, $collection_name)
    {

        if ($collection_name == 'all_collections') {
            $collections = Collections::orderBy('seven_day_volume', 'desc');
            if ($request->has('search')) {
                $collections = $collections->where('name', 'like', '%' . $request->get('search') . '%');
            }
            $collections = $collections->get();

            $limit = $request->has('limit') ? $request->get('limit') : 20;
            $page = $request->has('page') ? intval($request->get('page')) : 1;

            $totalPages = ceil(count($collections) / $limit);
            $offset = ($page - 1) * $limit;
            if ($offset < 0) {
                $offset = 0;
            }

            $data = array_slice($collections->toArray(), $offset, $limit);

            return response()->json([
                'success' => true,
                'totalPages' => $totalPages,
                'page' => $page,
                'collections' => $data,
            ]);

        } else if ($collection_name == 'newest') {
            $collections = Collections::orderBy('featured', "desc")->orderBy('created_at', 'desc');
        } else if ($collection_name == 'top_collections_by_seven_day_volume') {
            $collections = Collections::orderBy('stats.seven_day_volume', 'desc');
        } else if ($collection_name == 'top_collections_by_total_volume') {
            $collections = Collections::orderBy('stats.total_volume', 'desc');
        } else if ($collection_name == 'top_collections_by_seven_day_avg_price') {
            $collections = Collections::orderBy('stats.seven_day_average_price', 'desc');
        } else if ($collection_name == 'top_collections_by_owner_count') {
            $collections = Collections::orderBy('stats.num_owners', 'desc');
        } else if ($collection_name == "top_collections") {
            $collections['top_collections_by_seven_day_volume'] = Collections::orderBy('stats.seven_day_volume', 'desc')->take(10)->get();
            $collections['top_collections_by_total_volume'] = Collections::orderBy('stats.total_volume', 'desc')->take(10)->get();
            $collections['top_collections_by_seven_day_avg_price'] = Collections::orderBy('stats.seven_day_average_price', 'desc')->take(10)->get();
            $collections['top_collections_by_owner_count'] = Collections::orderBy('stats.num_owners', 'desc')->take(10)->get();

            return response()->json([
                'success' => true,
                'collections' => $collections,
            ]);
        }

        if ($request->has('offset')) {
            $collections = $collections->skip($request->get('offset'));
        }

        if ($request->has('limit')) {
            $collections = $collections->take($request->get('limit'));
        }

        if ($request->has('search')) {
            $collections = $collections->where('name', 'like', '%' . $request->get('search') . '%');
        }

        $collections = $collections->get();

        return response()->json([
            'success' => true,
            'collections' => $collections,
        ]);
    }

    public function getAllCollections(Request $request)
    {
        $collections = Collections::select('name', 'slug', 'image_url')->get();

        return response()->json([
            'success' => true,
            'collections' => $collections,
        ]);
    }

    public function getCollection(Request $request, $slug)
    {
        $collectionData = Collections::where('slug', $slug)->first();
        if (is_string($collectionData->traits)) {
            $collectionData->traits = json_decode($collectionData->traits, true);
        }
        $collection = $collectionData->toArray();

        if (Storage::disk('local')->exists($request->slug . ".json")) {
            $assets = Storage::disk('local')->get($request->slug . ".json");
            $assets = json_decode($assets);

            $tally = array(
                'TraitCount' => array(),
            );

            $metadata = array_column($assets, 'traits');

            for ($j = 0.0; $j < count($metadata); $j++) {
                $nftTraits = array_column($metadata[$j], "trait_type");
                $nftValues = array_column($metadata[$j], "value");
                $numOfTraits = count($nftTraits);

                if (isset($tally['TraitCount'][$numOfTraits])) {
                    $tally['TraitCount'][$numOfTraits]++;
                } else {
                    $tally['TraitCount'][$numOfTraits] = 1;
                }

                for ($i = 0; $i < count($nftTraits); $i++) {
                    $current = $nftTraits[$i];
                    if (isset($tally[$current])) {
                        $tally[$current]['occurences']++;
                    } else {
                        $tally[$current] = array('occurences' => 1);
                    }

                    $currentValue = $nftValues[$i];
                    if (isset($tally[$current][$currentValue])) {
                        $tally[$current][$currentValue]++;
                    } else {
                        $tally[$current][$currentValue] = 1;
                    }
                }
            }

            $collectionAttributes = array_keys((array) $tally);
            $nftArr = [];

            for ($j = 0; $j < count($metadata); $j++) {
                $current = $metadata[$j];
                $totalRarity = 0;
                $traitCountTraitCount = 0;
                for ($i = 0; $i < count($current); $i++) {
                    $rarityScore = $current[$i]->trait_count == 0 ? 0 : 1 / ($current[$i]->trait_count / count($assets));
                    $current[$i]->rarityScore = $rarityScore;
                    $totalRarity += $rarityScore;
                    $traitCountTraitCount += $current[$i]->trait_count;
                }

                $rarityScoreNumTraits = 0;
                if (!empty($traitCountTraitCount)) {
                    $rarityScoreNumTraits = 1 / ($traitCountTraitCount / count($assets));
                }
                array_push($current, array(
                    'trait_type' => "TraitCount",
                    'value' => count(array_keys((array) $current)),
                    'trait_count' => $tally['TraitCount'][count(array_keys((array) $current))],
                    'rarityScore' => $rarityScoreNumTraits,
                ));

                if (count($current) < count($collectionAttributes)) {
                    $nftAttributes = array_column($current, 'trait_type');
                    $absent = array_filter($collectionAttributes, function ($item) use ($nftAttributes) {
                        return !in_array($item, $nftAttributes);
                    });
                    foreach ($absent as $type) {
                        $rarityScoreNull =
                            1 / ((count($assets) - $tally[$type]['occurences']) / count($assets));
                        array_push($current, array(
                            'trait_type' => $type,
                            'value' => null,
                            'trait_count' => count($assets) - $tally[$type]['occurences'],
                            'rarityScore' => $rarityScoreNull,
                        ));
                        $collection['traits'][$type]['null'] = (count($assets) - $tally[$type]['occurences']);
                        $totalRarity += $rarityScoreNull;
                    }
                }
            }

            // return $nftArr[0];

            $collection['traits_count'] = $tally['TraitCount'];
        } else {
            $collection['traits_count'] = null;
        }

        if ($collection) {
            return response()->json([
                'success' => true,
                'collection' => $collection,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'collection' => "Collection Not Found!",
            ]);
        }
    }

    public function storeAssets(Request $request)
    {
        if (!Storage::disk('local')->exists($request->slug . ".json")) {
            $oldAssets = [];
        } else {
            $oldAssets = Storage::disk('local')->get($request->slug . ".json");
            $oldAssets = json_decode($oldAssets, true);
        }

        $assets = array_merge($request->assets, $oldAssets);
        Storage::disk('local')->put($request->slug . ".json", json_encode($assets, true));
        return response()->json([
            'success' => true,
        ]);
    }

    public function assets(Request $request, $slug)
    {
        if (!Storage::disk('local')->exists($request->slug . ".json")) {
            return response()->json([
                'success' => false,
                'total' => 0,
            ]);
        }
        $assets = Storage::disk('local')->get($request->slug . ".json");
        $assets = json_decode($assets);

        $tally = array(
            'TraitCount' => array(),
        );

        $metadata = array_column($assets, 'traits');

        for ($j = 0.0; $j < count($metadata); $j++) {
            $nftTraits = array_column($metadata[$j], "trait_type");
            $nftValues = array_column($metadata[$j], "value");
            $numOfTraits = count($nftTraits);

            if (isset($tally['TraitCount'][$numOfTraits])) {
                $tally['TraitCount'][$numOfTraits]++;
            } else {
                $tally['TraitCount'][$numOfTraits] = 1;
            }

            for ($i = 0; $i < count($nftTraits); $i++) {
                $current = $nftTraits[$i];
                if (isset($tally[$current])) {
                    $tally[$current]['occurences']++;
                } else {
                    $tally[$current] = array('occurences' => 1);
                }

                $currentValue = $nftValues[$i];
                if (isset($tally[$current][$currentValue])) {
                    $tally[$current][$currentValue]++;
                } else {
                    $tally[$current][$currentValue] = 1;
                }
            }
        }

        $collectionAttributes = array_keys((array) $tally);
        $nftArr = [];

        for ($j = 0; $j < count($metadata); $j++) {
            $current = $metadata[$j];
            $totalRarity = 0;
            $traitCountTraitCount = 0;
            for ($i = 0; $i < count($current); $i++) {
                $rarityScore = $current[$i]->trait_count == 0 ? 0 : 1 / ($current[$i]->trait_count / count($assets));
                $current[$i]->rarityScore = $rarityScore;
                $totalRarity += $rarityScore;
                $traitCountTraitCount += $current[$i]->trait_count;
            }

            $rarityScoreNumTraits = 0;
            if (!empty($traitCountTraitCount)) {
                $rarityScoreNumTraits = 1 / ($traitCountTraitCount / count($assets));
            }
            array_push($current, array(
                'trait_type' => "TraitCount",
                'value' => count(array_keys((array) $current)),
                'trait_count' => $tally['TraitCount'][count(array_keys((array) $current))],
                'rarityScore' => $rarityScoreNumTraits,
            ));
            $totalRarity += $rarityScoreNumTraits;

            if (count($current) < count($collectionAttributes)) {
                $nftAttributes = array_column($current, 'trait_type');
                $absent = array_filter($collectionAttributes, function ($item) use ($nftAttributes) {
                    return !in_array($item, $nftAttributes);
                });
                foreach ($absent as $type) {
                    $rarityScoreNull =
                        1 / ((count($assets) - $tally[$type]['occurences']) / count($assets));
                    array_push($current, array(
                        'trait_type' => $type,
                        'value' => null,
                        'trait_count' => count($assets) - $tally[$type]['occurences'],
                        'rarityScore' => $rarityScoreNull,
                    ));
                    $totalRarity += $rarityScoreNull;
                }
            }

            $asset = $assets[$j];
            $asset->traits = $current;
            $asset->rarityScore = $totalRarity;
            array_push($nftArr, $asset);
        }

        $assets = collect($nftArr);
        $assets = $assets->sortByDesc('rarityScore');

        $nftData = $assets->values()->all();

        foreach ($nftData as $key => $value) {
            $value->ranking_no = $key + 1;
        }

        $limit = $request->has('limit') ? (int) $request->get('limit') : 20;
        $page = $request->has('page') ? (int) $request->get('page') : 1;
        $sortBy = $request->has('sortBy') ? $request->get('sortBy') : null;
        $listing_type = $request->has('listing_type') ? $request->get('listing_type') : null;
        $listing_type2 = $request->has('listing_type2') ? $request->get('listing_type2') : null;
        $price_greater_than = $request->has('price_greater_than') ? $request->get('price_greater_than') : null;
        $price_less_than = $request->has('price_less_than') ? $request->get('price_less_than') : null;
        $traits = $request->has('traits') ? $request->get('traits') : null;

        if ($listing_type != "buy:now" || $listing_type2 != "on:auction") {
            if ($listing_type == "buy:now") {
                $assets = $assets->where('current_price', '!=', null);
            }

            if ($listing_type2 == "on:auction") {
                $assets = $assets->where('current_price', null);
            }
        }

        if ($price_greater_than != null) {
            $assets = $assets->where('current_price', '>=', $price_greater_than * 1000000000000000000)->where('current_price', '!=', null);
        }

        if ($price_less_than != null) {
            $assets = $assets->where('current_price', '<', $price_less_than * 1000000000000000000)->where('current_price', '!=', null);
        }

        if ($traits != null) {
            $traits = explode(":", $traits);
            foreach ($traits as $trait) {
                $trait = explode("=", $trait);
                $trait_type = $trait[0];
                $trait_value = $trait[1];
                $trait = [
                    'trait_type' => $trait_type,
                    'trait_value' => $trait_value,
                ];
                if ($trait_type == "Trait Count") {
                    $assets = $assets->map(function ($d) use ($trait) {
                        if ($d) {
                            $traitsColl = collect($d->traits);
                            $traitsColl = $traitsColl->where("trait_count", '!=', null)->where("value", '!=', null)->values()->toArray();
                            $trait_count = count($traitsColl) - 1;
                            if ($trait_count == $trait['trait_value']) {
                                return $d;
                            }
                        }
                    });
                    // return $assets;
                } else {
                    if ($trait['trait_value'] == 'Null') {
                        $trait['trait_value'] = null;
                    }
                    $assets = $assets->map(function ($d) use ($trait) {
                        if ($d) {
                            $traitsColl = collect($d->traits);
                            $traitsColl = $traitsColl->where('trait_type', $trait['trait_type'])->where('value', $trait['trait_value'])->all();
                            if (count($traitsColl) > 0) {
                                return $d;
                            }
                        }
                    });
                }

            }
        }

        if ($sortBy == "price:low:high") {
            $assets = $assets->sortBy(function ($e) {
                if (isset($e->current_price)) {
                    return $e->current_price;
                } else {
                    return PHP_INT_MAX;
                }
            });
        } else if ($sortBy == "price:high:low") {
            $assets = $assets->sortByDesc('current_price');
        } else if ($sortBy == 'recently:listed') {
            $assets = $assets->sortByDesc(function ($item) {
                if (isset($item->listing_created_date)) {
                    return $item->listing_created_date;
                } else {
                    return null;
                }
            });
        } else if ($sortBy == 'by:id') {
            $assets = $assets->sortBy('token_id');
        }

        $assets = $assets->where('id', '!=', null);

        $totalPages = ceil($assets->count() / $limit);
        $offset = ($page - 1) * $limit;
        if ($offset < 0) {
            $offset = 0;
        }

        $assets = $assets->values()->all();
        $data = array_slice($assets, $offset, $limit);

        return response()->json([
            'total' => count($assets),
            'page' => $page,
            'total_pages' => $totalPages,
            'assets' => $data,
        ]);
    }

    public function removeAssets(Request $request)
    {
        if (!Storage::disk('local')->exists($request->slug . ".json")) {
            return response()->json([
                'success' => true,
                'message' => "Assets Deleted Successful!",
            ]);
        }

        $assets = Storage::disk('local')->delete($request->slug . ".json");

        return response()->json([
            'success' => true,
            'message' => "Assets Deleted Successful!",
        ]);
    }

    public function getAsset(Request $request, $slug, $id)
    {
        if (!Storage::disk('local')->exists($request->slug . ".json")) {
            return response()->json([
                'success' => false,
                'asset' => null,
            ]);
        }

        $collection = Collections::where('slug', $request->slug)->first();

        $assets = Storage::disk('local')->get($request->slug . ".json");
        $assets = json_decode($assets);

        $tally = array(
            'TraitCount' => array(),
        );

        $metadata = array_column($assets, 'traits');

        $assetsCollection = collect($assets);

        for ($j = 0.0; $j < count($metadata); $j++) {
            $nftTraits = array_column($metadata[$j], "trait_type");
            $nftValues = array_column($metadata[$j], "value");
            $numOfTraits = count($nftTraits);

            if (isset($tally['TraitCount'][$numOfTraits])) {
                $tally['TraitCount'][$numOfTraits]++;
            } else {
                $tally['TraitCount'][$numOfTraits] = 1;
            }

            for ($i = 0; $i < count($nftTraits); $i++) {
                $current = $nftTraits[$i];
                if (isset($tally[$current])) {
                    $tally[$current]['occurences']++;
                } else {
                    $tally[$current] = array('occurences' => 1);
                }

                $currentValue = $nftValues[$i];
                if (isset($tally[$current][$currentValue])) {
                    $tally[$current][$currentValue]++;
                } else {
                    $tally[$current][$currentValue] = 1;
                }
            }
        }

        $collectionAttributes = array_keys((array) $tally);
        $nftArr = [];

        for ($j = 0; $j < count($metadata); $j++) {
            $current = $metadata[$j];
            $totalRarity = 0;
            $traitCountTraitCount = 0;
            for ($i = 0; $i < count($current); $i++) {
                $rarityScore = $current[$i]->trait_count == 0 ? 0 : 1 / ($current[$i]->trait_count / count($assets));
                $current[$i]->rarityScore = $rarityScore;
                $totalRarity += $rarityScore;
                $traitCountTraitCount += $current[$i]->trait_count;
            }

            $rarityScoreNumTraits = 0;
            if (!empty($traitCountTraitCount)) {
                $rarityScoreNumTraits = 1 / ($traitCountTraitCount / count($assets));
            }
            array_push($current, array(
                'trait_type' => "TraitCount",
                'value' => count(array_keys((array) $current)),
                'trait_count' => $tally['TraitCount'][count(array_keys((array) $current))],
                'rarityScore' => $rarityScoreNumTraits,
            ));
            $totalRarity += $rarityScoreNumTraits;

            if (count($current) < count($collectionAttributes)) {
                $nftAttributes = array_column($current, 'trait_type');
                $absent = array_filter($collectionAttributes, function ($item) use ($nftAttributes) {
                    return !in_array($item, $nftAttributes);
                });
                foreach ($absent as $type) {
                    $rarityScoreNull =
                        1 / ((count($assets) - $tally[$type]['occurences']) / count($assets));
                    array_push($current, array(
                        'trait_type' => $type,
                        'value' => null,
                        'trait_count' => (count($assets) - $tally[$type]['occurences']),
                        'rarityScore' => $rarityScoreNull,
                    ));
                    $totalRarity += $rarityScoreNull;
                }
            }

            $asset = $assets[$j];
            $asset->traits = $current;
            $asset->rarityScore = $totalRarity;
            array_push($nftArr, $asset);
        }

        $assets = collect($nftArr);
        $assets = $assets->sortByDesc('rarityScore');

        $nftData = $assets->values()->all();

        foreach ($nftData as $key => $value) {
            $value->ranking_no = $key + 1;
        }
        $assets = collect($nftData);
        $asset = $assets->where('token_id', $id)->all();
        if (count($asset) > 0) {
            $firstKey = array_key_first($asset);
            return response()->json([
                'success' => true,
                'asset' => $asset[$firstKey],
            ]);
        } else {
            return response()->json([
                'success' => false,
                'asset' => null,
            ]);
        }
    }

}
