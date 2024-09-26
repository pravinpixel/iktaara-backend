<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SiteResource;
use App\Models\GlobalSettings;

class SiteController extends Controller
{
    public function siteInfo()
    {
        try {
            $siteDetails = GlobalSettings::first();
        } catch (ModelNotFoundException $e) {
            return response()->json(array('error' => 1,'status_code' => 400, 'message' => $e->getMessage(), 'status' => 'failure', 'data' => []), 200);
        }

        return response()->json(array('error' => 0,'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => new SiteResource($siteDetails)), 200);

    }
}
