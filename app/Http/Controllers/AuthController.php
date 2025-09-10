<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use App\Models\UserType;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\TemporaryPass;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Check user
        $user = \App\Models\User::where('email', $credentials['email'])->first();
        if ($user && Hash::check($credentials['password'], $user->password)) {
            if (!$user->verified) {
                return back()->withErrors(['email' => 'Your account is not approved by admin yet.']);
            }
            Auth::guard('web')->login($user);

            if ($user->hasRole('moderator')) {
                return redirect()->route('moderator.dashboard');
            }
            return redirect()->route('user.pages.home');
        }

        // Check visitor
        $visitor = \App\Models\TemporaryPass::where('visitor_email', $credentials['email'])->first();
        if ($visitor && Hash::check($credentials['password'], $visitor->password)) {
            Auth::guard('visitor')->login($visitor);
            return redirect()->route('visitor.dashboard');
        }

        return back()->withErrors(['email' => 'Invalid email or password.']);
    }






    public function showRegisterForm()
    {
        $userTypes = UserType::all();
        return view('register', compact('userTypes'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email' => 'required|email|unique:temporary_passes,visitor_email',
            'phone_number'      => 'required|string|max:20',
            'user_type_id'      => 'required|exists:user_types,id',
            'password'          => 'required|string|min:6|confirmed',
            'profile_image'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Save with extension
        if ($request->user_type_id == 3) { // 2 ဆိုတာ visitor type ID ဆိုပါစို့
            $pass = new TemporaryPass();
            $pass->visitor_name = $request->name;
            $pass->visitor_email = $request->email;
            $pass->visitor_phone = $request->phone_number;
            $pass->password       = Hash::make($request->password);
            $pass->purpose = "Visitor Registration";
            // $pass->location_id = 1; // သတ်မှတ်ထားတဲ့ location ID
            // $pass->issued_by = $user->id; // သူ့ကိုယ်သူ register လုပ်တာ

            $pass->valid_from = Carbon::now();
            $pass->valid_until = Carbon::now()->addHours(6);
            $qrCodeImage = QrCode::format('svg')
                ->size(300)
                ->errorCorrection('H')
                ->generate((string) $request->email);

            $fileName = 'qrcodes/' . $request->email . '.svg';  // Add .svg extension

            Storage::disk('public')->put($fileName, $qrCodeImage);

            $pass->qr_code_path = $fileName;  // unique token

            if ($request->hasFile('profile_image')) {
                $image = $request->file('profile_image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/profile_images'), $imageName);
                $pass->profile_image = $imageName;
            }

            $pass->save();
            $role = Role::where('name', 'user')->first();
            if ($role) {
                DB::table('role_user')->insert([
                    'user_id' => $pass->id,
                    'role_id' => $role->id,

                ]);
            }
        } else {
            $request->validate([
                'name'        => 'required|string|max:255',
                'email'             => 'required|email|unique:users,email',
                'phone_number'      => 'required|string|max:20',
                'user_type_id'      => 'required|exists:user_types,id',
                'password'          => 'required|string|min:6|confirmed',
                'profile_image'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
            $user = new User();
            $user->name     = $request->name;
            $user->email          = $request->email;
            $user->phone_number   = $request->phone_number;
            $user->user_type_id   = $request->user_type_id;
            $user->password       = Hash::make($request->password);
            $user->verified       = false;

            // ✅ Handle profile image
            if ($request->hasFile('profile_image')) {
                $image = $request->file('profile_image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/profile_images'), $imageName);
                $user->profile_image = $imageName;
            }

            $user->save();
            $role = Role::where('name', 'user')->first();
            if ($role) {
                DB::table('role_user')->insert([
                    'user_id' => $user->id,
                    'role_id' => $role->id,

                ]);
            }
        }



        // 3️⃣ Create Temporary Pass for visitor (valid for 6 hours)


        return redirect()->route('register.test')->with('success', 'Registration successful! Please wait for admin approval.');
    }


    // ✅ Logout Method
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.test');
    }

    // ✅ Forgot Password
    public function forgot_password(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', 'Password reset link sent!')
            : back()->withErrors(['email' => $status]);
    }

    // ✅ Reset Password
    public function reset_password(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect('/login')->with('status', 'Password has been reset!')
            : back()->withErrors(['email' => $status]);
    }
    /**
     * Show the moderator login form.
     */
    public function showLoginForm()
    {
        return view('login');
    }
}