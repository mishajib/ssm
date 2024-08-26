<?php

namespace App\Http\Controllers\API\V1\Page;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Http\Resources\SliderResource;
use App\Models\CustomerType;
use App\Models\Page;
use App\Models\Slider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PagesController extends Controller
{
    public function index():JsonResponse
    {
        return success_response(
            'Home page retrieved successfully.',
            (new PageResource(
                Cache::rememberForever('homePage', fn() => Page::whereNull('slug')->whereTemplate(Page::TEMPLATE_HOME)->first())
            ))->resolve()
        );
    }

    public function sliders():JsonResponse
    {
        return success_response(
            'Sliders retrieved successfully.',
            Cache::rememberForever('sliders', function () {
                return SliderResource::collection(Slider::latest()->get())->resolve();
            })
        );
    }

    public function customerTypes():JsonResponse
    {
        return success_response(
            'Customer types retrieved successfully.',
            Cache::rememberForever('customer_types', function () {
                return CustomerType::active()->get();
            })
        );
    }

    public function ourStory():JsonResponse
    {
        return success_response(
            'Our story page retrieved successfully.',
            (new PageResource(
                Cache::get('ourStoryPage')
            ))->resolve()
        );
    }

    public function contact():JsonResponse
    {
        return success_response(
            'Contact page retrieved successfully.',
            (new PageResource(
                Cache::get('ourStoryPage')
            ))->resolve()
        );
    }
}
