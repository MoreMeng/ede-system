<?php
session_start();
error_reporting( E_ALL ^ E_NOTICE );
mb_internal_encoding( 'UTF-8' );

// set content return type
header( 'Content-Type: application/json; charset=utf-8' );

// Setting up some server access controls to allow people to get information
header( "Access-Control-Allow-Origin: *" );
header( 'Access-Control-Allow-Methods:  POST, GET' );

require realpath( '../../dv-config.php' );
require DEV_PATH . '/classes/db.class.v2.php';
require DEV_PATH . '/functions/global.php';

// สำหรับ dev parameter (อนุญาตเฉพาะ alphanumeric)
$GET_DEV = sanitizeGetParam( 'dev', 'alphanumeric', '', 50 );

$json_data['data'] = [];

switch ( $GET_DEV ) {

    case 'getdocinfo':
        $doc_code = sanitizeGetParam( 'code', 'alphanumeric', '' );
        $action   = sanitizeGetParam( 'action', 'alphanumeric', '' );
        

        if ( empty( $doc_code ) ) {
            http_response_code( 400 );
            $json_data['status']  = 'error';
            $json_data['message'] = 'ไม่ได้ระบุรหัสเอกสาร';
            break;
        }

        // เช็คว่า action = scan ถึงจะบวกยอด
        if ( $action === 'scan' ) {
            $stmtCount = "UPDATE documents SET view_count = view_count + 1 WHERE document_code = ?";
            CON::updateDB( [$doc_code], $stmtCount );
        }

        // ดึงข้อมูลเอกสาร
        $docSql = "SELECT d.*, dt.type_name
                   FROM documents d
                   LEFT JOIN document_type dt ON d.type_id = dt.type_id
                   WHERE d.document_code = ?";
        $docResult = CON::selectArrayDB( [$doc_code], $docSql );

        if ( !$docResult || empty( $docResult ) ) {
            http_response_code( 404 );
            $json_data['status']  = 'error';
            $json_data['message'] = 'ไม่พบเอกสาร';
            break;
        }

        $doc = $docResult[0];

        // ดึงประวัติ (Timeline)
        $logSql = "SELECT l.*, u.fullname, u.username
                   FROM document_status_log l
                   LEFT JOIN users u ON l.action_by = u.user_id
                   WHERE l.document_id = ?
                   ORDER BY l.action_time DESC";
        $logs = CON::selectArrayDB( [$doc['document_id']], $logSql ) ?? [];

        $json_data['status'] = 'success';
        $json_data['doc']    = $doc;
        $json_data['logs']   = $logs;
        break;

     case 'get_statuses':
        // 1. รับค่า creator_id ผ่านฟังก์ชัน sanitize (ปลอดภัยกว่า $_GET โดยตรง)
        // กำหนด default เป็น 0 ถ้าไม่ส่งมา
        $creator_id = sanitizeGetParam('creator_id', 'int', 0); 

        // 2. กำหนด Path ไฟล์ JSON 
        // __DIR__ คือโฟลเดอร์ปัจจุบัน (api) ถอยกลับไป 1 ขั้น (..) แล้วเข้า data
        $jsonFile = __DIR__ . '/../data/workflow_data.json'; 
        
        $statuses = [];

        if (file_exists($jsonFile)) {
            $jsonContent = file_get_contents($jsonFile);
            $workflows = json_decode($jsonContent, true) ?? [];
            
            // 3. วนลูปหาหมวดหมู่
            foreach ($workflows as $wf) {
                // กรองเฉพาะ Workflow ของ Creator คนนี้ (หรือของคนที่ ID=0/Null ถ้าเป็นระบบกลาง)
                // หมายเหตุ: ต้องแก้ตรงนี้ให้ยืดหยุ่น ถ้า workflow ไม่ระบุ created_by ให้ถือว่าเป็นของทุกคน
                $wfCreator = $wf['created_by'] ?? 0;
                
                if ($wfCreator == $creator_id || $wfCreator == 0) {
                    // เช็คว่ามี key 'statuses' และเป็น array ไหม กัน error
                    if (isset($wf['statuses']) && is_array($wf['statuses'])) {
                        foreach ($wf['statuses'] as $st) {
                            $statuses[] = [
                                'status_name' => $st['name'],
                                'color'       => $st['color'],
                                'category'    => $wf['name'] // ชื่อหมวดหมู่
                            ];
                        }
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

        // 4. ส่งค่ากลับ
        $json_data['data']   = $statuses;
        $json_data['status'] = 'success';
        break;

    case 'search':
        // ค้นหาเอกสารจาก keyword
        $keyword = sanitizeGetParam( 'keyword', 'string', '' );
        if ( empty( $keyword ) ) {
            $json_data['status']  = 'error';
            $json_data['message'] = 'ระบุคำค้นหา';
        } else {
            $sql = "SELECT d.*, dt.type_name
                    FROM documents d
                    LEFT JOIN document_type dt ON d.type_id = dt.type_id
                    WHERE d.document_code LIKE ? OR d.title LIKE ?
                    ORDER BY d.created_at DESC LIMIT 10";
            $params = ["%{$keyword}%", "%{$keyword}%"];
            $json_data['data'] = CON::selectArrayDB( $params, $sql );
            $json_data['status'] = 'success';
        }
        break;

    case 'history':
        $line_id = sanitizeGetParam('line_id', 'string', '');
        if (empty($line_id)) {
            $sql = "SELECT l.*, d.title, d.document_code, u.fullname
                FROM document_status_log l
                LEFT JOIN documents d ON l.document_id = d.document_id
                LEFT JOIN users u ON l.action_by = u.user_id
                ORDER BY l.action_time DESC LIMIT 50";
            $json_data['data']   = CON::selectArrayDB( [], $sql );
        } else {
            $sql = "SELECT l.*, d.title, d.document_code 
                    FROM document_status_log l
                    JOIN documents d ON l.document_id = d.document_id
                    WHERE l.line_user_id_action = ?
                    ORDER BY l.action_time DESC LIMIT 20";
            $json_data['data']   = CON::selectArrayDB( [$line_id], $sql );
        }
        $json_data['status'] = 'success';
        break;
        
    
    case 'manage-workflow':
        $jsonFile = __DIR__ . '/../data/workflow_data.json';
        
        // Helper Closures (ฟังก์ชันช่วยจัดการ JSON)
        $getJson = function() use ($jsonFile) {
            if (!file_exists($jsonFile)) {
                if (!is_dir(dirname($jsonFile))) mkdir(dirname($jsonFile), 0777, true);
                file_put_contents($jsonFile, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
            return json_decode(file_get_contents($jsonFile), true) ?? [];
        };

        $saveJson = function($data) use ($jsonFile) {
            return file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        };

        $getDefaultData = function() {
            return [
                'id' => 'cat_default',
                'name' => 'สถานะพื้นฐาน (General)',
                'created_by' => 'system',
                'statuses' => [
                    ['id' => 'st_def_1', 'name' => 'ลงทะเบียนเอกสารใหม่', 'color' => '#6c757d'],
                    ['id' => 'st_def_2', 'name' => 'รับเอกสาร', 'color' => '#ffc107'],
                    ['id' => 'st_def_3', 'name' => 'ส่งต่อ', 'color' => '#0dcaf0'],
                    ['id' => 'st_def_4', 'name' => 'ได้รับแล้ว', 'color' => '#198754']
                ]
            ];
        };

        $action = $_REQUEST['action'] ?? '';
        $currentUser = $_REQUEST['user_id'] ?? $_SESSION['user_id'] ?? 0;

        try {
            if ($action === 'list') {
                $data = $getJson();
                
                // 1. ตรวจสอบว่ามีหมวดพื้นฐานหรือยัง?
                $hasDefault = false;
                foreach ($data as $cat) {
                    if (isset($cat['id']) && $cat['id'] === 'cat_default') {
                        $hasDefault = true;
                        break;
                    }
                }

                // 2. ถ้ายังไม่มี ให้แทรกเข้าไปเป็น "อันแรก"
                if (!$hasDefault) {
                    $defaultCategory = $getDefaultData();
                    array_unshift($data, $defaultCategory);
                    $saveJson($data);
                }
                
                // 3. กรองข้อมูลตาม User
                if ($currentUser) {
                    $data = array_values(array_filter($data, function($item) use ($currentUser) {
                        $isOwner = isset($item['created_by']) && $item['created_by'] == $currentUser;
                        $isSystem = isset($item['id']) && $item['id'] === 'cat_default';
                        return $isOwner || $isSystem;
                    }));
                }

                $json_data['success'] = true;
                $json_data['data'] = $data;
                $json_data['status'] = 'success';
            } 
            elseif ($action === 'add_category') {
                $name = $_POST['category_name'] ?? '';
                if ($name) {
                    $data = $getJson();
                    $newCat = [
                        'id' => 'cat_' . uniqid(),
                        'name' => $name,
                        'created_by' => $_SESSION['user_id'] ?? 0,
                        'statuses' => []
                    ];
                    $data[] = $newCat;
                    $saveJson($data);
                    $json_data['success'] = true;
                    $json_data['status'] = 'success';
                } else {
                    $json_data['success'] = false;
                    $json_data['message'] = 'Missing category name';
                }
            }
            elseif ($action === 'edit_category') {
                $id = $_POST['id'] ?? '';
                $name = $_POST['category_name'] ?? '';
                
                if ($id === 'cat_default') {
                    $json_data['success'] = false;
                    $json_data['message'] = 'ไม่สามารถแก้ไขชื่อหมวดหมู่พื้นฐานได้';
                } elseif ($id && $name) {
                    $data = $getJson();
                    foreach ($data as &$cat) {
                        if ($cat['id'] === $id) {
                            $cat['name'] = $name;
                            break;
                        }
                    }
                    $saveJson($data);
                    $json_data['success'] = true;
                    $json_data['status'] = 'success';
                }
            }
            elseif ($action === 'delete_category') {
                $id = $_POST['id'] ?? '';
                if ($id === 'cat_default') {
                    $json_data['success'] = false;
                    $json_data['message'] = 'ไม่สามารถลบหมวดหมู่พื้นฐานของระบบได้';
                } elseif ($id) {
                    $data = $getJson();
                    $data = array_values(array_filter($data, fn($c) => $c['id'] !== $id));
                    $saveJson($data);
                    $json_data['success'] = true;
                    $json_data['status'] = 'success';
                }
            }
            elseif ($action === 'add_status') {
                $catId = $_POST['category_id'] ?? '';
                $name = $_POST['status_name'] ?? '';
                $color = $_POST['color_class'] ?? 'secondary';
                if ($catId && $name) {
                    $data = $getJson();
                    foreach ($data as &$cat) {
                        if ($cat['id'] === $catId) {
                            $cat['statuses'][] = ['id' => 'st_' . uniqid(), 'name' => $name, 'color' => $color];
                            break;
                        }
                    }
                    $saveJson($data);
                    $json_data['success'] = true;
                    $json_data['status'] = 'success';
                }
            }
            elseif ($action === 'edit_status') {
                $catId = $_POST['category_id'] ?? '';
                $stId = $_POST['status_id'] ?? '';
                $name = $_POST['status_name'] ?? '';
                $color = $_POST['color_class'] ?? 'secondary';

                if ($catId && $stId) {
                    $data = $getJson();
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
                    $saveJson($data);
                    $json_data['success'] = true;
                    $json_data['status'] = 'success';
                }
            }
            elseif ($action === 'delete_status') {
                $catId = $_POST['category_id'] ?? '';
                $statusId = $_POST['status_id'] ?? '';

                if (strpos($statusId, 'st_def_') === 0) {
                    $json_data['success'] = false;
                    $json_data['message'] = 'ไม่สามารถลบสถานะพื้นฐานของระบบได้';
                } elseif ($catId && $statusId) {
                    $data = $getJson();
                    foreach ($data as &$cat) {
                        if ($cat['id'] === $catId) {
                            $cat['statuses'] = array_values(array_filter($cat['statuses'], fn($s) => $s['id'] !== $statusId));
                            break;
                        }
                    }
                    $saveJson($data);
                    $json_data['success'] = true;
                    $json_data['status'] = 'success';
                }
            }
            elseif ($action === 'reorder_status') {
                $catId = $_POST['category_id'] ?? '';
                $sortedIds = json_decode($_POST['sorted_ids'] ?? '[]', true);
                if ($catId && !empty($sortedIds)) {
                    $data = $getJson();
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
                    $saveJson($data);
                    $json_data['success'] = true;
                    $json_data['status'] = 'success';
                }
            }
            else {
                $json_data['success'] = false;
                $json_data['message'] = 'Invalid action';
            }
        } catch (Exception $e) {
            $json_data['success'] = false;
            $json_data['message'] = 'Error: ' . $e->getMessage();
        }
        break;

    default:
        http_response_code( 400 );
        $json_data = [
            'success' => false,
            'message' => 'ไม่ได้ระบุหมายเหตุหรือ action:' . $GET_DEV . ' ไม่ถูกต้อง'
        ];
        break;
}

echo json_encode( $json_data, JSON_UNESCAPED_UNICODE );

// HTTP Response Codes Reference:
// 200 OK
// 201 CREATED  -  [POST,PUT]
// 204 NO CONTENT  -  [DELETE,PUT]
// 400 BAD REQUEST
// 401 UNAUTHORIZED
// 403 FORBIDDEN
// 404 NO FOUND
// 405 METHOD NOT ALLOWED
// 409 CONFLICT
// 500 INTERNAL SERVER ERROR
// 405 METHOD NOT ALLOWED
// 409 CONFLICT
// 500 INTERNAL SERVER ERROR
