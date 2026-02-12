<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\Post;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, Set $set): void {
                        $set('slug', Str::slug($state ?? ''));
                    }),
                Textarea::make('excerpt')
                    ->maxLength(500)
                    ->columnSpanFull(),
                MarkdownEditor::make('body')
                    ->required()
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
                        Post::STATUS_DRAFT => 'Draft',
                        Post::STATUS_PUBLISHED => 'Published',
                    ])
                    ->required()
                    ->default(Post::STATUS_DRAFT),
                DateTimePicker::make('published_at')
                    ->required()
                    ->default(now())
                    ->time(false),
                FileUpload::make('cover_image_path')
                    ->label('Cover Image')
                    ->disk('public')
                    ->directory('covers')
                    ->image(),
                Select::make('categories')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
                Select::make('tags')
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]);
    }
}
