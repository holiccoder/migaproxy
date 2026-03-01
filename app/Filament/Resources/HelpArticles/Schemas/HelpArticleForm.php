<?php

namespace App\Filament\Resources\HelpArticles\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class HelpArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('help_category_id')
                    ->label('Category')
                    ->relationship('category', 'title')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, callable $set): void {
                        $set('slug', Str::slug($state ?? ''));
                    }),
                Textarea::make('description')
                    ->required()
                    ->maxLength(2000)
                    ->columnSpanFull(),
                TagsInput::make('related_slugs')
                    ->placeholder('Add related article slug')
                    ->columnSpanFull(),
                Toggle::make('is_published')
                    ->default(true),
                Repeater::make('sections')
                    ->relationship('sections')
                    ->orderColumn('sort_order')
                    ->collapsed()
                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                    ->schema([
                        TextInput::make('section_key')
                            ->required()
                            ->maxLength(255)
                            ->label('Section ID'),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('paragraphs')
                            ->label('Paragraphs (one per line)')
                            ->required()
                            ->rows(6)
                            ->formatStateUsing(function ($state): string {
                                if (is_array($state)) {
                                    return implode(PHP_EOL, $state);
                                }

                                return (string) ($state ?? '');
                            })
                            ->dehydrateStateUsing(function ($state): array {
                                $lines = preg_split('/\r\n|\r|\n/', (string) $state) ?: [];

                                return collect($lines)
                                    ->map(fn (string $line): string => trim($line))
                                    ->filter()
                                    ->values()
                                    ->all();
                            }),
                        Select::make('callout_type')
                            ->options([
                                'note' => 'Note',
                                'warning' => 'Warning',
                                'tip' => 'Tip',
                            ]),
                        TextInput::make('callout_title')
                            ->maxLength(255),
                        Textarea::make('callout_content')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('code_language')
                            ->maxLength(50),
                        Textarea::make('code')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
