<?php
require_once "C:/xampp/htdocs/project/config/config.php";

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request';
    }
    
    // Sanitize inputs
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validation
    if (empty($email) || !validate_email($email)) {
        $errors[] = 'email_invalid';
    }
    
    if (empty($password)) {
        $errors[] = 'password_empty';
    }
    
    // Check user credentials
    if (empty($errors)) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $user = db_fetch_one($sql, "s", [$email]);
        
        if (!$user) {
            $errors[] = 'email_not_found';
        } elseif (!verify_password($password, $user['password'])) {
            $errors[] = 'password_incorrect';
        } elseif ($user['is_verified'] == 0) {
            $errors[] = 'email_not_verified';
        } else {
            // Login successful
            login_user($user);
            
            // Set remember me cookie if checked
            if ($remember) {
                $token = generate_token();
                setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 days
            }
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                redirect(BASE_URL . '/admin/dashboard.php');
            } elseif ($user['role'] === 'seller') {
                redirect(BASE_URL . '/seller/dashboard.php');
            } else {
                redirect(BASE_URL . '/project.php');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Sajilo Marketplace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
      @keyframes fadeInUp {
        from {
          opacity: 0;
          transform: translateY(30px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }
      @keyframes slideInRight {
        from {
          opacity: 0;
          transform: translateX(100px);
        }
        to {
          opacity: 1;
          transform: translateX(0);
        }
      }
      @keyframes float {
        0%,
        100% {
          transform: translateY(0px);
        }
        50% {
          transform: translateY(-20px);
        }
      }
      @keyframes pulse {
        0%,
        100% {
          transform: scale(1);
        }
        50% {
          transform: scale(1.05);
        }
      }
      @keyframes shine {
        0% {
          transform: translateX(-100%) translateY(-100%) rotate(45deg);
        }
        100% {
          transform: translateX(100%) translateY(100%) rotate(45deg);
        }
      }
      @keyframes bounceIn {
        0% {
          transform: scale(0.3);
          opacity: 0;
        }
        50% {
          transform: scale(1.1);
        }
        70% {
          transform: scale(0.9);
        }
        100% {
          transform: scale(1);
          opacity: 1;
        }
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
      .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        animation: fadeIn 0.3s ease;
      }
      .loading-overlay.active {
        display: flex;
      }
      .loading-content {
        text-align: center;
        color: white;
      }
      .loading-icon {
        font-size: 120px;
        animation: bounceIn 0.6s ease;
        display: inline-block;
        margin-bottom: 20px;
      }
      .loading-text {
        font-size: 32px;
        font-weight: bold;
        animation: fadeInUp 0.8s ease;
        margin-bottom: 10px;
      }
      .loading-subtext {
        font-size: 18px;
        opacity: 0.9;
        animation: fadeInUp 1s ease;
      }
      .error-overlay {
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
      .error-overlay.active {
        display: flex;
      }
      .error-content {
        text-align: center;
        color: white;
        animation: shake 0.5s ease;
      }
      .error-icon {
        font-size: 120px;
        animation: bounceIn 0.6s ease;
        display: inline-block;
        margin-bottom: 20px;
      }
      .login-container {
        animation: fadeInUp 0.8s ease-out;
      }
      .side-illustration {
        animation: slideInRight 1s ease-out;
      }
      .floating {
        animation: float 3s ease-in-out infinite;
      }
      .gradient-text {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
      }
      .input-group {
        position: relative;
      }
      .input-group input:focus + label,
      .input-group input:not(:placeholder-shown) + label {
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
      .shine-effect {
        position: relative;
        overflow: hidden;
      }
      .shine-effect::after {
        content: "";
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(
          to right,
          rgba(255, 255, 255, 0) 0%,
          rgba(255, 255, 255, 0.3) 50%,
          rgba(255, 255, 255, 0) 100%
        );
        transform: rotate(45deg);
        animation: shine 3s infinite;
      }
    </style>
  </head>
  <body class="bg-gray-50">
    <!-- Success Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay <?= !empty($errors) ? '' : (isset($_POST['email']) ? 'active' : '') ?>">
      <div class="loading-content">
        <div class="loading-icon" id="loadingIcon">🛒</div>
        <div class="loading-text" id="loadingText">Verifying credentials...</div>
        <div class="loading-subtext" id="loadingSubtext">Please wait while we log you in</div>
      </div>
    </div>

    <!-- Error Overlay -->
    <div id="errorOverlay" class="error-overlay <?= !empty($errors) ? 'active' : '' ?>">
      <div class="error-content">
        <div class="error-icon" id="errorIcon">
          <?php 
            if (in_array('email_not_found', $errors)) {
              echo '❌';
            } elseif (in_array('password_incorrect', $errors)) {
              echo '🔒';
            } elseif (in_array('email_not_verified', $errors)) {
              echo '📧';
            } else {
              echo '⚠️';
            }
          ?>
        </div>
        <div class="loading-text" id="errorText">
          <?php 
            if (in_array('email_not_found', $errors)) {
              echo 'Email Not Found!';
            } elseif (in_array('password_incorrect', $errors)) {
              echo 'Wrong Password!';
            } elseif (in_array('email_not_verified', $errors)) {
              echo 'Email Not Verified!';
            } else {
              echo 'Login Failed!';
            }
          ?>
        </div>
        <div class="loading-subtext" id="errorSubtext">
          <?php 
            if (in_array('email_not_found', $errors)) {
              echo 'The email address you entered doesn\'t exist in our system';
            } elseif (in_array('password_incorrect', $errors)) {
              echo 'The password you entered is incorrect. Please try again';
            } elseif (in_array('email_not_verified', $errors)) {
              echo 'Please verify your email address before logging in';
            } else {
              echo 'Please check your credentials and try again';
            }
          ?>
        </div>
        <button id="closeErrorBtn" class="mt-6 bg-white text-red-600 px-8 py-3 rounded-full font-bold hover:bg-gray-100 transition">
          Try Again
        </button>
      </div>
    </div>

    <!-- Notification Container -->
    <div id="notificationContainer"></div>

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
            <a href="#" class="hover:underline hidden md:inline">Become a Seller</a>
          </div>
        </div>
      </div>

      <!-- Main Navigation -->
      <nav class="container mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
          <!-- Logo -->
          <a href="<?= BASE_URL ?>/project.php" class="flex items-center gap-2 text-2xl font-bold text-purple-600">
            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
              <path
                d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"
              />
            </svg>
            <span><?= SITE_NAME ?></span>
          </a>

          <!-- Back to Home -->
          <a
            href="<?= BASE_URL ?>/project.php"
            class="text-purple-600 hover:text-purple-700 font-semibold flex items-center gap-2 transition"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M10 19l-7-7m0 0l7-7m-7 7h18"
              />
            </svg>
            <span class="hidden sm:inline">Back to Home</span>
          </a>
        </div>
      </nav>
    </header>

    <!-- Spacer for fixed header -->
    <div class="h-32"></div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8 md:py-12">
      <div class="flex items-center justify-center min-h-[calc(100vh-200px)]">
        <div class="grid md:grid-cols-2 gap-8 lg:gap-12 max-w-6xl w-full items-center">
          <!-- Left Side - Illustration -->
          <div class="side-illustration hidden md:flex items-center justify-center">
            <div class="text-center">
              <div class="text-[200px] leading-none mb-8 floating inline-block">🛍️</div>
              <h2 class="text-5xl font-bold gradient-text mb-6">
                Welcome Back to <?= SITE_NAME ?>!
              </h2>
              <p class="text-gray-600 text-xl max-w-md mx-auto">
                <?= SITE_DESCRIPTION ?>
              </p>
            </div>
          </div>

          <!-- Right Side - Login Form -->
          <div class="login-container">
            <div class="bg-white rounded-2xl shadow-2xl p-6 md:p-8 lg:p-10">
              <div class="text-center mb-8">
                <h1 class="text-3xl lg:text-4xl font-bold gradient-text mb-3">
                  Login to Your Account
                </h1>
                <p class="text-gray-600">
                  Enter your credentials to access your account
                </p>
              </div>

              <form method="POST" action="" id="loginForm" class="space-y-6">
                <?= csrf_field() ?>
                
                <!-- Email Input -->
                <div class="input-group">
                  <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder=" "
                    class="w-full px-4 py-4 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 transition <?= in_array('email_not_found', $errors) || in_array('email_invalid', $errors) ? 'input-error' : '' ?>"
                    value="<?= clean($_POST['email'] ?? '') ?>"
                    required
                  />
                  <label for="email">Email Address</label>
                </div>

                <!-- Password Input -->
                <div class="input-group">
                  <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder=" "
                    class="w-full px-4 py-4 pr-12 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 transition <?= in_array('password_incorrect', $errors) || in_array('password_empty', $errors) ? 'input-error' : '' ?>"
                    required
                  />
                  <label for="password">Password</label>
                  <button
                    type="button"
                    id="togglePassword"
                    class="absolute right-4 top-4 text-gray-500 hover:text-purple-600 transition"
                  >
                    <svg
                      id="eyeIcon"
                      class="w-6 h-6"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                      />
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                      />
                    </svg>
                  </button>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between text-sm">
                  <label class="flex items-center cursor-pointer group">
                    <input
                      type="checkbox"
                      id="remember"
                      name="remember"
                      class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500 cursor-pointer"
                    />
                    <span class="ml-2 text-gray-700 group-hover:text-purple-600 transition">Remember me</span>
                  </label>
                  <a
                    href="<?= BASE_URL ?>/auth/forgot-password.php"
                    class="text-purple-600 hover:text-purple-700 font-semibold hover:underline transition"
                    >Forgot Password?</a
                  >
                </div>

                <!-- Login Button -->
                <button
                  type="submit"
                  id="loginBtn"
                  class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-4 rounded-xl font-bold text-lg hover:from-purple-700 hover:to-indigo-700 transition transform hover:scale-[1.02] shadow-lg shine-effect"
                >
                  Login
                </button>

                <!-- Divider -->
                <div class="relative my-8">
                  <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                  </div>
                  <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-white text-gray-500 font-medium">Or continue with</span>
                  </div>
                </div>

                <!-- Social Login -->
                <div class="grid grid-cols-2 gap-4">
                  <button
                    type="button"
                    class="social-login flex items-center justify-center gap-2 px-4 py-3 border-2 border-gray-300 rounded-xl hover:bg-gray-50 hover:border-purple-300 transition transform hover:scale-[1.02]"
                  >
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                      <path
                        fill="#4285F4"
                        d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                      />
                      <path
                        fill="#34A853"
                        d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                      />
                      <path
                        fill="#FBBC05"
                        d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                      />
                      <path
                        fill="#EA4335"
                        d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                      />
                    </svg>
                    <span class="font-semibold text-gray-700">Google</span>
                  </button>
                  <button
                    type="button"
                    class="social-login flex items-center justify-center gap-2 px-4 py-3 border-2 border-gray-300 rounded-xl hover:bg-gray-50 hover:border-purple-300 transition transform hover:scale-[1.02]"
                  >
                    <svg class="w-5 h-5" fill="#1877F2" viewBox="0 0 24 24">
                      <path
                        d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"
                      />
                    </svg>
                    <span class="font-semibold text-gray-700">Facebook</span>
                  </button>
                </div>
              </form>

              <!-- Sign Up Link -->
              <div class="mt-8 text-center">
                <p class="text-gray-600">
                  Don't have an account?
                  <a
                    href="<?= BASE_URL ?>/auth/register.php"
                    class="text-purple-600 hover:text-purple-700 font-bold hover:underline transition"
                    >Sign up now</a
                  >
                </p>
              </div>

              <!-- Terms -->
              <div class="mt-6 text-center text-xs text-gray-500">
                By logging in, you agree to our
                <a href="#" class="text-purple-600 hover:underline">Terms of Service</a>
                and
                <a href="#" class="text-purple-600 hover:underline">Privacy Policy</a>
              </div>
            </div>

            <!-- Mobile Trust Badges -->
            <div class="md:hidden mt-6 grid grid-cols-3 gap-3 text-center">
              <div class="bg-white rounded-xl p-4 shadow-md">
                <div class="text-3xl mb-2">🔒</div>
                <p class="text-xs font-semibold text-gray-700">Verified Sellers</p>
              </div>
              <div class="bg-white rounded-xl p-4 shadow-md">
                <div class="text-3xl mb-2">💳</div>
                <p class="text-xs font-semibold text-gray-700">Secure Payments</p>
              </div>
              <div class="bg-white rounded-xl p-4 shadow-md">
                <div class="text-3xl mb-2">⚡</div>
                <p class="text-xs font-semibold text-gray-700">Fast Delivery</p>
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
            <path
              d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"
            />
          </svg>
          <span><?= SITE_NAME ?></span>
        </div>
        <p class="text-sm mb-4">
          &copy; <?= PROJECT_YEAR ?> <?= SITE_NAME ?>. All rights reserved. A Project by <?= PROJECT_AUTHORS ?>
        </p>
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
      const togglePassword = document.getElementById("togglePassword");
      const passwordInput = document.getElementById("password");
      const eyeIcon = document.getElementById("eyeIcon");

      if (togglePassword) {
        togglePassword.addEventListener("click", () => {
          const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
          passwordInput.setAttribute("type", type);

          if (type === "password") {
            eyeIcon.innerHTML = `
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            `;
          } else {
            eyeIcon.innerHTML = `
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
            `;
          }
        });
      }

      // Close error overlay
      const closeErrorBtn = document.getElementById("closeErrorBtn");
      if (closeErrorBtn) {
        closeErrorBtn.addEventListener("click", () => {
          document.getElementById("errorOverlay").classList.remove("active");
        });
      }

      // Click outside to close
      const errorOverlay = document.getElementById("errorOverlay");
      if (errorOverlay) {
        errorOverlay.addEventListener("click", (e) => {
          if (e.target.id === "errorOverlay") {
            errorOverlay.classList.remove("active");
          }
        });
      }

      // Clear error styling on input
      const emailInput = document.getElementById("email");
      const passwordInput2 = document.getElementById("password");

      if (emailInput) {
        emailInput.addEventListener("input", () => {
          emailInput.classList.remove("input-error");
        });
      }

      if (passwordInput2) {
        passwordInput2.addEventListener("input", () => {
          passwordInput2.classList.remove("input-error");
        });
      }

      // Social Login Buttons
      document.querySelectorAll('.social-login').forEach((btn) => {
        btn.addEventListener("click", () => {
          const provider = btn.querySelector('span').textContent.trim();
          const notification = document.createElement("div");
          notification.className = "notification bg-blue-500 text-white px-6 py-4 rounded-xl shadow-lg max-w-md";
          notification.innerHTML = `
            <div class="flex items-center justify-between">
              <span class="font-semibold">${provider} login coming soon!</span>
              <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">✕</button>
            </div>
          `;
          document.getElementById("notificationContainer").appendChild(notification);
          setTimeout(() => notification.remove(), 3000);
        });
      });

      // Page Load Animation
      window.addEventListener("load", () => {
        document.body.style.opacity = "0";
        setTimeout(() => {
          document.body.style.transition = "opacity 0.5s ease";
          document.body.style.opacity = "1";
        }, 100);
      });

      // Console message
      console.log("%c🛒 Sajilo Login Page", "color: #667eea; font-size: 20px; font-weight: bold;");
      console.log("%cDeveloped by: <?= PROJECT_AUTHORS ?>", "color: #764ba2; font-size: 14px;");
      console.log("%c<?= PROJECT_INSTITUTION ?>", "color: #666; font-size: 12px;");
      
      // Auto-hide loading overlay after 2 seconds if still showing
      setTimeout(() => {
    const loadingOverlay = document.getElementById("loadingOverlay");

    if (loadingOverlay && loadingOverlay.classList.contains("active")) {
        loadingOverlay.classList.remove("active");
    }
}, 2000); // 2 seconds delay

    </script>
  </body>
</html>