<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\GatePassLog;

class UserController extends Controller
{
    // Show all users
    public function index()
    {
        $users = User::with('userType')->get();
        return view('admin.pages.view-users', compact('users'));
    }
    // public function view()
    // {
    //     $index=Auth::user();
    //     $users=User::where('id',$index->id)->get();
    //     return view('user.pages.dashboard',compact('users'));
    // }
    public function create()
    {
        $roles = Role::all(); // or filtered as needed
        $userTypes = UserType::where('id', '!=', 3)->get();
        return view('admin.pages.add-user', compact('roles', 'userTypes'));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|confirmed|min:6',
            'user_type_id'  => 'required|integer',
            'role'          => 'required|integer|exists:roles,id',
            'phone_number'  => 'nullable|string|max:15',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        DB::beginTransaction();
        try {
            // Create user
            $user = new User();
            $user->name         = $validated['name'];
            $user->email        = $validated['email'];
            $user->password     = bcrypt($validated['password']);
            $user->user_type_id = $validated['user_type_id'];
            $user->phone_number = $validated['phone_number'] ?? null;
            $user->verified     = $request->has('verified');

            // Profile image
            if ($request->hasFile('profile_image')) {
                $image = $request->file('profile_image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/profile_images'), $imageName);
                $user->profile_image = $imageName;
            }

            $user->save(); // ✅ Save first to get user ID

            // Assign role
            $user->roles()->attach($validated['role']);

            // Generate QR only if verified
            if ($user->verified) {
                $qrCodeImage = QrCode::format('svg')
                    ->size(300)
                    ->errorCorrection('H')
                    ->generate((string) $user->id);

                $fileName = 'qrcodes/' . $user->id . '.svg';
                Storage::disk('public')->put($fileName, $qrCodeImage);

                $user->qr_code_path = $fileName; // ✅ use qr_code_path consistently
                $user->save();
            }

            DB::commit();
            return redirect()->route('users.view')->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error: ' . $e->getMessage()]);
        }
    }



    // Show edit form
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $userRoleId = $user->roles()->pluck('roles.id')->first();
        $userTypes = UserType::where('id', '!=', 3)->get();

        return view('admin.pages.edit-user', compact('user', 'userTypes', 'userRoleId'));
    }


    // Update user
    //     public function update(Request $request, $id)
    // {
    //     $request->validate([
    //         'name' => 'required|string',
    //         'email' => 'required|email',
    //         'phone_number' => 'nullable|string',
    //         'user_type_id' => 'required|exists:user_types,id',
    //         'role' => 'required|in:admin,moderator,user',
    //         'verified' => 'boolean',
    //     ]);

    //     $user = User::findOrFail($id);
    //     $user->name = $request->name;
    //     $user->email = $request->email;
    //     $user->phone_number = $request->phone_number;
    //     $user->user_type_id = $request->user_type_id;
    //     $user->role = $request->role;
    //     $user->verified = $request->verified ?? 0;
    //     $user->save();

    //     return redirect()->route('users.view')->with('success', 'User updated successfully!');
    // }


    // Delete user
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.view')->with('success', 'User deleted successfully!');
    }

    //     public function show(Request $request)
    // {
    //     $id = $request->query('id'); // get ?id=3
    //     $user = User::findOrFail($id);

    //     return view('user.profile', compact('user'));
    // }

    // public function generateQrCode($id)
    // {
    //     $user = User::findOrFail($id);
    //     $filename = 'user-' . $user->id . '.png';

    // Generate QR with user ID or any unique value
    // $qrImage = QrCode::format('png')->size(300)->generate($user->id);

    // Save to public storage
    // Storage::disk('public')->put('qr_codes/' . $filename, $qrImage);

    // Update user record
    //     $user->qr_code = $filename;
    //     $user->save();

    //     return redirect()->back()->with('success', 'QR Code Generated!');
    // }

    public function show(Request $request)
    {
        $user = User::findOrFail($request->id);
        return view('user.pages.details', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->name         = $request->name;
        $user->email        = $request->email;
        $user->phone_number = $request->phone_number;
        $user->user_type_id = $request->user_type_id;
        $user->verified     = $request->verified;

        $user->save();
        $user->roles()->sync([$request->role]); // sync with role ID

        return redirect()->route('users.view', ['id' => $id])
            ->with('success', 'Profile updated successfully.');
    }


    public function downloadQR($id)
    {
        $user = User::findOrFail($id);

        if (!$user->qr_code_path) {
            return back()->withErrors(['QR Code not available.']);
        }

        // Correct path to storage/app/public
        $filePath = storage_path('app/public/' . $user->qr_code_path);

        if (!file_exists($filePath)) {
            return back()->withErrors(['QR code file not found.']);
        }

        // Force download
        return response()->download($filePath, basename($filePath), [
            'Content-Type' => mime_content_type($filePath)
        ]);
    }

    // use Illuminate\Support\Str;

    // public function approve($id)
    // {
    //     $user = User::findOrFail($id);
    //     $user->verified = true;

    //     // Only generate QR token if not exists
    //     if (!$user->qr_token) {
    //         $user->qr_token = Str::uuid(); // Or Str::random(32);
    //     }

    //     $user->save();

    //     return redirect()->back()->with('success', 'User approved and QR Code generated.');
    // }
    public function showQRCode()
    {
        $user = Auth::user();

        if (!$user->qr_token) {
            return redirect()->back()->with('error', 'No QR code found.');
        }

        return view('user.pages.my-qrcode', compact('user'));
    }
    public function myQRCode()
    {
        $index = Auth::guard('web')->user();
        $user = User::findOrFail($index->id);
        // $userId = Auth::id();

        $attendanceLogs = GatePassLog::where('user_id', $index->id)
            ->orderBy('created_at', 'desc')
            ->take(5) // show latest 5 records
            ->get();
        return view('user.pages.my-qrcode', compact('user', 'attendanceLogs'));
    }

    public function profile()
    {
        return view('user.pages.profile');
    }
}