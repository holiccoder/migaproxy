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
        $faviconPath = SystemSetting::getString(SystemSetting::KEY_FRONTEND_FAVICON_PATH);

        return response()->json([
            'data' => [
                'logo_path' => $logoPath,
                'logo_url' => $this->resolvePublicAssetUrl($logoPath),
                'favicon_path' => $faviconPath,
                'favicon_url' => $this->resolvePublicAssetUrl($faviconPath),
            ],
        ]);
    }

    private function resolvePublicAssetUrl(?string $path): ?string
    {
        if (! is_string($path) || $path === '') {
            return null;
        }

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
