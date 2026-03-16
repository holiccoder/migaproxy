<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class BrandingSettingController extends Controller
{
    public function show(): JsonResponse
    {
        $logoPath = SystemSetting::getString(SystemSetting::KEY_FRONTEND_LOGO_PATH);
        $logoUrl = null;

        if (is_string($logoPath) && $logoPath !== '' && Storage::disk('public')->exists($logoPath)) {
            $logoUrl = Storage::disk('public')->url($logoPath);
        }

        return response()->json([
            'data' => [
                'logo_path' => $logoPath,
                'logo_url' => $logoUrl,
            ],
        ]);
    }
}
