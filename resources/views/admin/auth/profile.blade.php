@extends('admin.layouts.app')

@section('content')
    <div class="admin-profile-container">
        <div class="card">
            <h2 class="title">Admin Profile — Password Change</h2>

            @if(session('status'))
                <div class="alert success">{{ session('status') }}</div>
            @endif

            @if(session('error'))
                <div class="alert error">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('passwordUpdate') }}" class="form" novalidate>
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input id="current_password" type="password" name="current_password" required autocomplete="current-password">
                    @error('current_password')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="password">New Password</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password">
                    @error('password')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm New Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn">Change Password</button>
                    <button type="button" id="togglePasswords" class="btn alt">Show/Hide</button>
                </div>
            </form>

            <p class="note">Note: No header or footer included — this is a minimal Blade partial you can place inside your admin layout.</p>
        </div>
    </div>

    <style>
        /* Minimal centered card layout — adapt to your app's CSS or Tailwind */
        .admin-profile-container{display:flex;align-items:center;justify-content:center;min-height:60vh;padding:24px}
        .card{width:420px;border-radius:8px;box-shadow:0 6px 20px rgba(0,0,0,0.08);padding:24px;background:#fff}
        .title{margin:0 0 12px;font-size:20px;text-align:center}
        .form-group{margin-bottom:14px}
        label{display:block;margin-bottom:6px;font-size:13px;color:#333}
        input[type="password"]{width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:6px;font-size:14px}
        .field-error{color:#b00020;margin-top:6px;font-size:13px}
        .alert{padding:10px;border-radius:6px;margin-bottom:12px;font-size:14px}
        .alert.success{background:#e6ffef;border:1px solid #b7f0cc;color:#0b6b3a}
        .alert.error{background:#fff0f0;border:1px solid #f0b7b7;color:#8a1a1a}
        .form-actions{display:flex;gap:8px;align-items:center}
        .btn{padding:10px 14px;border-radius:6px;border:0;background:#2563eb;color:#fff;cursor:pointer}
        .btn.alt{background:transparent;border:1px solid #cbd5e1;color:#111}
        .note{font-size:12px;color:#666;margin-top:12px;text-align:center}
    </style>

    <script>
        // Small client-side helper — still rely on server validation
        document.getElementById('togglePasswords').addEventListener('click', function(){
            ['current_password','password','password_confirmation'].forEach(function(id){
                const el = document.getElementById(id);
                if(!el) return;
                el.type = (el.type === 'password') ? 'text' : 'password';
            });
        });

        // Optional: simple front-end check before submit
        document.querySelector('.form').addEventListener('submit', function(e){
            const p = document.getElementById('password').value;
            const pc = document.getElementById('password_confirmation').value;
            if(p.length < 8){
                e.preventDefault();
                alert('Password must be at least 8 characters.');
                return;
            }
            if(p !== pc){
                e.preventDefault();
                alert('Passwords do not match.');
            }
        });
    </script>
@endsection
