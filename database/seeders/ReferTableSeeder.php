<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Refer;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ReferTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $referer = [170];
        foreach ($referer as $referer_id) {


            for ($i = 0; $i < 10; $i++) {
                // checking if refer exist or not
                if ($referer_id != null) {
                    $referer = User::find($referer_id);


                    // initializing chain name to user
                    if ($referer->parent_chain != null) {
                        $newUserParentChain = json_decode($referer->parent_chain);
                        array_push($newUserParentChain, $referer->chain_name);
                        // updating how many times chain is used
                        foreach ($newUserParentChain as $chain) {
                            $chainOwner = User::where('chain_name', $chain)->first();
                            $chainOwner->chain_used = $chainOwner->chain_used + 1;
                            $chainOwner->save();
                        }
                    } else {
                        $newUserParentChain = [$referer->chain_name];
                        // updating how many times chain is used
                        $chainOwner = User::where('chain_name', $referer->chain_name)->first();
                        $chainOwner->chain_used = $chainOwner->chain_used + 1;
                        $chainOwner->save();
                    }
                }

                $user = User::create([
                    'name' => 'user' . time(),
                    'email' => time() . uniqid() . 'test@example.com',
                    'password' => Hash::make('password'),
                    'role_id' => 1,
                    'chain_name' => Str::random(25),
                    'parent_chain' => json_encode($newUserParentChain)
                ]);
                $refer = Refer::create([
                    'referer_id' => $referer_id,
                    'registered_user_id' => $user->id
                ]);
            }
        }
    }
}