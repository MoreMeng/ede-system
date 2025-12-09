<?php
require_once '../config/db.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    if (!isset($pdo)) throw new Exception("Database connection failed");

    // --- 1. ค้นหาเอกสาร ---
    if ($action === 'search') {
        $keyword = $_GET['keyword'] ?? '';
        if (empty($keyword)) throw new Exception("ระบุคำค้นหา");

        $sql = "SELECT d.*, dt.type_name 
                FROM documents d
                LEFT JOIN document_type dt ON d.type_id = dt.type_id
                WHERE d.document_code LIKE ? OR d.title LIKE ?
                ORDER BY d.created_at DESC LIMIT 10";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["%$keyword%", "%$keyword%"]);
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    // --- 2. ประวัติการสแกน ---
    else if ($action === 'history') {
        $line_id = $_GET['line_id'] ?? '';
        if (empty($line_id)) throw new Exception("No Line ID");

        $sql = "SELECT l.*, d.title, d.document_code 
                FROM document_status_log l
                JOIN documents d ON l.document_id = d.document_id
                WHERE l.line_user_id_action = ?
                ORDER BY l.action_time DESC LIMIT 20";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$line_id]);
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    // --- 3. ดึงสถานะ (ปรับปรุงใหม่: อ่านจาก JSON) ---
    else if ($action === 'get_statuses') {
        $creator_id = $_GET['creator_id'] ?? 0;
        $jsonFile = '../data/workflow_data.json';
        $statuses = [];

        if (file_exists($jsonFile)) {
            $workflows = json_decode(file_get_contents($jsonFile), true) ?? [];
            
            // วนลูปหาหมวดหมู่ที่ user คนนี้สร้าง (หรือหมวดกลางถ้ามี)
            foreach ($workflows as $wf) {
                // กรองเฉพาะ Workflow ของ Creator คนนี้ (หรือของคนที่ ID=0/Null ถ้าเป็นระบบกลาง)
                // หมายเหตุ: ต้องแก้ตรงนี้ให้ยืดหยุ่น ถ้า workflow ไม่ระบุ created_by ให้ถือว่าเป็นของทุกคน
                $wfCreator = $wf['created_by'] ?? 0;
                
                if ($wfCreator == $creator_id || $wfCreator == 0) {
                    foreach ($wf['statuses'] as $st) {
                        $statuses[] = [
                            'status_name' => $st['name'],
                            'color' => $st['color'],
                            'category' => $wf['name'] // เพิ่มชื่อหมวดหมู่ไปด้วย เพื่อทำ Group
                        ];
                    }
                }
            }
        }

        // ถ้าไม่มีข้อมูลเลย ให้ใส่ Default
        if (empty($statuses)) {
            $statuses = [
                ['status_name' => 'Received', 'category' => 'ค่าเริ่มต้น'],
                ['status_name' => 'Sent', 'category' => 'ค่าเริ่มต้น'],
                ['status_name' => 'Done', 'category' => 'ค่าเริ่มต้น']
            ];
        }

        echo json_encode(['status' => 'success', 'data' => $statuses]);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>