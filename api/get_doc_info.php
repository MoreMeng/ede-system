<?php
require_once '../config/db.php';
header('Content-Type: application/json');

$doc_code = $_GET['code'] ?? '';
// รับค่า action มาตรวจสอบ (ถ้าไม่มีให้เป็นว่างๆ)
$action = $_GET['action'] ?? '';

if (empty($doc_code)) {
    echo json_encode(['error' => 'No code provided']);
    exit;
}

try {
    // --- จุดที่แก้ไข: เช็คว่า action = scan ถึงจะบวกยอด ---
    if ($action === 'scan') {
        $stmtCount = $pdo->prepare("UPDATE documents SET view_count = view_count + 1 WHERE document_code = ?");
        $stmtCount->execute([$doc_code]);
    }
    // -----------------------------------------------------

    // ดึงข้อมูลเอกสาร
    $stmt = $pdo->prepare("
        SELECT d.*, dt.type_name 
        FROM documents d
        LEFT JOIN document_type dt ON d.type_id = dt.type_id
        WHERE d.document_code = ?
    ");
    $stmt->execute([$doc_code]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doc) {
        echo json_encode(['error' => 'Document not found']);
        exit;
    }

    // ดึงประวัติ (Timeline)
    $stmt_log = $pdo->prepare("
        SELECT l.*, u.fullname, u.username
        FROM document_status_log l
        LEFT JOIN users u ON l.action_by = u.user_id
        WHERE l.document_id = ?
        ORDER BY l.action_time DESC
    ");
    $stmt_log->execute([$doc['document_id']]);
    $logs = $stmt_log->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['doc' => $doc, 'logs' => $logs]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>