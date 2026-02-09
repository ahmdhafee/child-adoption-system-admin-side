<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal | Family Bridge Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="shortcut icon" href="favlogo.png" type="image/x-icon">
    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            
            --primary: #2C3E50;
            --secondary: #34495E;
            --accent: #3498DB;
            --danger: #E74C3C;
            --warning: #F39C12;
            --success: #27AE60;
            --info: #2980B9;
            --light: #ECF0F1;
            --dark: #1A252F;
            --gray: #7F8C8D;
            --light-gray: #F8F9FA;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
            --border-radius: 8px;
        }

        body {
            line-height: 1.6;
            color: var(--dark);
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        .header {
            padding: 25px 0;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--accent), var(--info));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            box-shadow: var(--shadow);
        }

        .logo-text h1 {
            color: white;
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .logo-text p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            margin-top: 2px;
        }

        .back-to-home {
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 10px 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: var(--border-radius);
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .back-to-home:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: white;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 150px 20px 60px;
            min-height: 100vh;
        }

        /* Login Container */
        .login-container {
            width: 100%;
            max-width: 480px;
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            position: relative;
            animation: fadeIn 0.8s ease-out;
            margin: 0 auto;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(to right, var(--accent), var(--info), var(--success));
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .login-header h2 {
            font-size: 2rem;
            margin-bottom: 10px;
            color: white;
        }

        .login-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
        }

        .login-body {
            padding: 40px;
        }

        /* Role Selection */
        .role-selection {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 30px;
        }

        .role-card {
            background-color: var(--light-gray);
            border: 2px solid var(--light-gray);
            border-radius: var(--border-radius);
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
        }

        .role-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
            border-color: var(--accent);
        }

        .role-card.selected {
            background-color: rgba(52, 152, 219, 0.05);
            border-color: var(--accent);
        }

        .role-card.selected::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background-color: var(--accent);
        }

        .role-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: white;
            font-size: 1.5rem;
        }

        .role-card.admin .role-icon {
            background: linear-gradient(135deg, var(--info), var(--accent));
        }

        .role-card.chief .role-icon {
            background: linear-gradient(135deg, var(--success), #2ECC71);
        }

        .role-card h4 {
            color: var(--dark);
            margin-bottom: 5px;
            font-size: 1.1rem;
        }

        .role-card p {
            color: var(--gray);
            font-size: 0.85rem;
            margin-bottom: 0;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.95rem;
        }

        .input-group {
            position: relative;
        }

        .form-control {
            width: 100%;
            padding: 14px 15px 14px 45px;
            border: 2px solid var(--light-gray);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
            background-color: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: 1.1rem;
        }

        .error-message {
            color: var(--danger);
            font-size: 0.85rem;
            margin-top: 5px;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        /* Security Code Field */
        .security-code-field {
            display: none;
        }

        /* Form Options */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 25px 0 30px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .checkbox-group input {
            margin-right: 8px;
            cursor: pointer;
        }

        .forgot-password {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: var(--transition);
        }

        .forgot-password:hover {
            color: var(--info);
            text-decoration: underline;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 15px 30px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
            text-decoration: none;
            width: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent), var(--info));
            color: white;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }

        /* Alert Messages */
        .alert {
            padding: 16px 20px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease-out;
            display: none;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-10px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .alert.error {
            background-color: rgba(231, 76, 60, 0.1);
            border-left: 4px solid var(--danger);
            color: var(--danger);
        }

        .alert.success {
            background-color: rgba(39, 174, 96, 0.1);
            border-left: 4px solid var(--success);
            color: var(--dark);
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 20px 0;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            background-color: rgba(26, 37, 47, 0.8);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .back-to-home {
                width: 100%;
                justify-content: center;
            }
            
            .login-container {
                max-width: 100%;
                margin: 0 15px;
            }
            
            .login-body {
                padding: 30px 25px;
            }
            
            .role-selection {
                grid-template-columns: 1fr;
            }
        }

        /* Loading Animation */
        .loader {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="index.html" class="logo-container">
                    <div class="logo-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="logo-text">
                        <h1>Family Bridge</h1>
                        <p>Administrative Management System</p>
                    </div>
                </a>
                
                <a href="http://localhost/adoption%20system/" class="back-to-home">
                    <i class="fas fa-arrow-left"></i>
                    Back to Main Site
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="login-container">
            <!-- Login Header -->
            <div class="login-header">
                <div class="login-icon">
                    <i class="fas fa-user-lock"></i>
                </div>
                <h2>Admin Portal</h2>
                <p>Secure access for authorized personnel only</p>
            </div>

            <!-- Login Body -->
            <div class="login-body">
                <!-- Alert Message -->
                <div class="alert" id="loginAlert">
                    <i id="alertIcon"></i>
                    <span id="alertMessage"></span>
                </div>

                <!-- Role Selection -->
                <div class="role-selection">
                    <div class="role-card admin selected" data-role="admin">
                        <div class="role-icon">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        <h4>System Administrator</h4>
                        <p>Full system access and management</p>
                    </div>
                    <div class="role-card chief" data-role="chief">
                        <div class="role-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h4>Chief Officer</h4>
                        <p>Executive oversight and approvals</p>
                    </div>
                </div>

                <!-- Main Login Form -->
                <form id="loginForm">
                    <!-- Username/Email Field -->
                    <div class="form-group">
                        <label for="username">Username or Email</label>
                        <div class="input-group">
                            <div class="input-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <input type="text" id="username" class="form-control" 
                                   placeholder="Enter your username or email">
                        </div>
                        <div class="error-message" id="usernameError">
                            Please enter your username or email
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <div class="input-icon">
                                <i class="fas fa-lock"></i>
                            </div>
                            <input type="password" id="password" class="form-control" 
                                   placeholder="Enter your password">
                        </div>
                        <div class="error-message" id="passwordError">
                            Please enter your password
                        </div>
                    </div>

                    <!-- Security Code Field (Initially hidden) -->
                    <div class="form-group security-code-field" id="securityCodeGroup">
                        <label for="securityCode">Security Code</label>
                        <div class="input-group">
                            <div class="input-icon">
                                <i class="fas fa-key"></i>
                            </div>
                            <input type="password" id="securityCode" class="form-control" 
                                   placeholder="Enter your security code">
                        </div>
                        <div class="error-message" id="securityCodeError">
                            Security code is required for admin access
                        </div>
                    </div>

                    <!-- Form Options -->
                    <div class="form-options">
                        <div class="checkbox-group">
                            <input type="checkbox" id="rememberMe">
                            <label for="rememberMe">Remember me</label>
                        </div>
                        <a href="#" class="forgot-password" id="forgotPassword">
                            Forgot Password?
                        </a>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" class="btn btn-primary" id="loginButton">
                        <i class="fas fa-sign-in-alt"></i>
                        <span id="loginButtonText">Login to Admin Portal</span>
                        <div class="loader" id="loginLoader"></div>
                    </button>
                </form>
            </div>
        </div>
    </main>

   
    <footer class="footer">
        <div class="container">
            <p>&copy; 2023 Family Bridge Admin Portal. All rights reserved.</p>
        </div>
    </footer>

    <script>
        const roleCards = document.querySelectorAll('.role-card');
        const loginForm = document.getElementById('loginForm');
        const securityCodeGroup = document.getElementById('securityCodeGroup');
        const loginButton = document.getElementById('loginButton');
      
        let currentRole = 'admin'; 
      
        roleCards.forEach(card => {
          card.addEventListener('click', function() {
            roleCards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            currentRole = this.dataset.role;
      
           
            securityCodeGroup.style.display = 'none';
      
            document.getElementById('loginButtonText').textContent =
              currentRole === 'admin' ? 'Login as System Admin' : 'Login as Chief Officer';
          });
        });
      
        function showAlert(message, type) {
          const loginAlert = document.getElementById('loginAlert');
          const icon = document.getElementById('alertIcon');
          const messageEl = document.getElementById('alertMessage');
      
          loginAlert.className = `alert ${type}`;
          icon.className = type === 'error' ? 'fas fa-exclamation-circle' : 'fas fa-check-circle';
          messageEl.textContent = message;
          loginAlert.style.display = 'flex';
          setTimeout(() => loginAlert.style.display = 'none', 4000);
        }
      
        loginForm.addEventListener('submit', async (e) => {
          e.preventDefault();
      
          const username = document.getElementById('username').value.trim();
          const password = document.getElementById('password').value;
      
          if (!username || !password) {
            showAlert('Please enter username/email and password', 'error');
            return;
          }
      
          loginButton.disabled = true;
      
          const form = new FormData();
          form.append('step', 'login');
          form.append('role', currentRole);
          form.append('username', username);
          form.append('password', password);
      
          try {
            const res = await fetch('officer_login_handler.php', { method: 'POST', body: form });
            const data = await res.json();
      
            if (!data.success) {
              showAlert(data.message || 'Login failed', 'error');
              loginButton.disabled = false;
              return;
            }
      
            showAlert(data.message || 'Success', 'success');
      
            if (data.redirect) {
              setTimeout(() => window.location.href = data.redirect, 800);
            }
      
          } catch (err) {
            showAlert('Server error. Please try again.', 'error');
            loginButton.disabled = false;
          }
        });
      </script>
</body>
</html>