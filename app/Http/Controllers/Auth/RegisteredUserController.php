<?php

namespace App\Http\Controllers\Auth;

use App\Models\Role;
use App\Models\User;
use App\Models\Refer;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Auth\Events\Registered;
use App\Providers\RouteServiceProvider;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create($id = null)
    {
        if ($id != null) {
            $user = User::where('id', $id)->with('getRole')->first();
            $totalRefer = Refer::where('referer_id', $user->id)->count();

            return view('auth.register')->with(['user' => $user, 'totalRefer' => $totalRefer]);
        }
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */

    // public function store(Request $request)
    // {

    //     $request->validate([
    //         'name' => ['required', 'string', 'max:255'],
    //         'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
    //         'password' => ['required', 'confirmed', Rules\Password::defaults()],
    //     ]);

    //     $user = User::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'password' => Hash::make($request->password),
    //     ]);
    //     if ($request->referer_id != null) {
    //         $mainReferer = User::find($request->referer_id);
    //         $refererRole = Role::find($mainReferer->role_id);

    //         // creating refer
    //         $refer = Refer::create([
    //             'referer_id' => $mainReferer->id,
    //             'registered_user_id' => $user->id
    //         ]);

    //         /**
    //          *
    //          * initializing  mfs member in first Node
    //          */
    //         $referNode1 = Refer::where('referer_id', $mainReferer->id)->latest()->get();

    //         if ($referNode1->count() >= 10 && $refererRole->role == 'normal_user') {
    //             $mainReferer->role_id = 2;
    //             $mainReferer->save();
    //             return "you are mfs member now";
    //         }

    //         /**
    //          *
    //          *
    //          * initializing mfs leader
    //          *
    //          */


    //         if ($referNode1->count() >= 4 && $refererRole->role == 'mfs_member') {
    //             $memberInNode2 = 0;
    //             $referNode2 = [];

    //             $result = $this->memberChecker($referNode1, $referNode2, $memberInNode2, 'mfs_member');
    //             $memberInNode2 = $result[0];
    //             $referNode2 = $result[1];

    //             /**
    //              *
    //              *  checking total mfs member in second node if it's<=4 then initializing msf leader to the first node referer
    //              *
    //              */

    //             $memberInNode3 = $memberInNode2 + 0;
    //             $referNode3 = [];

    //             if ($memberInNode2 >= 4) {

    //                 // /**
    //                 //  *
    //                 //  * third stage
    //                 //  *
    //                 //  */
    //                 // // if number of mm before more than 4; then initializing 4 mm to second node
    //                 // $memberInNode3 = 4;
    //                 // $result = $this->memberChecker($referNode2, $referNode3, $memberInNode3, 'mfs_member');
    //                 // $memberInNode3 = $result[0];
    //                 // $referNode3 = $result[1];
    //                 // // checking if 9 mm is exist or not under the user
    //                 // if ($memberInNode3 >= 9) {
    //                 //     // main referer role will leader
    //                 //     $mainReferer->role_id = 3;
    //                 //     $mainReferer->save();
    //                 //     return "you are leader now";
    //                 // } else {
    //                 //     $referNode4 = [];
    //                 //     $memberInNode4 = $referNode3;
    //                 //     $result = $this->memberChecker($referNode3, $referNode4, $memberInNode4, 'mfs_member');
    //                 //     $memberInNode4 = $result[0];
    //                 //     $referNode4 = $result[1];


    //                 // }




    //         }
    //         return back();
    //     }
    //     // event(new Registered($user));

    //     // Auth::login($user);

    //     // return redirect(RouteServiceProvider::HOME);
    // }


    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($request->referer_id != null) {
            $mainReferer = User::find($request->referer_id);
            $refererRole = Role::find($mainReferer->role_id);

            // Create a referral record
            Refer::create([
                'referer_id' => $mainReferer->id,
                'registered_user_id' => $user->id
            ]);

            // Update roles for all stages
            $data = $this->updateRoles($mainReferer, $refererRole);
            // if ($data != null) {

            return $data;
            // }

            return back();
        }

        // Handle events or authentication logic here
    }

    public function updateRoles($mainReferer, $refererRole)
    {
        /**
         *
         * second stage
         */

        $refer[1] = Refer::where('referer_id', $mainReferer->id)->get();
        $chainCount = Refer::where('referer_id', $mainReferer->id)->count();
        // // Check the conditions for each stage
        // if ($refererRole->role == 'normal_user' && $referNode[1]->count() >= 10) {
        //     $mainReferer->role_id = 2;
        //     $mainReferer->save();
        //     return "you are mfs member now";
        // }
        /**
         *
         *second stage
         */
        // $referNodes[1] = ; // Initialize an array to store refer nodes for each stage
        // $memberCounts[1] = 0; // Initialize an array to store member counts for each stage
        // Loop through all 20 stages
        $memberCount = 0;
        for ($stage = 1; $stage <= 20; $stage++) {
            // Check the conditions for each stage
            if ($refererRole->role == 'normal_user' && $chainCount >= 10) {
                $mainReferer->role_id = 2;
                $mainReferer->save();
                return "you are mfs member now";
            }

            // return $memberCount;
            if ($refererRole->role == 'mfs_member' && $chainCount >= 4 && $stage >= 2) {
                // in second stage if there are more than 4 member; then initializing 4 member at total
                if ($stage == 2 && $memberCount >= 4) {
                    $memberCount = 4;
                }
                $refer[$stage] = [];
                $result = $this->memberChecker($refer[$stage - 1], $refer[$stage], $memberCount, 'mfs_member');
                $memberCount = $result[0];
                $refer[$stage] = $result[1];
                // return $memberCount;
                if ($memberCount >= 4) {
                    //     $result = $this->memberChecker($refer[$stage], $refer[$stage + 1], $memberCount, 'mfs_member');
                    //     $memberCount = $result[0];
                    //     $refer[$stage] = $result[1];

                    if ($memberCount >= 9) {
                        $mainReferer->role_id = 3;
                        $mainReferer->save();
                        return "you are leader now";
                    }
                }

                // Update referNodeCount based on the logic for moving to the next stage
                // You should implement the logic for moving to the next stage here
                // $stage = $this->moveToNextStage($stage);

            }
        }
        return $memberCount;
    }

    // public function memberChecker($memberCount)
    // {
    //     $memberInNode = $memberCount;
    //     $referNode = [];

    //     // Logic for checking members and building refer nodes goes here

    //     return [$memberInNode, $referNode];
    // }

    public function moveToNextStage($currentStage)
    {
        // Implement logic for moving to the next stage and updating the count
        // For example, you might want to increment the count by a fixed value
        return $currentStage + 1;
    }


    public function memberChecker($beforeNode, $referNode, $setMember, $role)
    { {
            if ($setMember != null) {

                $memberInNode = $setMember;
            } else {
                $memberInNode = 0;
            }

            foreach ($beforeNode as $registered_user) {

                $registered_user = collect($registered_user);

                $user = User::where('id', $registered_user['registered_user_id'])->with('getRole')->first();

                $refer = Refer::where('referer_id', $user->id)->get();

                if ($user->getRole->role == $role) {
                    $memberInNode += 1;
                }
                if ($refer != null && $refer->count() > 0) {
                    $referNode = array_merge($refer->toArray(), $referNode);
                }

            }
            return [$memberInNode, $referNode];
        }



    }
}