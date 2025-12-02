<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $department = trim($_POST['department']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. ตรวจสอบรหัสผ่านตรงกันไหม
    if ($password !== $confirm_password) {
        echo "<script>alert('❌ รหัสผ่านไม่ตรงกัน'); window.history.back();</script>";
        exit;
    }

    try {
        if (isset($pdo)) {
            // 2. ตรวจสอบว่า Username ซ้ำไหม
            $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $check->execute([$username]);
            if ($check->fetchColumn() > 0) {
                echo "<script>alert('❌ Username นี้มีผู้ใช้งานแล้ว'); window.history.back();</script>";
                exit;
            }

            // 3. หา role_id ของ 'User' (ปกติมักจะเป็น ID 2 หรือ 3 แล้วแต่ตอนสร้าง)
            // ค้นหา ID ของ role ชื่อ 'User' หรือถ้าไม่มีให้ใช้ ID 2 เป็นค่าเริ่มต้น
            $stmtRole = $pdo->prepare("SELECT role_id FROM roles WHERE role_name LIKE '%User%' LIMIT 1");
            $stmtRole->execute();
            $role = $stmtRole->fetch(PDO::FETCH_ASSOC);
            $default_role_id = $role ? $role['role_id'] : 2; 

            // 4. บันทึกข้อมูล
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (username, password_hash, fullname, department, role_id) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $password_hash, $fullname, $department, $default_role_id]);

            // สมัครเสร็จแล้ว ส่งกลับไปหน้า Login พร้อมแจ้งเตือน
            echo "<script>
                alert('✅ สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ'); 
                window.location.href = '../login.php';
            </script>";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    header("Location: ../login.php");
}
?>