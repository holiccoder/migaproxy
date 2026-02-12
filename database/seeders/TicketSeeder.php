<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->count() > 0
            ? User::all()
            : User::factory()->count(5)->create();

        $admin = Admin::query()->first() ?? Admin::factory()->create();

        Ticket::factory()
            ->count(12)
            ->make()
            ->each(function (Ticket $ticket) use ($users, $admin): void {
                $ticket->user_id = $users->random()->id;
                $ticket->save();

                $userMessage = TicketMessage::query()->create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $ticket->user_id,
                    'admin_id' => null,
                    'sender_type' => TicketMessage::SENDER_USER,
                    'message' => fake()->paragraph(),
                ]);

                $ticket->forceFill([
                    'last_user_reply_at' => $userMessage->created_at,
                ])->save();

                if (fake()->boolean(70)) {
                    $adminMessage = TicketMessage::query()->create([
                        'ticket_id' => $ticket->id,
                        'user_id' => null,
                        'admin_id' => $admin->id,
                        'sender_type' => TicketMessage::SENDER_ADMIN,
                        'message' => fake()->sentence(18),
                    ]);

                    $status = fake()->randomElement([
                        Ticket::STATUS_IN_PROGRESS,
                        Ticket::STATUS_RESOLVED,
                    ]);

                    $ticket->forceFill([
                        'status' => $status,
                        'last_admin_reply_at' => $adminMessage->created_at,
                        'resolved_at' => $status === Ticket::STATUS_RESOLVED ? now() : null,
                    ])->save();
                }
            });
    }
}
