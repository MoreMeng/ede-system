<?php
    $page_title   = "ประวัติการทำงานของคุณ";
    $header_class = "header-dashboard";
    include 'includes/topbar.php';

    // ---------------------------------------------------------
    // ส่วนที่ 1: AJAX Handler สำหรับดึงข้อมูลรายละเอียด + Timeline (JSON)
    // ---------------------------------------------------------
    if (isset($_GET['ajax_get_detail']) && isset($_GET['doc_id'])) {
        while (ob_get_level()) { ob_end_clean(); } // Clean buffer
        header('Content-Type: application/json');

        $doc_id = $_GET['doc_id'];
        $response = ['success' => false];

        // 1. ดึงรายละเอียดเอกสาร (Document Details)
        $sql_doc = "SELECT d.*, dt.type_name 
                    FROM documents d 
                    LEFT JOIN document_type dt ON d.type_id = dt.type_id 
                    WHERE d.document_id = ?";
        $docData = CON::selectArrayDB([$doc_id], $sql_doc);

        if (!empty($docData)) {
            $d = $docData[0];
            $response['doc'] = [
                'code' => $d['document_code'],
                'title' => $d['title'],
                'type' => $d['type_name'] ?? '-',
                'status' => $d['current_status'],
                'created_at' => date('d/m/Y H:i', strtotime($d['created_at'])),
                'sender' => $d['sender'] ?? '-',
                'receiver' => $d['receiver'] ?? '-',
                'view_count' => number_format($d['view_count'] ?? 0)
            ];

            // 2. ดึงประวัติ Timeline (History)
            // ใช้ l.* ไว้หลังเพื่อให้ค่าหลักยึดตาม Log
            $sql_hist = "SELECT u.*, l.* FROM document_status_log l 
                         LEFT JOIN users u ON l.action_by = u.user_id 
                         WHERE l.document_id = ? 
                         ORDER BY l.action_time DESC";
            $histData = CON::selectArrayDB([$doc_id], $sql_hist) ?? [];

            $html = '<ul class="list-group list-group-flush">';
            if (count($histData) > 0) {
                foreach ($histData as $h) {
                    $h_time = date('d/m/Y H:i', strtotime($h['action_time']));
                    
                    // ---------------------------------------------------------
                    // Identity Detection (ระบบค้นหาชื่อแบบละเอียด)
                    // ---------------------------------------------------------
                    $found_name = '';

                    // 1. ลองหาจาก Snapshot ใน Log ก่อน (แม่นยำที่สุด ณ เวลาที่บันทึก)
                    if (!empty($h['actor_name_snapshot'])) {
                        $found_name = $h['actor_name_snapshot'];
                    }
                    // 2. ถ้าไม่มี Snapshot ให้ลองหาจากคอลัมน์ต่างๆ ที่ Join มาได้
                    elseif (!empty($h['first_name'])) {
                        $found_name = $h['first_name'] . ' ' . ($h['last_name'] ?? '');
                    } elseif (!empty($h['name'])) {
                        $found_name = $h['name'];
                    } elseif (!empty($h['username'])) {
                        $found_name = $h['username'];
                    } elseif (!empty($h['fullname'])) { // เผื่อใช้ชื่อ fullname
                        $found_name = $h['fullname'];
                    } elseif (!empty($h['eng_name'])) { // เผื่อใช้ชื่อภาษาอังกฤษ
                        $found_name = $h['eng_name'];
                    }
                    
                    // 3. ถ้ายังไม่เจอ ลองเช็คว่าเป็นตัวเองหรือไม่ (ดึงจาก Session)
                    if (empty($found_name) && isset($_SESSION['user_id']) && isset($h['action_by'])) {
                        if ($h['action_by'] == $_SESSION['user_id']) {
                            // ลองหาชื่อใน Session
                            if (!empty($_SESSION['user_name'])) $found_name = $_SESSION['user_name'] . " (คุณ)";
                            elseif (!empty($_SESSION['name'])) $found_name = $_SESSION['name'] . " (คุณ)";
                            elseif (!empty($_SESSION['first_name'])) $found_name = $_SESSION['first_name'] . " (คุณ)";
                        }
                    }

                    // 4. กำหนดค่าที่จะแสดงผลสุดท้าย
                    if (!empty($found_name)) {
                        $user_name = $found_name;
                    } else {
                        // กรณีหาไม่เจอจริงๆ
                        $uid = $h['action_by'] ?? '';
                        if (empty($uid) || $uid == 0) {
                            $user_name = "ไม่ระบุ (System)";
                        } else {
                            $user_name = "User ID: $uid"; 
                        }
                    }
                    
                    // Image Check
                    $img_src = '';
                    // 1. ลองหาจาก Snapshot รูปภาพใน Log ก่อน
                    if (!empty($h['actor_pic_snapshot'])) {
                        $img_src = $h['actor_pic_snapshot'];
                    }
                    // 2. ถ้าไม่มี Snapshot ให้ลองหาจากคอลัมน์ต่างๆ
                    elseif (!empty($h['profile_img'])) $img_src = $h['profile_img'];
                    elseif (!empty($h['image'])) $img_src = $h['image'];
                    elseif (!empty($h['avatar'])) $img_src = $h['avatar']; // เพิ่ม avatar
                    
                    // Icon/Image HTML
                    // ปรับปรุง: ลบ file_exists ออกเพื่อให้แสดงผลรูปได้แม้ Path ใน Server จะหาไม่เจอ
                    if (!empty($img_src)) {
                        $user_icon = "
                        <div class='me-2 position-relative' style='width:35px; height:35px;'>
                            <img src='$img_src' class='rounded-circle border w-100 h-100' style='object-fit:cover;' 
                                 onerror=\"this.style.display='none'; this.nextElementSibling.style.display='flex';\">
                            <div class='rounded-circle bg-light align-items-center justify-content-center border w-100 h-100 position-absolute top-0 start-0' style='display:none;'>
                                <i class='fas fa-user text-secondary'></i>
                            </div>
                        </div>";
                    } else {
                        $user_icon = "<div class='rounded-circle bg-light d-flex align-items-center justify-content-center me-2 border' style='width:35px; height:35px;'><i class='fas fa-user text-secondary'></i></div>";
                    }

                    $h_status = $h['status'] ?? '-';
                    $h_ip     = $h['ip_address'] ?? '-';
                    $h_device = $h['device_info'] ?? '-'; // เพิ่มตัวแปร device_info

                    // สร้าง List Item
                    $html .= "
                    <li class='list-group-item px-0 border-bottom-0'>
                        <div class='d-flex align-items-start'>
                            <div class='me-3 text-center' style='width: 60px;'>
                                <small class='text-muted d-block' style='font-size: 0.75rem;'>".date('H:i', strtotime($h['action_time']))."</small>
                                <small class='text-muted' style='font-size: 0.7rem;'>".date('d/m/y', strtotime($h['action_time']))."</small>
                            </div>
                            <div class='flex-grow-1'>
                                <div class='d-flex align-items-center mb-1'>
                                    $user_icon
                                    <div>
                                        <span class='fw-bold text-dark d-block' style='line-height:1.2;'>$user_name</span>
                                        <span class='badge bg-light text-secondary border rounded-pill small'>$h_status</span>
                                    </div>
                                </div>
                                <div class='small text-muted ps-5 ms-1'>
                                    <span class='me-2'><i class='fas fa-network-wired me-1'></i> IP: $h_ip</span>
                                    <span><i class='fas fa-mobile-alt me-1'></i> $h_device</span>
                                </div>
                            </div>
                        </div>
                    </li>";
                }
            } else {
                $html .= "<li class='list-group-item text-center text-muted'>ยังไม่มีประวัติ</li>";
            }
            $html .= '</ul>';

            $response['history_html'] = $html;
            $response['success'] = true;
        }

        echo json_encode($response);
        exit;
    }

    // ---------------------------------------------------------
    // Main Page Logic
    // ---------------------------------------------------------
    
    // Helper Function: Badge สถานะ (เหมือนตัวอย่าง)
    function getStatusBadge($status) {
        switch ($status) {
            case 'Received': return '<span class="badge rounded-pill bg-success">สำเร็จ/ได้รับแล้ว</span>';
            case 'Registered': return '<span class="badge rounded-pill bg-info text-dark">ลงทะเบียนใหม่</span>';
            case 'Sent': return '<span class="badge rounded-pill bg-warning text-dark">กำลังนำส่ง</span>';
            case 'Late': return '<span class="badge rounded-pill bg-danger">ล่าช้า</span>';
            default: return '<span class="badge rounded-pill bg-secondary">' . htmlspecialchars($status) . '</span>';
        }
    }

    // Query User's History
    $sql = "SELECT l.*, d.title, d.document_code, d.current_status 
            FROM document_status_log l 
            JOIN documents d ON l.document_id = d.document_id 
            WHERE l.action_by = ? 
            ORDER BY l.action_time DESC 
            LIMIT 50";
            
    $history = CON::selectArrayDB([$_SESSION['user_id']], $sql) ?? [];

    // Generate Rows
    $historyRows = '';
    if (count($history) > 0) {
        foreach ($history as $row) {
            $time = date('d/m/Y H:i', strtotime($row['action_time']));
            $code = $row['document_code'] ?? '';
            $title = $row['title'] ?? '';
            $status = $row['status'] ?? '-';
            $ip = $row['ip_address'] ?? '';
            $device = $row['device_info'] ?? '-'; // เพิ่ม device_info
            $docId = $row['document_id'];

            // Style Link: ใช้คลาส doc-link สีฟ้าเหมือน Dashboard
            $codeLink = "<a href='javascript:void(0)' onclick='openDetailModal($docId)' class='doc-link shadow-sm'><i class='fas fa-search me-1'></i>$code</a>";
            
            // ใช้ Badge สวยๆ
            $statusBadge = getStatusBadge($status);

            $historyRows .= "<tr>
                <td class='ps-4 text-muted small'>$time</td>
                <td>
                    $codeLink
                    <div class='mt-1 text-dark small'>$title</div>
                </td>
                <td>$statusBadge</td>
                <td class='text-muted small'>
                    <div><i class='fas fa-desktop me-1'></i>$ip</div>
                    <div class='text-secondary' style='font-size: 0.75rem;'><i class='fas fa-info-circle me-1'></i>$device</div>
                </td>
            </tr>";
        }
    } else {
        $historyRows = '<tr><td colspan="4" class="text-center py-5 text-muted">ยังไม่พบประวัติการทำงานของคุณ</td></tr>';
    }
?>

<style>
    /* สไตล์ปุ่มเลขเอกสารเหมือน Dashboard */
    .doc-link {
        color: #29B6F6; font-weight: bold; text-decoration: none;
        background: rgba(41, 182, 246, 0.1); padding: 5px 12px; border-radius: 20px; transition: 0.2s; display: inline-block;
    }
    .doc-link:hover { background: #29B6F6; color: white; }
    
    .view-count-badge { font-size: 0.85rem; color: #555; background: #eee; padding: 5px 10px; border-radius: 15px; display: inline-flex; align-items: center; gap: 5px; }
</style>

<div class="page-content">
    
    <h5 class="mb-4 fw-bold text-secondary">**🕒 รายการที่คุณเคยสแกน/อัปเดต**</h5>

    <div class="table-responsive rounded-4 shadow-sm border">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th class="py-3 ps-4">เวลา</th>
                    <th class="py-3">เอกสาร (กดเพื่อดูรายละเอียด)</th>
                    <th class="py-3">สถานะตอนสแกน</th>
                    <th class="py-3">อุปกรณ์</th>
                </tr>
            </thead>
            <tbody>
                <?php echo $historyRows; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal รายละเอียดเอกสาร (Dashboard Style) -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0 bg-primary text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="fas fa-file-alt me-2"></i>รายละเอียดเอกสาร</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                
                <!-- Loading State -->
                <div id="modalLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 text-muted">กำลังโหลดข้อมูล...</div>
                </div>

                <!-- Content State -->
                <div id="modalContent" style="display:none;">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h4 id="d_title" class="fw-bold text-primary mb-0">...</h4>
                                <span class="view-count-badge shadow-sm">
                                    <i class="far fa-eye text-primary"></i> <strong id="d_views" class="text-dark ms-1">0</strong>
                                </span>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6"><small class="text-muted d-block">เลขทะเบียน</small><strong id="d_code" class="fs-5 text-dark">...</strong></div>
                                <div class="col-md-6"><small class="text-muted d-block">สถานะปัจจุบัน</small><span id="d_status" class="badge bg-secondary">...</span></div>
                                <div class="col-md-6"><small class="text-muted d-block">ประเภท</small><span id="d_type" class="text-dark">...</span></div>
                                <div class="col-md-6"><small class="text-muted d-block">วันที่สร้าง</small><span id="d_date" class="text-dark">...</span></div>
                                <div class="col-md-6"><small class="text-muted d-block">ผู้ส่ง</small><span id="d_sender" class="text-dark">...</span></div>
                                <div class="col-md-6"><small class="text-muted d-block">ผู้รับ</small><span id="d_receiver" class="text-dark">...</span></div>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold text-secondary ps-2 border-start border-4 border-primary mb-3">ประวัติการดำเนินงาน (Timeline)</h6>
                    <div id="d_timeline" class="timeline ms-1">
                        <!-- Timeline Content will be loaded here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>

<script>
function openDetailModal(docId) {
    // 1. เปิด Modal
    var myModal = new bootstrap.Modal(document.getElementById('detailModal'));
    myModal.show();
    
    // 2. แสดง Loading / ซ่อน Content
    document.getElementById('modalLoading').style.display = 'block';
    document.getElementById('modalContent').style.display = 'none';
    
    // 3. เรียก AJAX
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('ajax_get_detail', '1');
    currentUrl.searchParams.set('doc_id', docId);

    fetch(currentUrl)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // ใส่ข้อมูลส่วนหัว (Details)
            document.getElementById('d_title').innerText = data.doc.title;
            document.getElementById('d_code').innerText = data.doc.code;
            document.getElementById('d_views').innerText = data.doc.view_count;
            document.getElementById('d_type').innerText = data.doc.type;
            document.getElementById('d_date').innerText = data.doc.created_at;
            document.getElementById('d_sender').innerText = data.doc.sender;
            document.getElementById('d_receiver').innerText = data.doc.receiver;
            
            // ใส่ Badge Status
            document.getElementById('d_status').innerHTML = `<span class='badge bg-info text-dark'>${data.doc.status}</span>`;

            // ใส่ข้อมูล Timeline (HTML)
            document.getElementById('d_timeline').innerHTML = data.history_html;

            // สลับการแสดงผล
            document.getElementById('modalLoading').style.display = 'none';
            document.getElementById('modalContent').style.display = 'block';
        } else {
            alert('ไม่พบข้อมูลเอกสาร');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('d_timeline').innerHTML = '<div class="text-danger text-center">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>';
        document.getElementById('modalLoading').style.display = 'none';
        document.getElementById('modalContent').style.display = 'block';
    });
}
</script>