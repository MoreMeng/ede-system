<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$jsonFile = '../data/workflow_data.json';

// สร้างไฟล์ถ้ายังไม่มี
if (!file_exists($jsonFile)) {
    if (!is_dir('../data')) mkdir('../data', 0777, true);
    file_put_contents($jsonFile, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function getJson() {
    global $jsonFile;
    return json_decode(file_get_contents($jsonFile), true) ?? [];
}

function saveJson($data) {
    global $jsonFile;
    return file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// ข้อมูลพื้นฐานที่ทุกคนต้องมี
function getDefaultData() {
    return [
        'id' => 'cat_default', // ID พิเศษ
        'name' => 'สถานะพื้นฐาน (General)',
        'created_by' => 'system',
        'statuses' => [
            ['id' => 'st_def_1', 'name' => 'ลงทะเบียนเอกสารใหม่', 'color' => '#6c757d'],
            ['id' => 'st_def_2', 'name' => 'รับเอกสาร', 'color' => '#ffc107'],
            ['id' => 'st_def_3', 'name' => 'ส่งต่อ', 'color' => '#0dcaf0o'],
            ['id' => 'st_def_4', 'name' => 'ได้รับแล้ว', 'color' => '#198754']
        ]
    ];
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$currentUser = $_GET['user_id'] ?? $_SESSION['user_id'] ?? 0;

try {
    switch ($action) {
        case 'list':
            $data = getJson();
            
            // 1. ตรวจสอบว่ามีหมวดพื้นฐานหรือยัง?
            $hasDefault = false;
            foreach ($data as $cat) {
                if (isset($cat['id']) && $cat['id'] === 'cat_default') {
                    $hasDefault = true;
                    break;
                }
            }

            // 2. ถ้ายังไม่มี ให้แทรกเข้าไปเป็น "อันแรก" (array_unshift)
            if (!$hasDefault) {
                $defaultCategory = getDefaultData();
                array_unshift($data, $defaultCategory); // ใส่ไว้หน้าสุด
                saveJson($data); // บันทึกลงไฟล์ทันที เพื่อให้ครั้งหน้าไม่ต้องสร้างใหม่
            }
            
            // 3. กรองข้อมูลตาม User (แต่ต้องปล่อยให้เห็นหมวดพื้นฐานเสมอ)
            if ($currentUser) {
                $data = array_values(array_filter($data, function($item) use ($currentUser) {
                    // แสดงถ้าเป็นของ User คนนั้น หรือ เป็นของ System (cat_default)
                    $isOwner = isset($item['created_by']) && $item['created_by'] == $currentUser;
                    $isSystem = isset($item['id']) && $item['id'] === 'cat_default';
                    
                    return $isOwner || $isSystem;
                }));
            }

            echo json_encode(['success' => true, 'data' => $data]);
            break;

        case 'add_category':
            $name = $_POST['category_name'] ?? '';
            if ($name) {
                $data = getJson();
                $newCat = [
                    'id' => 'cat_' . uniqid(),
                    'name' => $name,
                    'created_by' => $_SESSION['user_id'] ?? 0,
                    'statuses' => []
                ];
                $data[] = $newCat; // ต่อท้าย
                saveJson($data);
                echo json_encode(['success' => true]);
            }
            break;

        case 'edit_category':
            $id = $_POST['id'] ?? '';
            $name = $_POST['category_name'] ?? '';
            
            // ห้ามแก้ชื่อหมวดพื้นฐาน
            if ($id === 'cat_default') {
                echo json_encode(['success' => false, 'message' => 'ไม่สามารถแก้ไขชื่อหมวดหมู่พื้นฐานได้']);
                exit;
            }

            if ($id && $name) {
                $data = getJson();
                foreach ($data as &$cat) {
                    if ($cat['id'] === $id) {
                        $cat['name'] = $name;
                        break;
                    }
                }
                saveJson($data);
                echo json_encode(['success' => true]);
            }
            break;

        case 'delete_category':
            $id = $_POST['id'] ?? '';
            
            // ห้ามลบหมวดพื้นฐาน
            if ($id === 'cat_default') {
                echo json_encode(['success' => false, 'message' => 'ไม่สามารถลบหมวดหมู่พื้นฐานของระบบได้']);
                exit;
            }

            if ($id) {
                $data = getJson();
                $data = array_values(array_filter($data, fn($c) => $c['id'] !== $id));
                saveJson($data);
                echo json_encode(['success' => true]);
            }
            break;

        case 'add_status':
            $catId = $_POST['category_id'] ?? '';
            $name = $_POST['status_name'] ?? '';
            $color = $_POST['color_class'] ?? 'secondary';
            if ($catId && $name) {
                $data = getJson();
                foreach ($data as &$cat) {
                    if ($cat['id'] === $catId) {
                        $cat['statuses'][] = ['id' => 'st_' . uniqid(), 'name' => $name, 'color' => $color];
                        break;
                    }
                }
                saveJson($data);
                echo json_encode(['success' => true]);
            }
            break;

        case 'edit_status':
            $catId = $_POST['category_id'] ?? '';
            $stId = $_POST['status_id'] ?? '';
            $name = $_POST['status_name'] ?? '';
            $color = $_POST['color_class'] ?? 'secondary';

            // ถ้าต้องการห้ามแก้สถานะพื้นฐาน ให้เปิด comment นี้
            /*
            if (strpos($stId, 'st_def_') === 0) {
                 echo json_encode(['success' => false, 'message' => 'ไม่สามารถแก้ไขสถานะพื้นฐานได้']);
                 exit;
            }
            */

            if ($catId && $stId) {
                $data = getJson();
                foreach ($data as &$cat) {
                    if ($cat['id'] === $catId) {
                        foreach ($cat['statuses'] as &$status) {
                            if ($status['id'] === $stId) {
                                $status['name'] = $name;
                                $status['color'] = $color;
                                break 2;
                            }
                        }
                    }
                }
                saveJson($data);
                echo json_encode(['success' => true]);
            }
            break;

        case 'delete_status':
            $catId = $_POST['category_id'] ?? '';
            $statusId = $_POST['status_id'] ?? '';

            // ห้ามลบสถานะพื้นฐาน
            if (strpos($statusId, 'st_def_') === 0) {
                echo json_encode(['success' => false, 'message' => 'ไม่สามารถลบสถานะพื้นฐานของระบบได้']);
                exit;
            }

            if ($catId && $statusId) {
                $data = getJson();
                foreach ($data as &$cat) {
                    if ($cat['id'] === $catId) {
                        $cat['statuses'] = array_values(array_filter($cat['statuses'], fn($s) => $s['id'] !== $statusId));
                        break;
                    }
                }
                saveJson($data);
                echo json_encode(['success' => true]);
            }
            break;

        case 'reorder_status':
            $catId = $_POST['category_id'] ?? '';
            $sortedIds = json_decode($_POST['sorted_ids'] ?? '[]', true);
            if ($catId && !empty($sortedIds)) {
                $data = getJson();
                foreach ($data as &$cat) {
                    if ($cat['id'] === $catId) {
                        $statusMap = [];
                        foreach ($cat['statuses'] as $st) $statusMap[$st['id']] = $st;
                        $newStatuses = [];
                        foreach ($sortedIds as $stId) {
                            if (isset($statusMap[$stId])) {
                                $newStatuses[] = $statusMap[$stId];
                                unset($statusMap[$stId]);
                            }
                        }
                        foreach ($statusMap as $remaining) $newStatuses[] = $remaining;
                        $cat['statuses'] = $newStatuses;
                        break;
                    }
                }
                saveJson($data);
                echo json_encode(['success' => true]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>