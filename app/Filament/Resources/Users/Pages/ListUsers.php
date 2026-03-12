<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
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
            CreateAction::make(),
        ];
    }
}
