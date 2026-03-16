<?php

namespace App\Filament\Pages;

use App\Models\SystemSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class BrandingSettings extends Page implements HasForms
{
    use InteractsWithForms;

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
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $validated = $this->form->getState();

        $currentPath = SystemSetting::getString(SystemSetting::KEY_FRONTEND_LOGO_PATH);
        $newPath = isset($validated['logo_path']) && is_string($validated['logo_path'])
            ? trim($validated['logo_path'])
            : null;

        if (
            is_string($currentPath)
            && $currentPath !== ''
            && $currentPath !== $newPath
            && Storage::disk('public')->exists($currentPath)
        ) {
            Storage::disk('public')->delete($currentPath);
        }

        SystemSetting::putString(SystemSetting::KEY_FRONTEND_LOGO_PATH, $newPath);

        Notification::make()
            ->title('Branding settings saved.')
            ->success()
            ->send();
    }
}
