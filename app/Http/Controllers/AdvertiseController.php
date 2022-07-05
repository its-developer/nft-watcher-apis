<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdvertiseFAQ;
use App\Models\AdvertisePricingBox;
use App\Models\AdvertisePricingSlider;

class AdvertiseController extends Controller
{
    public function index(Request $request)
    {
        $sliders = AdvertisePricingSlider::all();
        $faqs = AdvertiseFAQ::all();
        $boxes = AdvertisePricingBox::all();

        return response()->json(array(
            'success' => true,
            'advertise' => array(
                'pricing_sliders' => $sliders,
                'faqs' => $faqs,
                'pricing_boxes' => $boxes
            )
        ));
    }
}
