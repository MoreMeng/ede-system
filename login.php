<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบ / สมัครสมาชิก - EDE System</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; background: linear-gradient(135deg, #29B6F6 0%, #7E57C2 100%); height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); width: 100%; max-width: 450px; position: relative; overflow: hidden; }
        .form-control { border-radius: 50px; padding: 12px 20px; background: #f0f2f5; border: none; margin-bottom: 15px; }
        .btn-action { border-radius: 50px; padding: 12px; font-weight: bold; width: 100%; color: white; transition: 0.3s; border: none; }
        .btn-login { background: #7E57C2; }
        .btn-login:hover { background: #5E35B1; transform: translateY(-2px); }
        .btn-register { background: #29B6F6; }
        .btn-register:hover { background: #039BE5; transform: translateY(-2px); }
        .toggle-link { cursor: pointer; color: #7E57C2; font-weight: bold; text-decoration: none; }
        .toggle-link:hover { text-decoration: underline; }

        /* Animation สำหรับสลับฟอร์ม */
        .form-section { transition: all 0.3s ease-in-out; }
        .hidden { display: none; }
    </style>
</head>
<body>

    <div class="login-card text-center">

        <!-- ส่วน Logo -->
        <div class="mb-4">
            <i class="fas fa-file-signature fa-4x text-primary mb-3"></i>
            <h4 class="fw-bold text-secondary">EDE System</h4>
            <p class="text-muted small">ระบบทะเบียนเอกสารอิเล็กทรอนิกส์</p>
        </div>

        <!-- 1. ฟอร์มเข้าสู่ระบบ (Login) -->
        <div id="loginForm" class="form-section">
            <h5 class="mb-3 text-secondary">เข้าสู่ระบบ</h5>
            <form action="api/auth.php" method="POST">
                <input type="text" name="username" class="form-control" placeholder="ชื่อผู้ใช้งาน" required>
                <input type="password" name="password" class="form-control" placeholder="รหัสผ่าน" required>

                <button type="submit" class="btn btn-action btn-login shadow-sm mb-3">
                    เข้าสู่ระบบ <i class="fas fa-sign-in-alt ms-2"></i>
                </button>
            </form>
            <div class="text-muted small">
                ยังไม่มีบัญชี? <span onclick="toggleForm()" class="toggle-link">สมัครสมาชิก</span>
            </div>
        </div>

        <!-- 2. ฟอร์มสมัครสมาชิก (Register) - ซ่อนอยู่ -->
        <div id="registerForm" class="form-section hidden">
            <h5 class="mb-3 text-info">สมัครสมาชิกใหม่</h5>
            <form action="api/public_register.php" method="POST">
                <input type="text" name="fullname" class="form-control" placeholder="ชื่อ-นามสกุล" required>
                <input type="text" name="department" class="form-control" placeholder="แผนก/หน่วยงาน" required>
                <input type="text" name="username" class="form-control" placeholder="กำหนดชื่อผู้ใช้งาน (Username)" required>
                <input type="password" name="password" class="form-control" placeholder="กำหนดรหัสผ่าน" required>
                <input type="password" name="confirm_password" class="form-control" placeholder="ยืนยันรหัสผ่าน" required>

                <button type="submit" class="btn btn-action btn-register shadow-sm mb-3">
                    ลงทะเบียน <i class="fas fa-user-plus ms-2"></i>
                </button>
            </form>
            <div class="text-muted small">
                มีบัญชีอยู่แล้ว? <span onclick="toggleForm()" class="toggle-link" style="color: #29B6F6;">เข้าสู่ระบบ</span>
            </div>
        </div>

        <div class="mt-4 pt-3 border-top">
            <small class="text-muted">© 2025 EDE System</small>
        </div>
    </div>

    <script>
        // ฟังก์ชันสลับฟอร์ม
        function toggleForm() {
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');

            if (loginForm.classList.contains('hidden')) {
                // แสดง Login
                loginForm.classList.remove('hidden');
                registerForm.classList.add('hidden');
            } else {
                // แสดง Register
                loginForm.classList.add('hidden');
                registerForm.classList.remove('hidden');
            }
        }
    </script>

</body>
</html>