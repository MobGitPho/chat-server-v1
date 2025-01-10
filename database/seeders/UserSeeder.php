<?php

namespace Database\Seeders;

use App\Enums\ProfileType;
use App\Enums\UserRole;
use App\Models\AdminProfile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin Profiles
        // $adminProfiles = AdminProfile::factory(9)->create();

        // Create Users
        /* $users = collect();

        $users = $users->merge(User::factory(3)->create());
        $users = $users->merge(User::factory(2)->withLocale('en')->unverified()->create());
        $users = $users->merge(User::factory(1)->withLocale('fr')->disabled()->create());
        $users = $users->merge(User::factory(1)->authWithGoogle()->create());
        $users = $users->merge(User::factory(2)->authWithPhone()->create()); */

        // Assign Profile ID to Users
        /* $users->each(function ($user, $key) use ($adminProfiles) {
            if ($key === 1) {
                $user->addRole(UserRole::SUPER_ADMIN->value);
            } else if ($key > 1 && $key < 5) {
                $user->addRole(UserRole::ADMIN->value);
            } else {
                $user->addRole(UserRole::USER->value);
            }
            $user->profile_id = $adminProfiles->get($key % count($adminProfiles))->id;
            $user->profile_type = ProfileType::ADMIN->value;
            $user->save();
        }); */

        $adminProfile = AdminProfile::factory()->create();
        $user = User::factory()->withEmail('blckntr@gmail.com')->withLocale('fr')->unverified()->create();
        $user->addRole(UserRole::SUPER_ADMIN->value);
        $user->profile_id = $adminProfile->id;
        $user->profile_type = ProfileType::ADMIN->value;
        $user->save();
    }
}
