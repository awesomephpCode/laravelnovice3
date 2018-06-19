<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{

    public function run()
    {
        $users = factory(User::class)->times(50)->make();
        User::insert($users->makeVisible(['password', 'remember_token'])->toArray());

        $user = User::find(1);
        $user->name = 'boy';
        $user->email = '1785301169@qq.com';
        $user->password = bcrypt('999999');
        $user->is_admin = true;
        $user->activated = true;
        $user->save();
    }
}
