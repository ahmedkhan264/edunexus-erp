<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - EduNexus ERP + LMS</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --edunexus-blue: #1e3a8a;
            --edunexus-light-blue: #3b82f6;
            --edunexus-green: #10b981;
            --edunexus-light-green: #34d399;
            --edunexus-white: #ffffff;
            --edunexus-gray: #6b7280;
        }
        
        body {
            background: linear-gradient(135deg, var(--edunexus-blue) 0%, var(--edunexus-light-blue) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-card {
            background: var(--edunexus-white);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--edunexus-green) 0%, var(--edunexus-light-green) 100%);
            padding: 40px 30px;
            text-align: center;
            color: var(--edunexus-white);
        }
        
        .logo {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .tagline {
            font-size: 0.9rem;
            opacity: 0.9;
            margin: 0;
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .form-label {
            color: var(--edunexus-blue);
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .form-control {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--edunexus-light-blue);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .input-group-text {
            background: var(--edunexus-light-blue);
            border: 2px solid var(--edunexus-light-blue);
            color: var(--edunexus-white);
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--edunexus-blue) 0%, var(--edunexus-light-blue) 100%);
            color: var(--edunexus-white);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(30, 58, 138, 0.3);
            color: var(--edunexus-white);
        }
        
        .btn-login:disabled {
            opacity: 0.6;
            transform: none;
        }
        
        .form-check-input:checked {
            background-color: var(--edunexus-green);
            border-color: var(--edunexus-green);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            font-size: 0.9rem;
        }
        
        .alert-danger {
            background: #fef2f2;
            color: #dc2626;
        }
        
        .alert-success {
            background: #f0fdf4;
            color: #16a34a;
        }
        
        .forgot-password {
            color: var(--edunexus-light-blue);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .forgot-password:hover {
            color: var(--edunexus-blue);
            text-decoration: underline;
        }
        
        .footer-text {
            text-align: center;
            color: var(--edunexus-gray);
            font-size: 0.8rem;
            margin-top: 20px;
        }
        
        .spinner-border {
            width: 1rem;
            height: 1rem;
            border-width: 2px;
        }
        
        @media (max-width: 576px) {
            .login-card {
                margin: 10px;
            }
            
            .login-header {
                padding: 30px 20px;
            }
            
            .login-body {
                padding: 30px 20px;
            }
            
            .logo {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-graduation-cap"></i> EduNexus
                </div>
                <p class="tagline">Institute ERP + LMS Platform</p>
            </div>
            
            <div class="login-body">
                @if ($errors->any())
                    <div class="alert alert-danger mb-4">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ $errors->first() }}
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger mb-4">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                    </div>
                @endif
                
                @if(session('success'))
                    <div class="alert alert-success mb-4">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                    </div>
                @endif
                
                <form method="POST" action="{{ route('login.post') }}" id="loginForm">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-2"></i>Email Address
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   placeholder="Enter your email"
                                   autocomplete="email"
                                   autofocus
                                   required>
                        </div>
                        @error('email')
                            <div class="invalid-feedback d-block">
                                <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-2"></i>Password
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Enter your password"
                                   autocomplete="current-password"
                                   required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye" id="passwordIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">
                                <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>
                    
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-3">
                        <button type="submit" class="btn btn-login" id="loginBtn">
                            <span id="loginText">
                                <i class="fas fa-sign-in-alt me-2"></i>Sign In
                            </span>
                            <span id="loginSpinner" class="d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                Signing in...
                            </span>
                        </button>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="#" class="forgot-password">
                            <i class="fas fa-question-circle me-1"></i>Forgot your password?
                        </a>
                    </div>
                </form>
                
                <div class="footer-text">
                    <p class="mb-0">© 2024 EduNexus. All rights reserved.</p>
                    <p class="mb-0">Version 1.0.0</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('passwordIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.remove('fa-eye');
                passwordIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.remove('fa-eye-slash');
                passwordIcon.classList.add('fa-eye');
            }
        });
        
        // Form submission loading state
        document.getElementById('loginForm').addEventListener('submit', function() {
            const loginBtn = document.getElementById('loginBtn');
            const loginText = document.getElementById('loginText');
            const loginSpinner = document.getElementById('loginSpinner');
            
            loginBtn.disabled = true;
            loginText.classList.add('d-none');
            loginSpinner.classList.remove('d-none');
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Clear form errors on input
        document.querySelectorAll('.form-control').forEach(function(input) {
            input.addEventListener('input', function() {
                this.classList.remove('is-invalid');
                const feedback = this.parentElement.parentElement.querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
