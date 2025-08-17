<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class SetupPasswordController extends Controller
{
    /**
     * Show the form to set a new password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function create(Request $request)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'The invitation link is invalid or has expired.');
        }

        return Inertia::render('auth/SetupPassword', [
            'email' => $request->email,
            'token' => $request->token,
        ]);
    }

    /**
     * Handle the password setup request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::broker('users')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                    'email_verified_at' => now(),
                ])->save();

                // Update client status to active
                if ($user->client) {
                    $user->client->update(['status' => 'Active']);
                }

                // Update agency status to active
                if ($user->agency) {
                    $user->agency->update(['status' => 'Active']);
                }

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully set, we will redirect the user to the
        // client dashboard and log them in. If there is an error we can redirect
        // them back to the password setup form with an error message.
        if ($status === Password::PASSWORD_RESET) {
            // Get the user to log them in
            $user = User::where('email', $request->email)->first();

            // Log the user in
            Auth::login($user);

            // Redirect based on role/relations
            if ($user->agency) {
                return redirect()->route('agency.dashboard')->with('status', __($status));
            }
            if ($user->client) {
                return redirect()->route('client.dashboard')->with('status', __($status));
            }

            // Fallback
            return redirect()->route('home')->with('status', __($status));
        }

        // If there was an error, redirect back with the error message
        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
