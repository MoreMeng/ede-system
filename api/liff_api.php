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

    // --- 3. ดึงสถานะ (แบบเจาะจงตาม Workflow ID) ---
    else if ($action === 'get_statuses') {
        // รับค่า workflow_id ที่ส่งมาจาก JS
        $target_id = $_GET['workflow_id'] ?? ''; 
        
        $jsonFile = '../data/workflow_data.json';
        $statuses = [];

        if (file_exists($jsonFile)) {
            $workflows = json_decode(file_get_contents($jsonFile), true) ?? [];
            
            foreach ($workflows as $wf) {
                // เปรียบเทียบ ID ให้ตรงกันเป๊ะๆ (เช่น cat_default หรือ cat_xxxx)
                // ถ้าตรงกัน ให้ดึงเฉพาะอันนี้ แล้วสั่ง break เพื่อหยุดหาทันที
                if ($wf['id'] === $target_id) {
                    foreach ($wf['statuses'] as $st) {
                        $statuses[] = [
                            'status_name' => $st['name'],
                            'color'       => $st['color'],
                            'category'    => $wf['name']
                        ];
                    }
                    break; // เจอแล้วหยุดเลย ไม่เอาอันอื่นมาปน
                }
            }
        }

        // กรณีไม่เจอ (เช่น เอกสารเก่า หรือค่าว่าง) ให้ใช้ Default (General)
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