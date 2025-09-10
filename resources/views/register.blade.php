@extends('moderator.layouts.layout1')

@section('title', 'Register - QR Attendance System')

@section('main-content')
<div class="login-container">
    <div class="login-card">
        <div class="login-header text-center">
            <h3><i class="fas fa-user-plus me-2"></i> Register</h3>
        </div>

        @if(session('success'))
            <div class="alert alert-success text-center">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger text-center">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <!-- ✅ Added enctype for file upload -->
        <form method="POST" action="{{ route('register.perform') }}" enctype="multipart/form-data">
            @csrf

            <div class="form-group mb-3">
                <label for="name" class="form-label">Full Name</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control neon-input" name="name" id="name" placeholder="Enter full name" required>
                </div>
            </div>

            <div class="form-group mb-3">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control neon-input" name="email" id="email" placeholder="Enter email address" required>
                </div>
            </div>

            <div class="form-group mb-3">
                <label for="phone_number" class="form-label">Phone</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                    <input type="text" class="form-control neon-input" name="phone_number" id="phone_number" placeholder="Enter phone number" required>
                </div>
            </div>

            <div class="form-group mb-3">
                <label for="user_type" class="form-label">User Type</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-users"></i></span>
                    <select name="user_type_id" class="form-control neon-input">
                        @foreach($userTypes as $type)
                            <option value="{{ $type->id }}">{{ ucfirst($type->name) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group mb-3">
                <label for="profile_image" class="form-label">Profile Image</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-image"></i></span>
                    <input type="file" class="form-control neon-input" name="profile_image" id="profile_image" accept="image/*">
                </div>
            </div>

            <div class="form-group mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control neon-input" name="password" id="password" placeholder="Enter password" required>
                </div>
            </div>

            <div class="form-group mb-3">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control neon-input" name="password_confirmation" id="password_confirmation" placeholder="Confirm password" required>
                </div>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-3d-neon">Register</button>
            </div>
        </form>

        <div class="text-center mt-3 text-white">
            <p>Already have an account? <a href="{{ route('login.test') }}" class="fw-semibold text-decoration-none neon-link">Login</a></p>
        </div>
    </div>
</div>

<!-- ✅ Reuse the same styles from login page -->
<style>
/* Background & Card */
.login-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
}

.login-card {
    background: #1c1c1c;
    padding: 2.5rem;
    border-radius: 20px;
    width: 100%;
    max-width: 520px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.5);
    border: 1px solid rgba(255,255,255,0.1);
}

.login-header h3 {
    color: #fff;
    font-weight: 700;
    margin-bottom: 1.5rem;
}

/* Inputs */
.neon-input {
    border-radius: 12px;
    border: 1px solid #444;
    background: #1c1c1c;
    color: #fff;
    padding: 12px;
    box-shadow: 0 0 5px #007bff inset;
    transition: 0.3s;
}

.neon-input:focus {
    outline: none;
    box-shadow: 0 0 8px #00ffff;
    border-color: #00ffff;
}

.input-group-text {
    background: #111;
    border: none;
    color: #00ffff;
}

/* Button */
.btn-3d-neon {
    background: linear-gradient(135deg, #00ffff, #007bff);
    color: #fff;
    font-weight: 600;
    border-radius: 50px;
    padding: 12px;
    font-size: 1.1rem;
    border: none;
    box-shadow: 0 6px #00bfff;
    text-transform: uppercase;
    transition: all 0.3s ease;
}

.btn-3d-neon:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px #00bfff;
    background: linear-gradient(135deg, #00bfff, #00ffff);
}

.btn-3d-neon:active {
    transform: translateY(2px);
    box-shadow: 0 4px #007bff;
}

/* Links */
.neon-link {
    color: #00ffff;
    transition: 0.3s;
}

.neon-link:hover {
    color: #00bfff;
    text-decoration: underline;
}

/* Labels */
label {
    color: #fff;
    font-weight: 500;
}
</style>
@endsection
