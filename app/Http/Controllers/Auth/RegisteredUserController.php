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

            // creating refer
            $refer = Refer::create([
                'referer_id' => $mainReferer->id,
                'registered_user_id' => $user->id
            ]);

            /**
             *
             * initializing  mfs member in first Node
             */
            $referNode1 = Refer::where('referer_id', $mainReferer->id)->latest()->get();

            if ($referNode1->count() >= 10 && $refererRole->role == 'normal_user') {
                $mainReferer->role_id = 2;
                $mainReferer->save();
                return "you are mfs member now";
            }

            /**
             *
             *
             * initializing mfs leader
             *
             */


            if ($referNode1->count() >= 4 && $refererRole->role == 'mfs_member') {
                $memberInNode2 = 0;
                $referNode2 = [];

                $result = $this->memberChecker($referNode1, $referNode2, $memberInNode2, 'mfs_member');
                $memberInNode2 = $result[0];
                $referNode2 = $result[1];

                /**
                 *
                 *  checking total mfs member in second node if it's<=4 then initializing msf leader to the first node referer
                 *
                 */

                $memberInNode3 = $memberInNode2 + 0;
                $referNode3 = [];

                if ($memberInNode2 >= 4) {

                    // /**
                    //  *
                    //  * third stage
                    //  *
                    //  */
                    // // if number of mm before more than 4; then initializing 4 mm to second node
                    // $memberInNode3 = 4;
                    // $result = $this->memberChecker($referNode2, $referNode3, $memberInNode3, 'mfs_member');
                    // $memberInNode3 = $result[0];
                    // $referNode3 = $result[1];
                    // // checking if 9 mm is exist or not under the user
                    // if ($memberInNode3 >= 9) {
                    //     // main referer role will leader
                    //     $mainReferer->role_id = 3;
                    //     $mainReferer->save();
                    //     return "you are leader now";
                    // } else {
                    //     $referNode4 = [];
                    //     $memberInNode4 = $referNode3;
                    //     $result = $this->memberChecker($referNode3, $referNode4, $memberInNode4, 'mfs_member');
                    //     $memberInNode4 = $result[0];
                    //     $referNode4 = $result[1];


                    // }


                    /* -------------------------------------------------------------------------- */
                    /*                                  gpt code                                  */
                    /* -------------------------------------------------------------------------- */
                    $referNodes = []; // Initialize an array to store refer nodes for each stage
                    $memberCounts = []; // Initialize an array to store member counts for each stage

                    for ($stage = 1; $stage <= 20; $stage++) {
                        // Initialize member count for the current stage
                        $memberCounts[$stage] = 0;

                        // Initialize refer nodes for the current stage
                        $referNodes[$stage] = [];

                        // Calculate the previous stage
                        $previousStage = $stage - 1;

                        if ($stage == 1) {
                            // For the first stage, perform a fixed initialization
                            $memberCounts[$stage] = 4;
                            $result = $this->memberChecker([], $referNodes[$stage], $memberCounts[$stage], 'mfs_member');

                        } else {
                            // For subsequent stages, use the dynamic approach
                            $memberCounts[$stage] = $memberCounts[$previousStage]; // Initialize member count based on the previous stage

                            $result = $this->memberChecker($referNodes[$previousStage], $referNodes[$stage], $memberCounts[$stage], 'mfs_member');
                        }

                        $memberCounts[$stage] = $result[0];
                        $referNodes[$stage] = $result[1];



                        // Check if the required member count (e.g., 9) is reached for a stage
                        if ($memberCounts[$stage] >= 9) {
                            // Update the role or perform other actions for this stage if needed
                            // Example: $mainReferer->role_id = 3;
                            // ...
                            // Break the loop or return if necessary
                            return "you are leader now";
                            // break;
                        }
                    }

                    /* -------------------------------------------------------------------------- */
                    /*                                  gpt code                                  */
                    /* -------------------------------------------------------------------------- */


                }



            }
            return back();
        }
        // event(new Registered($user));

        // Auth::login($user);

        // return redirect(RouteServiceProvider::HOME);
    }
    public function memberChecker($beforeNode, $referNode, $setMember, $role)
    {
        $memberInNode = $setMember;

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