<?php

// Show all PHP errors
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// เริ่มจับเวลา
$start_time = microtime(true);

session_start();
require_once 'config/db.php';

// ตรวจสอบสิทธิ์
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

// --- ส่วนดึงข้อมูล ---
$stats = [ 'total' => 0, 'success' => 0, 'pending' => 0, 'late' => 0 ];
$recent_docs = [];

$is_admin = (stripos($_SESSION['role'], 'admin') !== false);
$user_id = $_SESSION['user_id'];

try {
    if (isset($pdo)) {
        // 1. Stats
        $where_clause = $is_admin ? "" : "WHERE created_by = $user_id";
        $where_success = $is_admin ? "WHERE current_status = 'Received'" : "WHERE current_status = 'Received' AND created_by = $user_id";
        $where_pending = $is_admin ? "WHERE current_status IN ('Registered', 'Sent')" : "WHERE current_status IN ('Registered', 'Sent') AND created_by = $user_id";
        $where_late    = $is_admin ? "WHERE current_status = 'Late'" : "WHERE current_status = 'Late' AND created_by = $user_id";

        $stats['total']   = $pdo->query("SELECT COUNT(*) FROM documents $where_clause")->fetchColumn();
        $stats['success'] = $pdo->query("SELECT COUNT(*) FROM documents $where_success")->fetchColumn();
        $stats['pending'] = $pdo->query("SELECT COUNT(*) FROM documents $where_pending")->fetchColumn();
        $stats['late']    = $pdo->query("SELECT COUNT(*) FROM documents $where_late")->fetchColumn();

        // 2. Recent Docs
        $sql = "SELECT d.*, dt.type_name
                FROM documents d
                LEFT JOIN document_type dt ON d.type_id = dt.type_id ";
        if (!$is_admin) { $sql .= " WHERE d.created_by = $user_id "; }
        $sql .= " ORDER BY d.created_at DESC LIMIT 10";

        $stmt = $pdo->query($sql);
        $recent_docs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {}

function getStatusBadge($status) {
    // Use switch for compatibility with PHP versions that don't support match()
    switch ($status) {
        case 'Received':
            return '<span class="badge rounded-pill bg-success">สำเร็จ/ได้รับแล้ว</span>';
        case 'Registered':
            return '<span class="badge rounded-pill bg-info text-dark">ลงทะเบียนใหม่</span>';
        case 'Sent':
        case 'กำลังนำส่ง':
            return '<span class="badge rounded-pill bg-warning text-dark">กำลังนำส่ง</span>';
        case 'Late':
            return '<span class="badge rounded-pill bg-danger">ล่าช้า</span>';
        default:
            return '<span class="badge rounded-pill bg-secondary">' . htmlspecialchars($status) . '</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - EDE System</title>

    <!-- Bootstrap & Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">

    <!-- QR Code Lib -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <style>
        .doc-link {
            color: #29B6F6; font-weight: bold; text-decoration: none;
            background: rgba(41, 182, 246, 0.1); padding: 5px 10px; border-radius: 20px; transition: 0.2s;
        }
        .doc-link:hover { background: #29B6F6; color: white; }
        .view-count-badge { font-size: 0.85rem; color: #555; background: #eee; padding: 5px 10px; border-radius: 15px; display: inline-flex; align-items: center; gap: 5px; }
    </style>
</head>
<body>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <?php
            $page_title = "Dashboard (ภาพรวม)";
            $header_class = "header-dashboard";
            include 'includes/topbar.php';
        ?>

        <div class="page-content">
            <!-- Load Time Display -->
            <div class="alert alert-info rounded-4 mb-4 shadow-sm" style="font-size: 0.85rem;">
                <i class="fas fa-tachometer-alt me-2"></i>
                <strong>เวลาโหลดหน้า:</strong>
                <span id="loadTime">กำลังคำนวณ...</span> วินาที
            </div>

            <!-- Cards สรุปยอด -->
            <h5 class="mb-4 fw-bold text-secondary">**สรุปสถานะประจำวัน** <?php echo $is_admin ? '(ทั้งหมด)' : '(เฉพาะของคุณ)'; ?></h5>
            <div class="row mb-5 g-4">
                <div class="col-md-3"><div class="p-4 rounded-5 text-center text-white shadow-sm position-relative overflow-hidden" style="background: linear-gradient(135deg, #4FC3F7, #29B6F6);"><i class="fas fa-folder-open fa-4x position-absolute" style="opacity:0.2; right:-10px; bottom:-10px;"></i><h2 class="fw-bold mb-0"><?php echo number_format($stats['total']); ?></h2><small>เอกสารทั้งหมด</small></div></div>
                <div class="col-md-3"><div class="p-4 rounded-5 text-center text-white shadow-sm" style="background: linear-gradient(135deg, #81C784, #66BB6A);"><i class="fas fa-check-circle fa-4x position-absolute" style="opacity:0.2; right:-10px; bottom:-10px;"></i><h2 class="fw-bold mb-0"><?php echo number_format($stats['success']); ?></h2><small>สำเร็จ</small></div></div>
                <div class="col-md-3"><div class="p-4 rounded-5 text-center text-white shadow-sm" style="background: linear-gradient(135deg, #FFB74D, #FFA726);"><i class="fas fa-clock fa-4x position-absolute" style="opacity:0.2; right:-10px; bottom:-10px;"></i><h2 class="fw-bold mb-0"><?php echo number_format($stats['pending']); ?></h2><small>ค้างส่ง</small></div></div>
                <div class="col-md-3"><div class="p-4 rounded-5 text-center text-white shadow-sm" style="background: linear-gradient(135deg, #E57373, #EF5350);"><i class="fas fa-exclamation-triangle fa-4x position-absolute" style="opacity:0.2; right:-10px; bottom:-10px;"></i><h2 class="fw-bold mb-0"><?php echo number_format($stats['late']); ?></h2><small>ล่าช้า</small></div></div>
            </div>

            <!-- ตารางรายการล่าสุด -->
            <h5 class="mb-3 fw-bold text-secondary">**รายการเอกสารล่าสุด**</h5>
            <div class="table-responsive rounded-4 shadow-sm border">
                <table class="table table-hover mb-0 align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3">เลขทะเบียน</th>
                            <th class="py-3 text-start">เรื่อง</th>
                            <th class="py-3">วันที่สร้าง</th>
                            <th class="py-3">สถานะ / การเข้าชม</th>
                            <th class="py-3">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recent_docs) > 0): ?>
                            <?php foreach ($recent_docs as $doc): ?>
                                <tr>
                                    <td>
                                        <!-- ลิงก์กดดู Modal -->
                                        <a href="javascript:void(0)"
                                           onclick="openDetailModal('<?php echo $doc['document_code']; ?>')"
                                           class="doc-link shadow-sm">
                                            <i class="fas fa-qrcode me-1"></i> <?php echo htmlspecialchars($doc['document_code']); ?>
                                        </a>
                                    </td>
                                    <td class="text-start">
                                        <?php echo htmlspecialchars($doc['title']); ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($doc['type_name'] ?? '-'); ?></small>
                                    </td>
                                    <td class="text-muted small">
                                        <?php echo date('d/m/Y H:i', strtotime($doc['created_at'])); ?>
                                    </td>
                                    <td>
                                        <?php echo getStatusBadge($doc['current_status']); ?>
                                        <!-- แสดงยอดวิวในตาราง -->
                                        <div class="mt-1 text-muted small">
                                            <i class="far fa-eye"></i> <?php echo number_format($doc['view_count'] ?? 0); ?> ครั้ง
                                        </div>
                                    </td>
                                    <td>
                                        <button onclick="showQRModal('<?php echo htmlspecialchars($doc['document_code']); ?>', '<?php echo htmlspecialchars($doc['title']); ?>')" class="btn btn-sm btn-light border rounded-pill shadow-sm text-dark"><i class="fas fa-qrcode text-success"></i> QR</button>
                                        <a href="print_cover.php?code=<?php echo $doc['document_code']; ?>" class="btn btn-sm btn-light border rounded-circle shadow-sm ms-1"><i class="fas fa-print"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="py-4 text-muted">ยังไม่มีข้อมูลเอกสาร</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- 1. Modal QR Code -->
<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 bg-light rounded-top-4">
                <h5 class="modal-title fw-bold text-secondary"><i class="fas fa-qrcode me-2"></i>QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <h5 id="modalDocTitle" class="fw-bold mb-1 text-primary">...</h5>
                <small id="modalDocCode" class="text-muted d-block mb-3">...</small>
                <div id="qrcode" class="d-flex justify-content-center my-3"></div>
                <p class="small text-muted mt-3">ใช้แอปพลิเคชันสแกนเพื่ออัปเดตสถานะ</p>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <a id="btnPrintLink" href="#" class="btn btn-primary rounded-pill px-4"><i class="fas fa-print me-2"></i>พิมพ์ใบปะหน้า</a>
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<!-- 2. Modal รายละเอียดเอกสาร (เพิ่มส่วนแสดงยอดวิว) -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0 bg-primary text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="fas fa-file-alt me-2"></i>รายละเอียดเอกสาร</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div id="modalLoading" class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>

                <div id="modalContent" style="display:none;">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <h4 id="d_title" class="fw-bold text-primary mb-3">...</h4>
                                <!-- *** แสดงยอดวิวที่นี่ *** -->
                                <span class="view-count-badge shadow-sm">
                                    <i class="far fa-eye text-primary"></i> ถูกสแกน: <strong id="d_views" class="text-dark">0</strong> ครั้ง
                                </span>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6"><small class="text-muted d-block">เลขทะเบียน</small><strong id="d_code" class="fs-5">...</strong></div>
                                <div class="col-md-6"><small class="text-muted d-block">สถานะปัจจุบัน</small><span id="d_status" class="badge bg-secondary">...</span></div>
                                <div class="col-md-6"><small class="text-muted d-block">ประเภท</small><span id="d_type">...</span></div>
                                <div class="col-md-6"><small class="text-muted d-block">วันที่สร้าง</small><span id="d_date">...</span></div>
                                <div class="col-md-6"><small class="text-muted d-block">ผู้ส่ง</small><span id="d_sender">...</span></div>
                                <div class="col-md-6"><small class="text-muted d-block">ผู้รับ</small><span id="d_receiver">...</span></div>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold text-secondary ps-2 border-start border-4 border-primary mb-3">ประวัติการดำเนินงาน (Timeline)</h6>
                    <div id="d_timeline" class="timeline ms-2"></div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light"><button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">ปิดหน้าต่าง</button></div>
        </div>
    </div>
</div>

<script>
    // คำนวณและแสดงเวลาโหลด
    window.addEventListener('load', function() {
        const serverTime = <?php echo ($start_time * 1000); ?>;
        const clientLoadTime = (performance.now() - (Date.now() - serverTime * 1000)) / 1000;
        const displayTime = (Date.now() / 1000 - <?php echo $start_time; ?>).toFixed(3);
        document.getElementById('loadTime').textContent = displayTime;
    });

    function showQRModal(docCode, docTitle) {
        document.getElementById('modalDocCode').innerText = "รหัส: " + docCode;
        document.getElementById('modalDocTitle').innerText = docTitle;
        document.getElementById('btnPrintLink').href = 'print_cover.php?code=' + docCode;
        const qrContainer = document.getElementById("qrcode");
        qrContainer.innerHTML = "";
        new QRCode(qrContainer, { text: docCode, width: 180, height: 180 });
        new bootstrap.Modal(document.getElementById('qrModal')).show();
    }

    async function openDetailModal(code) {
        new bootstrap.Modal(document.getElementById('detailModal')).show();
        document.getElementById('modalLoading').style.display = 'block';
        document.getElementById('modalContent').style.display = 'none';

        try {
            // เรียก API ตัวเดิม (เพราะเราแก้ให้มันส่ง view_count มาด้วยแล้ว)
            const res = await fetch(`api/get_doc_info.php?code=${code}`);
            const data = await res.json();

            if(data.error) throw new Error(data.error);

            const doc = data.doc;
            document.getElementById('d_title').innerText = doc.title;
            document.getElementById('d_code').innerText = doc.document_code;
            document.getElementById('d_status').innerText = doc.current_status;
            document.getElementById('d_type').innerText = doc.type_name || '-';
            document.getElementById('d_date').innerText = doc.created_at;
            document.getElementById('d_sender').innerText = doc.sender_name;
            document.getElementById('d_receiver').innerText = doc.receiver_name;

            // ใส่ตัวเลขยอดวิวลงไป
            document.getElementById('d_views').innerText = doc.view_count || 0;

            let html = '';
            if(data.logs && data.logs.length > 0) {
                data.logs.forEach((log, index) => {
                    const activeClass = index === 0 ? 'active' : '';
                    // แสดงชื่อคนทำรายการ (ถ้ามีรูปก็แสดงรูปด้วย)
                    const actor = log.actor_name_snapshot || log.fullname || 'Unknown';
                    const actorPic = log.actor_pic_snapshot ? `<img src="${log.actor_pic_snapshot}" class="rounded-circle me-1" width="20">` : '<i class="fas fa-user-circle me-1"></i>';

                    html += `
                        <div class="timeline-item">
                            <div class="timeline-dot ${activeClass}"></div>
                            <div class="ps-4">
                                <div class="d-flex justify-content-between">
                                    <strong class="text-dark">${log.status}</strong>
                                    <small class="text-muted">${log.action_time}</small>
                                </div>
                                <small class="text-secondary d-flex align-items-center mt-1">
                                    โดย: ${actorPic} ${actor}
                                </small>
                                ${log.location_note ? `<br><small class="text-danger"><i class="fas fa-map-marker-alt"></i> ${log.location_note}</small>` : ''}
                            </div>
                        </div>`;
                });
            } else {
                html = '<p class="text-muted ms-4">ยังไม่มีประวัติ</p>';
            }
            document.getElementById('d_timeline').innerHTML = html;

            document.getElementById('modalLoading').style.display = 'none';
            document.getElementById('modalContent').style.display = 'block';

        } catch (err) {
            alert("ไม่สามารถโหลดข้อมูลได้: " + err.message);
        }
    }
</script>

</body>
</html>