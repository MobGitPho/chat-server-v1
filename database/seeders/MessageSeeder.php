<?php

namespace Database\Seeders;

use App\Enums\MessageStatus;
use App\Models\Message;
use App\Models\User;
use App\Models\Group;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // Créer 50 messages aléatoires
        for ($i = 0; $i < 50; $i++) {
            $sender = User::inRandomOrder()->first();

            if (rand(0, 1)) {
                // Message privé
                $receiver = User::where('id', '!=', $sender->id)->inRandomOrder()->first();
                Message::create([
                    'sender_id' => $sender->id,
                    'receiver_id' => $receiver->id,
                    'group_id' => null,
                    'content' => fake()->sentence(),
                    'attachment' => rand(0, 3) ? null : 'attachments/example.jpg',
                    'status' => MessageStatus::SENT->value,
                    'read_at' => rand(0, 1) ? now() : null,
                ]);
            } else {
                // Message de groupe
                $group = Group::inRandomOrder()->first();
                if ($group) { // Vérifier que le groupe existe
                    Message::create([
                        'sender_id' => $sender->id,
                        'receiver_id' => null,
                        'group_id' => $group->id,
                        'content' => fake()->sentence(),
                        'attachment' => rand(0, 3) ? null : 'attachments/example.jpg',
                        'status' => MessageStatus::SENT->value,
                        'read_at' => rand(0, 1) ? now() : null,
                    ]);
                }
            }
        }
    }
}
