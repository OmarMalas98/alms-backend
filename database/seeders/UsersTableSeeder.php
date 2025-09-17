<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        $user = new User();
        $user->name = 'Omar Altinawi';
        $user->email = 'omar@gmail.com';
        $user->password = Hash::make('12345678');
        $user->role_id = 2;
        $user->save();

        $user = new User();
        $user->name = 'Omar Malas';
        $user->email = 'omar2@gmail.com';
        $user->password = Hash::make('12345678');
        $user->role_id = 1;
        $user->save();
    }
}
