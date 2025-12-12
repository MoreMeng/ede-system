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

        // ** จุดที่ปรับปรุง: รับค่าสถานะเริ่มต้นจาก Workflow **
        // ถ้าไม่มีค่าส่งมา (กรณีไม่ได้เลือก) ให้ใช้ค่า Default 'ลงทะเบียนใหม่'
        $initial_status = !empty($_POST['current_status']) ? $_POST['current_status'] : 'ลงทะเบียนใหม่';

        // ------------------------------------------------------------------
        // 2. สร้างรหัสเอกสาร (System Code) แบบไม่ซ้ำแน่นอน (Unique)
        // ------------------------------------------------------------------
        $uuid_part = substr(uniqid(), -5);
        $document_code = "EDE-" . date("Ymd") . "-" . strtoupper($uuid_part) . rand(10,99);

        // 3. บันทึกลงฐานข้อมูล
        // เปลี่ยนจาก 'ลงทะเบียนใหม่' เป็นตัวแปร $initial_status
        $sql = "INSERT INTO documents (document_code, title, type_id, reference_no, sender_name, receiver_name, created_by, current_status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $document_code,
            $title,
            $type_id,
            $reference_no,
            $sender_name,
            $receiver_name,
            $created_by,
            $initial_status // <--- ใช้ค่านี้
        ]);

        $document_id = $pdo->lastInsertId();

        // 4. สร้าง Log แรก
        // บันทึกสถานะเริ่มต้นลงใน Log ด้วย
        $stmtLog = $pdo->prepare("INSERT INTO document_status_log (document_id, status, action_by) VALUES (?, ?, ?)");
        $stmtLog->execute([$document_id, $initial_status, $created_by]);

        // 5. ส่งไปหน้าพิมพ์
        header("Location: ../../print/" . $document_code);
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