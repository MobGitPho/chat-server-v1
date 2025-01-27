<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Création de 5 groupes aléatoires
        $groups = [
            ['name' => '{ "en": "All", "fr": "All" }', 'code' => 'ALL'],
            ['name' => '{ "en": "Compta", "fr": "Compta" }', 'code' => 'CPTA'],
            ['name' => '{ "en": "HR", "fr": "RH" }', 'code' => 'HRRH'],
            ['name' => '{ "en": "IT", "fr": "Informatique" }', 'code' => 'IT'],
            ['name' => '{ "en": "Marketing", "fr": "Marketing" }', 'code' => 'MKT'],
            ['name' => '{ "en": "Sales", "fr": "Ventes" }', 'code' => 'SLS'],
        ];

        foreach ($groups as $group) {

            $groupModel = Group::create($group);
            $users = User::inRandomOrder()->limit(rand(2, 5))->pluck('id');
            $groupModel->users()->attach($users);
        }


    }
}
