<?php
session_start();
require_once '../config/db.php';

// ฟังก์ชันสร้าง UUID (v4)
function gen_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0x0fff ) | 0x4000,
        mt_rand( 0, 0x3fff ) | 0x8000,
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 1. รับค่าจากฟอร์ม
        $title = $_POST['title'];
        $type_id = $_POST['type_id'];
        $reference_no = $_POST['reference_no']; // เลขที่หนังสือ (พิมพ์เอง)
        $sender_name = $_POST['sender_name'];
        $receiver_name = $_POST['receiver_name'];
        $created_by = $_POST['created_by'];

        // 2. สร้างรหัสเอกสาร (System ID / UUID) ที่หลังบ้าน
        // ถ้าอยากได้รหัสสั้นๆ ให้ใช้: "EDE-" . date("ymd") . "-" . rand(1000, 9999);
        // ถ้าอยากได้ UUID ให้ใช้: gen_uuid();
        $document_code = "EDE-" . date("Ymd") . "-" . rand(1000,9999); 

        // 3. บันทึกลงฐานข้อมูล
        $sql = "INSERT INTO documents (document_code, title, type_id, reference_no, sender_name, receiver_name, created_by, current_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Registered')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $document_code,
            $title,
            $type_id,
            $reference_no,
            $sender_name,
            $receiver_name,
            $created_by
        ]);
        
        $document_id = $pdo->lastInsertId();

        // 4. สร้าง Log แรก (Registered)
        $stmtLog = $pdo->prepare("INSERT INTO document_status_log (document_id, status, action_by) VALUES (?, 'Registered', ?)");
        $stmtLog->execute([$document_id, $created_by]);

        // 5. ส่งไปหน้าพิมพ์ใบปะหน้า (ส่ง document_code ไปสร้าง QR)
        header("Location: ../print_cover.php?code=" . $document_code);
        exit;

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
        echo "<br><a href='../register.php'>กลับไปหน้าลงทะเบียน</a>";
    }
} else {
    header("Location: ../register.php");
}
?>