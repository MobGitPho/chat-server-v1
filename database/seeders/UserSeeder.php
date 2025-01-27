<?php

namespace Database\Seeders;

use App\Enums\ProfileType;
use App\Enums\AuthType;
use App\Enums\UserRole;
use App\Models\AdminProfile;
use App\Models\CustomerProfile;
use App\Models\User;
use App\Enums\AccountStatus;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    protected static ?string $password;
    public function run(): void
    {
        $adminProfile = AdminProfile::create();
        $customerProfile = CustomerProfile::create();

        //Create Admin Profile
        $admin = User::create([
            'lastname' => 'Admin',
            'firstname' => 'Admin',
            'email' => 'admin@gmail.com',
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(60),
            'uid' => '1234_admin',
            'address' => '',
            'account_status' => AccountStatus::ENABLED->value,
            'auth_type' => AuthType::EMAIL_PASSWORD->value
        ]);
        $admin->addRole(UserRole::SUPER_ADMIN->value);
        $admin->profile_id = $adminProfile->id;
        $admin->profile_type = ProfileType::ADMIN->value;
        $admin->save();

        // Create User Profiles
        $user = User::create([
            'lastname' => 'John',
            'firstname' => 'Doe',
            'email' => 'johndoe@gmail.com',
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(60),
            'uid' => '1234_johnDoe',
            'address' => '',
            'account_status' => AccountStatus::ENABLED->value,
            'auth_type' => AuthType::EMAIL_PASSWORD->value
        ]);
        $user->addRole(UserRole::USER->value);
        $user->profile_id = $customerProfile->id;
        $user->profile_type = ProfileType::CUSTOMER->value;
        $user->save();


        // Création de 5 utilisateurs
        for ($i = 1; $i <= 5; $i++) {
            /*$customerProfile = CustomerProfile::create([
                'customer_code' => 'CUST00' . $i, // Génération d'un code client unique
                'details' => 'Profil du client ' . $i,
            ]);*/

            $user = User::create([
                'lastname' => 'User' . $i,
                'firstname' => 'Test',
                'email' => "user{$i}@example.com",
                'email_verified_at' => now(),
                'password' => static::$password ??= Hash::make('password'),
                'remember_token' => Str::random(60),
                'uid' => "user{$i}_testUser",
                'address' => 'Adresse Test',
                'account_status' => AccountStatus::ENABLED->value,
                'auth_type' => AuthType::EMAIL_PASSWORD->value,
            ]);

            // Assigner un rôle et lier le profil client
            $user->addRole(UserRole::USER->value);
            $user->profile_id = $customerProfile->id;
            $user->profile_type = ProfileType::CUSTOMER->value;
            $user->save();


        }
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

        /*$adminProfile = AdminProfile::factory()->create();
        $user = User::factory()->withEmail('admin@gmail.com')->withLocale('fr')->unverified()->create();
        $user->addRole(UserRole::SUPER_ADMIN->value);
        $user->profile_id = $adminProfile->id;
        $user->profile_type = ProfileType::ADMIN->value;
        $user->save();*/
    }
}
