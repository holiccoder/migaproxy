<?php

namespace App\Filament\Resources\Users\Tables;

use App\Actions\Notifications\SendPrivateMessage;
use App\Models\Admin;
use App\Models\BalanceHistory;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'currentSubscription.plan',
                'ipmart',
            ]))
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('balance')
                    ->label('Wallet Balance')
                    ->money('USD', divideBy: 100)
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('plan')
                    ->label('Plan')
                    ->getStateUsing(fn (User $record): string => $record->currentSubscription?->plan?->name ?? '-')
                    ->toggleable(),
                TextColumn::make('available_traffic')
                    ->label('Available Traffic')
                    ->getStateUsing(fn (User $record): int => (int) ($record->ipmart?->plan_balance ?? 0))
                    ->numeric()
                    ->toggleable(),
                TextColumn::make('ipmart.ipmart_id')
                    ->label('IPmart ID')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('rechargeBalance')
                    ->label('Recharge Balance')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->modalHeading('Recharge User Balance')
                    ->modalSubmitActionLabel('Recharge')
                    ->form([
                        Select::make('user_id')
                            ->label('User')
                            ->required()
                            ->searchable()
                            ->placeholder('Search by ID, name, or email')
                            ->getSearchResultsUsing(function (string $search): array {
                                return User::query()
                                    ->where(function (Builder $query) use ($search): void {
                                        $query
                                            ->where('name', 'like', "%{$search}%")
                                            ->orWhere('email', 'like', "%{$search}%");

                                        if (is_numeric($search)) {
                                            $query->orWhere('id', (int) $search);
                                        }
                                    })
                                    ->orderByDesc('id')
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(
                                        fn (User $user): array => [
                                            (string) $user->id => sprintf(
                                                '#%s %s (%s)',
                                                (string) $user->id,
                                                $user->name,
                                                $user->email,
                                            ),
                                        ],
                                    )
                                    ->all();
                            })
                            ->getOptionLabelUsing(function ($value): ?string {
                                $user = User::query()->find($value);

                                if (! $user instanceof User) {
                                    return null;
                                }

                                return sprintf(
                                    '#%s %s (%s)',
                                    (string) $user->id,
                                    $user->name,
                                    $user->email,
                                );
                            }),
                        TextInput::make('amount')
                            ->label('Amount (USD)')
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->step(0.01),
                        TextInput::make('reference')
                            ->label('Reference')
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(1000),
                    ])
                    ->action(function (array $data): void {
                        $userId = (int) ($data['user_id'] ?? 0);
                        $amount = (float) ($data['amount'] ?? 0);
                        $amountInCents = (int) round($amount * 100);

                        if ($userId <= 0 || $amountInCents <= 0) {
                            FilamentNotification::make()
                                ->title('Invalid recharge details.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $reference = isset($data['reference']) && is_string($data['reference'])
                            ? trim($data['reference'])
                            : '';
                        $description = isset($data['description']) && is_string($data['description'])
                            ? trim($data['description'])
                            : '';

                        try {
                            $updatedUser = DB::transaction(function () use (
                                $userId,
                                $amountInCents,
                                $reference,
                                $description,
                            ): User {
                                $user = User::query()
                                    ->whereKey($userId)
                                    ->lockForUpdate()
                                    ->firstOrFail();

                                $beforeBalance = (int) $user->balance;
                                $afterBalance = $beforeBalance + $amountInCents;

                                $user->forceFill([
                                    'balance' => $afterBalance,
                                ])->save();

                                $user->balanceHistories()->create([
                                    'type' => BalanceHistory::TYPE_CREDIT,
                                    'amount' => $amountInCents,
                                    'before_balance' => $beforeBalance,
                                    'after_balance' => $afterBalance,
                                    'reference' => $reference !== '' ? $reference : null,
                                    'description' => $description !== '' ? $description : 'Admin balance recharge',
                                ]);

                                return $user;
                            });

                            FilamentNotification::make()
                                ->title('Balance recharged.')
                                ->body(sprintf(
                                    'Added $%s to %s. New balance: $%s.',
                                    number_format($amountInCents / 100, 2, '.', ''),
                                    $updatedUser->email,
                                    number_format(((int) $updatedUser->balance) / 100, 2, '.', ''),
                                ))
                                ->success()
                                ->send();
                        } catch (\Throwable) {
                            FilamentNotification::make()
                                ->title('Unable to recharge balance.')
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->recordActions([
                Action::make('sendPrivateMessage')
                    ->label('Send Message')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->form([
                        TextInput::make('subject')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('message')
                            ->required()
                            ->maxLength(5000)
                            ->rows(6),
                    ])
                    ->action(function (User $record, array $data, SendPrivateMessage $sendPrivateMessage): void {
                        $authAdmin = auth('admin')->user();
                        $sender = $authAdmin instanceof Admin ? $authAdmin : null;

                        $sendPrivateMessage->execute(
                            recipient: $record,
                            sender: $sender,
                            subject: $data['subject'],
                            message: $data['message'],
                        );

                        FilamentNotification::make()
                            ->title('Private message sent.')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }
}
