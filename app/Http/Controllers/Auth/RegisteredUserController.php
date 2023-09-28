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
            $referer = User::find($request->referer_id);
            $refererRole = Role::find($referer->role_id);

            // creating refer
            $refer = Refer::create([
                'referer_id' => $referer->id,
                'registered_user_id' => $user->id
            ]);

            /**
             *
             * setting mfs member in first Node
             */
            $firstNodeReferer = Refer::where('referer_id', $referer->id);

            if ($firstNodeReferer->count() >= 10 && $refererRole->role == 'normal_user') {
                $referer->role_id = 2;
                $referer->save();
                return "you are mfs member now";
            }

            /**
             *
             *
             * setting mfs leader
             *
             */

            if ($firstNodeReferer->count() >= 4 && $refererRole->role == 'mfs_member') {
                $getReferredByReferer = $firstNodeReferer->latest()->get();


                $secondNodeMember = 0;
                $secondNodeRefer = [];
                foreach ($getReferredByReferer as $ref) {
                    // in second node
                    $user = User::where('id', $ref->registered_user_id)->with('getRole')->first();
                    if ($user->getRole->role == 'mfs_member') {
                        $secondNodeMember += 1;
                    }
                    $secondNodeRefer[] = $user;
                }

                /**
                 *
                 * if number is mfs member is 4 then check in 3rd node and counting is going
                 *
                 */


                $thirdNodeMember = $secondNodeMember;
                $thirdNodeReferer = [];
                if ($secondNodeMember >= 4) {
                    foreach ($secondNodeRefer as $ref) {
                        // in second node
                        $user = User::where('id', $ref->registered_user_id)->with('getRole')->first();
                        if ($user->getRole->role == 'mfs_member') {
                            $thirdNodeMember += 1;
                        }
                    }
                    if ($thirdNodeMember >= 8 && $refererRole->role == 'mfs_member') {
                        $refererRole->role_id = 3;
                        $refererRole->save();
                        return "you are mfs leader now";
                    }
                } else {
                    foreach ($secondNodeRefer as $ref) {
                        // in second node
                        $user = User::where('id', $ref->registered_user_id)->with('getRole')->first();
                        if ($user->getRole->role == 'mfs_member') {
                            $secondNodeMember += 1;
                        }

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