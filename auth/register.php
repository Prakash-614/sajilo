<?php


require_once "../config/config.php";

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'invalid_request';
    }
    
    // Sanitize inputs
    $full_name = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_type = sanitize($_POST['user_type'] ?? 'buyer');
    $terms = isset($_POST['terms']);
    
    // Validation
    if (empty($full_name)) {
        $errors[] = 'full_name_empty';
    }
    
    if (empty($email) || !validate_email($email)) {
        $errors[] = 'email_invalid';
    }
    
    // Check if phone is provided and validate
    if (!empty($phone) && !validate_phone($phone)) {
        $errors[] = 'phone_invalid';
    }
    
    if (empty($password)) {
        $errors[] = 'password_empty';
    } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = 'password_short';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'password_mismatch';
    }
    
    if (!$terms) {
        $errors[] = 'terms_not_accepted';
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $sql = "SELECT user_id FROM users WHERE email = ?";
        $existing_user = db_fetch_one($sql, "s", [$email]);
        
        if ($existing_user) {
            $errors[] = 'email_exists';
        }
    }
    
    // Check if username (derived from email) already exists
    if (empty($errors)) {
        $username = strtolower(explode('@', $email)[0]) . rand(100, 999);
        $sql = "SELECT user_id FROM users WHERE username = ?";
        $existing_username = db_fetch_one($sql, "s", [$username]);
        
        // Generate unique username if already exists
        while ($existing_username) {
            $username = strtolower(explode('@', $email)[0]) . rand(100, 999);
            $existing_username = db_fetch_one($sql, "s", [$username]);
        }
    }
    
    // Register user if no errors
    if (empty($errors)) {
        // Hash password
        $hashed_password = hash_password($password);
        
        // Generate verification token
        $verification_token = generate_token();
        
        // Determine role based on user type
        $role = ($user_type === 'seller') ? 'seller' : 'buyer';
        
        // Insert user into database
      
// Insert user into database
$sql = "INSERT INTO users (username, email, password, full_name, phone, role, verification_token, is_verified) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = db_execute($sql, "sssssssi", [
    $username, 
    $email, 
    $hashed_password, 
    $full_name, 
    $phone, 
    $role, 
    $verification_token, 
    1  // Set to 1 to auto-verify accounts
]);
        
        if ($stmt) {
            $stmt->close();
            
            // Send verification email (optional - comment out if email not configured)
            // send_verification_email($email, $verification_token);
            
            $success = true;
            set_flash('success', 'Registration successful! Please login to continue.');
            
            // Redirect to login page after 2 seconds
            header("refresh:2;url=" . BASE_URL . "/auth/login.php");
        } else {
            $errors[] = 'registration_failed';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(100px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
            20%, 40%, 60%, 80% { transform: translateX(10px); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.1); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); opacity: 1; }
        }
        .register-container { animation: fadeInUp 0.8s ease-out; }
        .side-illustration { animation: slideInRight 1s ease-out; }
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .input-group { position: relative; }
        .input-group input:focus + label,
        .input-group input:not(:placeholder-shown) + label,
        .input-group select:focus + label,
        .input-group select:not([value=""]) + label {
            transform: translateY(-28px) scale(0.85);
            color: #667eea;
        }
        .input-group label {
            position: absolute;
            left: 16px;
            top: 16px;
            transition: all 0.3s ease;
            pointer-events: none;
            color: #9ca3af;
            background: white;
            padding: 0 4px;
        }
        .input-error {
            border-color: #ef4444 !important;
            animation: shake 0.5s ease;
        }
        .notification {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 9999;
            animation: slideInRight 0.3s ease;
        }
        .success-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            animation: fadeIn 0.3s ease;
        }
        .success-overlay.active {
            display: flex;
        }
        .success-content {
            text-align: center;
            color: white;
        }
        .success-icon {
            font-size: 120px;
            animation: bounceIn 0.6s ease;
            display: inline-block;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-50 via-white to-indigo-50 min-h-screen">
    <!-- Success Overlay -->
    <div id="successOverlay" class="success-overlay <?= $success ? 'active' : '' ?>">
        <div class="success-content">
            <div class="success-icon">✅</div>
            <div class="text-4xl font-bold mb-4">Registration Successful!</div>
            <div class="text-xl opacity-90 mb-6">Your account has been created successfully</div>
            <div class="text-lg">Redirecting to login page...</div>
        </div>
    </div>

    <!-- Notification Container -->
    <div id="notificationContainer">
        <?php if (!empty($errors)): ?>
            <div class="notification bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg max-w-md">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-bold mb-1">Registration Failed</div>
                        <div class="text-sm">
                            <?php
                            $error_messages = [
                                'full_name_empty' => 'Full name is required',
                                'email_invalid' => 'Please enter a valid email address',
                                'email_exists' => 'This email is already registered',
                                'phone_invalid' => 'Please enter a valid Nepal phone number (98XXXXXXXX)',
                                'password_empty' => 'Password is required',
                                'password_short' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters',
                                'password_mismatch' => 'Passwords do not match',
                                'terms_not_accepted' => 'You must accept the terms and conditions',
                                'invalid_request' => 'Invalid request. Please try again',
                                'registration_failed' => 'Registration failed. Please try again'
                            ];
                            
                            foreach ($errors as $error) {
                                echo $error_messages[$error] ?? $error;
                                break; // Show only first error
                            }
                            ?>
                        </div>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">✕</button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Header -->
    <header class="bg-white shadow-md fixed top-0 left-0 right-0 z-50">
        <!-- Top Bar -->
        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-2">
            <div class="container mx-auto px-4 flex justify-between items-center text-sm">
                <div class="flex items-center gap-6">
                    <span class="hidden md:inline">📍 Delivering across Nepal</span>
                    <span class="hidden md:inline">📞 <?= CONTACT_PHONE ?></span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="#" class="hover:underline">Help</a>
                    <a href="#" class="hover:underline">Track Order</a>
                </div>
            </div>
        </div>

        <!-- Main Navigation -->
        <nav class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="<?= BASE_URL ?>/project.php" class="flex items-center gap-2 text-2xl font-bold text-purple-600">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z" />
                    </svg>
                    <span><?= SITE_NAME ?></span>
                </a>
                <a href="<?= BASE_URL ?>/auth/login.php" class="text-purple-600 hover:text-purple-700 font-semibold transition flex items-center gap-2">
                    <span>Already have an account? Login</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </nav>
    </header>

    <!-- Spacer for fixed header -->
    <div class="h-32"></div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-center min-h-[calc(100vh-200px)]">
            <div class="grid md:grid-cols-2 gap-8 lg:gap-12 max-w-6xl w-full items-center">
                <!-- Left Side - Illustration -->
                <div class="side-illustration hidden md:flex items-center justify-center">
                    <div class="text-center">
                        <div class="text-[180px] leading-none mb-6 inline-block">🛍️</div>
                        <h2 class="text-4xl font-bold gradient-text mb-4">Join <?= SITE_NAME ?> Today!</h2>
                        <p class="text-gray-600 text-lg max-w-md mx-auto">
                            Start shopping or selling on Nepal's trusted digital marketplace
                        </p>
                        <div class="mt-8 grid grid-cols-3 gap-4 text-center">
                            <div>
                                <div class="text-3xl mb-2">🔒</div>
                                <p class="text-sm font-semibold text-gray-700">Secure</p>
                            </div>
                            <div>
                                <div class="text-3xl mb-2">✅</div>
                                <p class="text-sm font-semibold text-gray-700">Verified</p>
                            </div>
                            <div>
                                <div class="text-3xl mb-2">⚡</div>
                                <p class="text-sm font-semibold text-gray-700">Fast</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side - Registration Form -->
                <div class="register-container">
                    <div class="bg-white rounded-2xl shadow-2xl p-6 md:p-8 lg:p-10">
                        <div class="text-center mb-8">
                            <h1 class="text-3xl lg:text-4xl font-bold gradient-text mb-3">Create Your Account</h1>
                            <p class="text-gray-600">Join thousands of happy customers and sellers</p>
                        </div>

                        <form method="POST" action="" id="registerForm" class="space-y-5">
                            <?= csrf_field() ?>
                            
                            <!-- Full Name -->
                            <div class="input-group">
                                <input type="text" id="full_name" name="full_name" placeholder=" " 
                                    class="w-full px-4 py-4 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 transition <?= in_array('full_name_empty', $errors) ? 'input-error' : '' ?>"
                                    value="<?= clean($_POST['full_name'] ?? '') ?>" required />
                                <label for="full_name">Full Name</label>
                            </div>

                            <!-- Email -->
                            <div class="input-group">
                                <input type="email" id="email" name="email" placeholder=" " 
                                    class="w-full px-4 py-4 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 transition <?= in_array('email_invalid', $errors) || in_array('email_exists', $errors) ? 'input-error' : '' ?>"
                                    value="<?= clean($_POST['email'] ?? '') ?>" required />
                                <label for="email">Email Address</label>
                            </div>

                            <!-- Phone -->
                            <div class="input-group">
                                <input type="tel" id="phone" name="phone" placeholder=" " 
                                    class="w-full px-4 py-4 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 transition <?= in_array('phone_invalid', $errors) ? 'input-error' : '' ?>"
                                    value="<?= clean($_POST['phone'] ?? '') ?>" />
                                <label for="phone">Phone Number (Optional)</label>
                                <div class="text-xs text-gray-500 mt-1 ml-1">Format: 98XXXXXXXX or 97XXXXXXXX</div>
                            </div>

                            <!-- Password -->
                            <div class="input-group">
                                <input type="password" id="password" name="password" placeholder=" " 
                                    class="w-full px-4 py-4 pr-12 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 transition <?= in_array('password_empty', $errors) || in_array('password_short', $errors) ? 'input-error' : '' ?>" required />
                                <label for="password">Password</label>
                                <button type="button" id="togglePassword" class="absolute right-4 top-4 text-gray-500 hover:text-purple-600 transition">
                                    <svg id="eyeIconPassword" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Confirm Password -->
                            <div class="input-group">
                                <input type="password" id="confirm_password" name="confirm_password" placeholder=" " 
                                    class="w-full px-4 py-4 pr-12 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 transition <?= in_array('password_mismatch', $errors) ? 'input-error' : '' ?>" required />
                                <label for="confirm_password">Confirm Password</label>
                                <button type="button" id="toggleConfirmPassword" class="absolute right-4 top-4 text-gray-500 hover:text-purple-600 transition">
                                    <svg id="eyeIconConfirm" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Account Type -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">I want to register as:</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <label class="relative flex items-center p-4 border-2 border-gray-300 rounded-xl cursor-pointer hover:border-purple-500 transition group">
                                        <input type="radio" name="user_type" value="buyer" <?= (!isset($_POST['user_type']) || $_POST['user_type'] === 'buyer') ? 'checked' : '' ?> class="w-4 h-4 text-purple-600" />
                                        <div class="ml-3">
                                            <div class="font-semibold text-gray-800 group-hover:text-purple-600 transition">Customer</div>
                                            <div class="text-xs text-gray-500">Buy products</div>
                                        </div>
                                    </label>
                                    <label class="relative flex items-center p-4 border-2 border-gray-300 rounded-xl cursor-pointer hover:border-purple-500 transition group">
                                        <input type="radio" name="user_type" value="seller" <?= (isset($_POST['user_type']) && $_POST['user_type'] === 'seller') ? 'checked' : '' ?> class="w-4 h-4 text-purple-600" />
                                        <div class="ml-3">
                                            <div class="font-semibold text-gray-800 group-hover:text-purple-600 transition">Seller</div>
                                            <div class="text-xs text-gray-500">Sell products</div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Terms -->
                            <div class="flex items-start">
                                <input type="checkbox" id="terms" name="terms" required 
                                    class="w-4 h-4 mt-1 text-purple-600 border-gray-300 rounded focus:ring-purple-500 cursor-pointer" />
                                <label for="terms" class="ml-2 text-sm text-gray-600">
                                    I agree to the <a href="#" class="text-purple-600 hover:underline font-semibold">Terms of Service</a> and <a href="#" class="text-purple-600 hover:underline font-semibold">Privacy Policy</a>
                                </label>
                            </div>

                            <!-- Register Button -->
                            <button type="submit" id="submitBtn" 
                                class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-4 rounded-xl font-bold text-lg hover:from-purple-700 hover:to-indigo-700 transition transform hover:scale-[1.02] shadow-lg">
                                Create Account
                            </button>
                        </form>

                        <!-- Login Link -->
                        <div class="mt-6 text-center">
                            <p class="text-gray-600">
                                Already have an account?
                                <a href="<?= BASE_URL ?>/auth/login.php" class="text-purple-600 hover:text-purple-700 font-bold hover:underline transition">Login here</a>
                            </p>
                        </div>

                        <!-- Terms -->
                        <div class="mt-6 text-center text-xs text-gray-500">
                            By creating an account, you agree to our
                            <a href="#" class="text-purple-600 hover:underline">Terms of Service</a>
                            and
                            <a href="#" class="text-purple-600 hover:underline">Privacy Policy</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-8 mt-12">
        <div class="container mx-auto px-4 text-center">
            <div class="flex items-center justify-center gap-2 text-xl font-bold text-white mb-4">
                <svg class="w-6 h-6 text-purple-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z" />
                </svg>
                <span><?= SITE_NAME ?></span>
            </div>
            <p class="text-sm mb-4">&copy; <?= PROJECT_YEAR ?> <?= SITE_NAME ?>. All rights reserved. A Project by <?= PROJECT_AUTHORS ?></p>
            <div class="text-xs opacity-75">
                <p><?= PROJECT_INSTITUTION ?></p>
                <p class="mt-1">Bachelor of Computer Application - Project I Proposal</p>
                <p class="mt-1">Supervised by: <?= PROJECT_SUPERVISOR ?></p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Toggle Password Visibility
        function setupPasswordToggle(buttonId, inputId, iconId) {
            const button = document.getElementById(buttonId);
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (button && input && icon) {
                button.addEventListener("click", () => {
                    const type = input.getAttribute("type") === "password" ? "text" : "password";
                    input.setAttribute("type", type);
                    
                    if (type === "password") {
                        icon.innerHTML = `
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        `;
                    } else {
                        icon.innerHTML = `
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        `;
                    }
                });
            }
        }

        setupPasswordToggle("togglePassword", "password", "eyeIconPassword");
        setupPasswordToggle("toggleConfirmPassword", "confirm_password", "eyeIconConfirm");

        // Clear error styling on input
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('input', () => {
                input.classList.remove('input-error');
            });
        });

        // Auto-hide notifications
        setTimeout(() => {
            const notifications = document.querySelectorAll('.notification');
            notifications.forEach(notification => {
                notification.style.transition = 'opacity 0.3s ease';
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            });
        }, 5000);

        // Page Load Animation
        window.addEventListener("load", () => {
            document.body.style.opacity = "0";
            setTimeout(() => {
                document.body.style.transition = "opacity 0.5s ease";
                document.body.style.opacity = "1";
            }, 100);
        });

        // Console message
        console.log("%c🛒 <?= SITE_NAME ?> Registration", "color: #667eea; font-size: 20px; font-weight: bold;");
        console.log("%cDeveloped by: <?= PROJECT_AUTHORS ?>", "color: #764ba2; font-size: 14px;");
        console.log("%c<?= PROJECT_INSTITUTION ?>", "color: #666; font-size: 12px;");
    </script>
</body>
</html>