<?php

namespace App\Filament\Resources\CmsPages\Schemas;

use App\Models\CmsPage;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CmsPageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, Set $set): void {
                        $set('slug', Str::slug($state ?? ''));
                    }),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                MarkdownEditor::make('content')
                    ->required()
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
                        CmsPage::STATUS_DRAFT => 'Draft',
                        CmsPage::STATUS_PUBLISHED => 'Published',
                    ])
                    ->required()
                    ->default(CmsPage::STATUS_DRAFT),
                DateTimePicker::make('published_at')
                    ->required()
                    ->default(now())
                    ->time(false),
                TextInput::make('meta_title')
                    ->maxLength(255),
                Textarea::make('meta_description')
                    ->maxLength(500)
                    ->columnSpanFull(),
            ]);
    }
}
