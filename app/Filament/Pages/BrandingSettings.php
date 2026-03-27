<?php

namespace App\Filament\Pages;

use App\Models\SystemSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class BrandingSettings extends Page implements HasForms
{
    use InteractsWithForms;

    private const MIN_AFFILIATE_COMMISSION_RATE = 1;

    private const MAX_AFFILIATE_COMMISSION_RATE = 100;

    protected static ?string $navigationLabel = 'Branding Settings';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.branding-settings';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'logo_path' => SystemSetting::getString(SystemSetting::KEY_FRONTEND_LOGO_PATH),
            'favicon_path' => SystemSetting::getString(SystemSetting::KEY_FRONTEND_FAVICON_PATH),
            'default_affiliate_commission_rate' => SystemSetting::getString(
                SystemSetting::KEY_DEFAULT_AFFILIATE_COMMISSION_RATE,
                (string) SystemSetting::DEFAULT_AFFILIATE_COMMISSION_RATE,
            ),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('logo_path')
                    ->label('Frontend Logo')
                    ->disk('public')
                    ->directory('branding')
                    ->acceptedFileTypes(['image/*', 'image/svg+xml'])
                    ->helperText('Upload a logo image for frontend pages. Suggested minimum width: 300px.'),
                FileUpload::make('favicon_path')
                    ->label('Frontend Favicon')
                    ->disk('public')
                    ->directory('branding')
                    ->acceptedFileTypes([
                        'image/png',
                        'image/jpeg',
                        'image/webp',
                        'image/svg+xml',
                        'image/x-icon',
                        'image/vnd.microsoft.icon',
                    ])
                    ->helperText('Upload a favicon for browser tabs. Recommended: square icon (32x32 or 64x64).'),
                TextInput::make('default_affiliate_commission_rate')
                    ->label('Default Affiliate Commission Rate (%)')
                    ->numeric()
                    ->required()
                    ->step(1)
                    ->minValue(self::MIN_AFFILIATE_COMMISSION_RATE)
                    ->maxValue(self::MAX_AFFILIATE_COMMISSION_RATE)
                    ->helperText('Applied when an affiliate profile is auto-created after email verification.'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $validated = $this->form->getState();

        $this->syncUploadedFileSetting(
            SystemSetting::KEY_FRONTEND_LOGO_PATH,
            $validated['logo_path'] ?? null,
        );

        $this->syncUploadedFileSetting(
            SystemSetting::KEY_FRONTEND_FAVICON_PATH,
            $validated['favicon_path'] ?? null,
        );

        $this->syncIntegerSetting(
            SystemSetting::KEY_DEFAULT_AFFILIATE_COMMISSION_RATE,
            $validated['default_affiliate_commission_rate'] ?? null,
            self::MIN_AFFILIATE_COMMISSION_RATE,
            self::MAX_AFFILIATE_COMMISSION_RATE,
            SystemSetting::DEFAULT_AFFILIATE_COMMISSION_RATE,
        );

        Notification::make()
            ->title('Branding settings saved.')
            ->success()
            ->send();
    }

    private function syncUploadedFileSetting(string $key, mixed $rawValue): void
    {
        $currentPath = SystemSetting::getString($key);
        $newPath = is_string($rawValue) ? trim($rawValue) : null;

        if (
            is_string($currentPath)
            && $currentPath !== ''
            && $currentPath !== $newPath
            && Storage::disk('public')->exists($currentPath)
        ) {
            Storage::disk('public')->delete($currentPath);
        }

        SystemSetting::putString($key, $newPath);
    }

    private function syncIntegerSetting(string $key, mixed $rawValue, int $minimum, int $maximum, int $fallback): void
    {
        $integerValue = null;

        if (is_int($rawValue)) {
            $integerValue = $rawValue;
        } elseif (is_float($rawValue)) {
            $integerValue = (int) round($rawValue);
        } elseif (is_string($rawValue) && is_numeric($rawValue)) {
            $integerValue = (int) round((float) $rawValue);
        }

        if (! is_int($integerValue)) {
            $integerValue = $fallback;
        }

        $normalizedValue = min($maximum, max($minimum, $integerValue));

        SystemSetting::putString($key, (string) $normalizedValue);
    }
}
