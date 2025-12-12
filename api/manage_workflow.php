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

$action = $_GET['action'] ?? '';
$currentUser = $_GET['user_id'] ?? $_SESSION['user_id'] ?? 0;

switch ($action) {
    case 'list':
        $data = getJson();
        
        // ถ้ามีการส่ง user_id มา หรือมีใน session ให้กรองเฉพาะของคนนั้น
        if ($currentUser) {
            $data = array_values(array_filter($data, function($item) use ($currentUser) {
                // แสดงเฉพาะที่ created_by ตรงกัน (ถ้าข้อมูลเก่าไม่มี created_by อาจจะไม่แสดง หรือต้องแก้ให้แสดง)
                return isset($item['created_by']) && $item['created_by'] == $currentUser;
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
                'created_by' => $_SESSION['user_id'] ?? 0, // บันทึกว่าใครสร้าง
                'statuses' => []
            ];
            $data[] = $newCat;
            saveJson($data);
            echo json_encode(['success' => true]);
        }
        break;

    case 'delete_category':
        $id = $_POST['id'] ?? '';
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
                    $cat['statuses'][] = [
                        'id' => 'st_' . uniqid(),
                        'name' => $name,
                        'color' => $color
                    ];
                    break;
                }
            }
            saveJson($data);
            echo json_encode(['success' => true]);
        }
        break;

    case 'delete_status':
        $catId = $_POST['category_id'] ?? '';
        $statusId = $_POST['status_id'] ?? '';

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
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>