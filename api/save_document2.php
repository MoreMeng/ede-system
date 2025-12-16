<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 1. รับค่าจากฟอร์ม
        $title = $_POST['title'];
        $type_id = $_POST['type_id'];
        $reference_no = $_POST['reference_no'];
        $sender_name = $_POST['sender_name'];
        $receiver_name = $_POST['receiver_name'];
        $created_by = $_POST['created_by'];

        // [แก้ไข 1] รับค่า workflow_id (สำคัญมากสำหรับการระบุหมวดหมู่สถานะ)
        // ถ้าไม่มีค่าส่งมา ให้ใช้ 'cat_default' (General)
        $workflow_id = !empty($_POST['workflow_id']) ? $_POST['workflow_id'] : 'cat_default';

        // รับค่าสถานะเริ่มต้นจาก Workflow
        $initial_status = !empty($_POST['current_status']) ? $_POST['current_status'] : 'ลงทะเบียนใหม่';

        // ------------------------------------------------------------------
        // 2. สร้างรหัสเอกสาร (System Code)
        // ------------------------------------------------------------------
        $uuid_part = substr(uniqid(), -5);
        $document_code = "EDE-" . date("Ymd") . "-" . strtoupper($uuid_part) . rand(10,99);

        // 3. บันทึกลงฐานข้อมูล
        // [แก้ไข 2] เพิ่ม workflow_id เข้าไปในคำสั่ง SQL
        $sql = "INSERT INTO documents (document_code, title, type_id, reference_no, sender_name, receiver_name, created_by, current_status, workflow_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $document_code,
            $title,
            $type_id,
            $reference_no,
            $sender_name,
            $receiver_name,
            $created_by,
            $initial_status,
            $workflow_id // [แก้ไข 3] ส่งค่า workflow_id ไปบันทึก
        ]);

        $document_id = $pdo->lastInsertId();

        // 4. สร้าง Log แรก
        $stmtLog = $pdo->prepare("INSERT INTO document_status_log (document_id, status, action_by) VALUES (?, ?, ?)");
        $stmtLog->execute([$document_id, $initial_status, $created_by]);

        // 5. ส่งไปหน้าพิมพ์
        header("Location: /ede-system/print/" . $document_code . "/");
        exit;

    } catch (Exception $e) {
        if ($e->getCode() == 23000) {
             echo "<script>alert('เกิดข้อผิดพลาดในการสร้างรหัส (ซ้ำ) กรุณาลองใหม่'); window.history.back();</script>";
        } else {
             echo "Error: " . $e->getMessage();
        }
    }
} else {
    header("Location: ../register.php");
}
?>