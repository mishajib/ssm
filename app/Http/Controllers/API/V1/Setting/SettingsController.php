<?php

namespace App\Http\Controllers\API\V1\Setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function socialMedias()
    {
        return success_response(
            'Social medias retrieved successfully.',
            [
                'facebook' => setting('facebook_url'),
                'twitter' => setting('twitter_url'),
                'instagram' => setting('instagram_url'),
            ]
        );
    }
}
