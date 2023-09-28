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
            $firstNodeRefers = Refer::where('referer_id', $mainReferer->id)->latest()->get();

            if ($firstNodeRefers->count() >= 10 && $refererRole->role == 'normal_user') {
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
            // return $firstNodeRefers;

            if ($firstNodeRefers->count() >= 4 && $refererRole->role == 'mfs_member') {
                $secondNodeMember = 0;
                $secondNodeRefers = [];

                foreach ($firstNodeRefers as $registered_user) {
                    // in second node
                    $user = User::where('id', $registered_user['registered_user_id'])->with('getRole')->first();
                    // echo $user . "<br>";
                    $refer = Refer::where('referer_id', $user->id)->latest()->get();
                    if ($user->getRole->role == 'mfs_member') {
                        $secondNodeMember += 1;
                    }
                    if ($refer != null && $refer->count() > 0) {
                        $secondNodeRefers = array_merge($refer->toArray(), $secondNodeRefers);
                    }

                }

                /**
                 *
                 *  checking total mfs member in second node if it's<=4 then initializing msf leader to the first node referer
                 *
                 */

                $thirdNodeMember = $secondNodeMember + 0;
                $thirdNodeRefers = [];

                if ($secondNodeMember >= 4) {
                    // if number of mm before more than 4; then initiazing 4 mm to second node
                    $thirdNodeMember = 4;
                    foreach ($secondNodeRefers as $registered_user) {
                        // in third node
                        $registered_user = collect($registered_user);

                        $user = User::where('id', $registered_user['registered_user_id'])->with('getRole')->first();
                        // return $registered_user['registered_user_id'];
                        $refer = Refer::where('referer_id', $user->id)->get();
                        // return $refer;
                        // echo $refer;
                        // echo $user->id . "<br>";
                        if ($user->getRole->role == 'mfs_member') {
                            $thirdNodeMember += 1;
                            // echo $user . "<br>";
                        }
                        if ($refer != null && $refer->count() > 0) {
                            $thirdNodeRefers = array_merge($refer->toArray(), $thirdNodeRefers);
                        }
                    }
                    // return $thirdNodeRefers;
                    if ($thirdNodeMember >= 8) {
                        // main referer role will leader
                        $mainReferer->role_id = 3;
                        $mainReferer->save();
                        return "you are leader now";
                    }
                }



            }
            return back();
        }
        // event(new Registered($user));

        // Auth::login($user);

        // return redirect(RouteServiceProvider::HOME);
    }
}