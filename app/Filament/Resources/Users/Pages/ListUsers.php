<?php

namespace App\Filament\Resources\Users\Pages;

use App\Actions\Notifications\SendPrivateMessage;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Filament\Resources\Users\UserResource;
use App\Models\Admin;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Str;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('orderForUser')
                ->label('Order for User')
                ->icon('heroicon-o-shopping-cart')
                ->color('success')
                ->form([
                    Select::make('user_id')
                        ->label('User')
                        ->options(User::query()->pluck('email', 'id'))
                        ->searchable()
                        ->required(),
                    Select::make('plan_id')
                        ->label('Plan')
                        ->options(Plan::query()->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $user = User::query()->findOrFail($data['user_id']);
                    $plan = Plan::query()->findOrFail($data['plan_id']);

                    $order = Order::query()->create([
                        'public_id' => Str::ulid()->toBase32(),
                        'user_id' => $user->id,
                        'plan_id' => $plan->id,
                        'status' => Order::STATUS_PAID,
                        'subtotal' => $plan->price,
                        'discount_total' => 0,
                        'total' => $plan->price,
                        'currency' => 'USD',
                        'metadata' => ['source' => 'admin_manual'],
                        'paid_at' => now(),
                    ]);

                    $startsAt = now();
                    $endsAt = $startsAt->copy()->addDays($plan->durationInDays());

                    $subscription = Subscription::query()->create([
                        'user_id' => $user->id,
                        'plan_id' => $plan->id,
                        'order_id' => $order->id,
                        'status' => Subscription::STATUS_ACTIVE,
                        'starts_at' => $startsAt,
                        'ends_at' => $endsAt,
                    ]);

                    $order->forceFill(['subscription_id' => $subscription->id])->save();

                    Notification::make()
                        ->title('Order created and subscription activated.')
                        ->success()
                        ->send();
                }),
            Action::make('sendUserMessage')
                ->label('Send User Message')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->modalHeading('Send User Message')
                ->modalSubmitActionLabel('Send Message')
                ->form([
                    Select::make('user_name_id')
                        ->label('User Name')
                        ->searchable()
                        ->placeholder('Search by user name')
                        ->getSearchResultsUsing(function (string $search): array {
                            return User::query()
                                ->where('name', 'like', "%{$search}%")
                                ->orderBy('name')
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(
                                    fn (User $user): array => [
                                        (string) $user->id => sprintf('%s (%s)', $user->name, $user->email),
                                    ],
                                )
                                ->all();
                        })
                        ->getOptionLabelUsing(function ($value): ?string {
                            $user = User::query()->find($value);

                            if (! $user instanceof User) {
                                return null;
                            }

                            return sprintf('%s (%s)', $user->name, $user->email);
                        }),
                    Select::make('user_email_id')
                        ->label('User Email')
                        ->searchable()
                        ->placeholder('Search by user email')
                        ->getSearchResultsUsing(function (string $search): array {
                            return User::query()
                                ->where('email', 'like', "%{$search}%")
                                ->orderBy('email')
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(
                                    fn (User $user): array => [
                                        (string) $user->id => $user->email,
                                    ],
                                )
                                ->all();
                        })
                        ->getOptionLabelUsing(function ($value): ?string {
                            $user = User::query()->find($value);

                            if (! $user instanceof User) {
                                return null;
                            }

                            return $user->email;
                        }),
                    Textarea::make('message')
                        ->label('Message')
                        ->required()
                        ->rows(6)
                        ->maxLength(5000),
                ])
                ->action(function (array $data, SendPrivateMessage $sendPrivateMessage): void {
                    $userIdFromName = (int) ($data['user_name_id'] ?? 0);
                    $userIdFromEmail = (int) ($data['user_email_id'] ?? 0);

                    if ($userIdFromName <= 0 && $userIdFromEmail <= 0) {
                        Notification::make()
                            ->title('Select a user by name or email.')
                            ->danger()
                            ->send();

                        return;
                    }

                    if ($userIdFromName > 0 && $userIdFromEmail > 0 && $userIdFromName !== $userIdFromEmail) {
                        Notification::make()
                            ->title('Selected name and email must belong to the same user.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $recipientId = $userIdFromName > 0 ? $userIdFromName : $userIdFromEmail;
                    $recipient = User::query()->find($recipientId);

                    if (! $recipient instanceof User) {
                        Notification::make()
                            ->title('User not found.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $message = isset($data['message']) && is_string($data['message'])
                        ? trim($data['message'])
                        : '';

                    if ($message === '') {
                        Notification::make()
                            ->title('Message is required.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $authAdmin = auth('admin')->user();
                    $sender = $authAdmin instanceof Admin ? $authAdmin : null;

                    $sendPrivateMessage->execute(
                        recipient: $recipient,
                        sender: $sender,
                        subject: 'Admin Message',
                        message: $message,
                    );

                    Notification::make()
                        ->title('Private message sent.')
                        ->body(sprintf('Message sent to %s.', $recipient->email))
                        ->success()
                        ->send();
                }),
            UsersTable::makeRechargeBalanceAction(),
            CreateAction::make(),
        ];
    }
}
