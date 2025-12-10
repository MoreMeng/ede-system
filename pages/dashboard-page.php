<?php
// Dashboard Page - Logic Section

// เริ่มจับเวลา
$start_time = microtime(true);
$time_logs = [];

// ดึงข้อมูลสถิติและเอกสารล่าสุด
$stats = [ 'total' => 0, 'success' => 0, 'pending' => 0, 'late' => 0 ];
$recent_docs = [];

$is_admin = (stripos($_SESSION['role'], 'admin') !== false);
$user_id = $_SESSION['user_id'];

try {
    if (isset($pdo)) {
        // 1. Stats
        $time_logs['stats_queries'] = microtime(true);
        $where_clause = $is_admin ? "" : "WHERE created_by = $user_id";
        $where_success = $is_admin ? "WHERE current_status = 'Received'" : "WHERE current_status = 'Received' AND created_by = $user_id";
        $where_pending = $is_admin ? "WHERE current_status IN ('Registered', 'Sent')" : "WHERE current_status IN ('Registered', 'Sent') AND created_by = $user_id";
        $where_late    = $is_admin ? "WHERE current_status = 'Late'" : "WHERE current_status = 'Late' AND created_by = $user_id";

        $stats['total']   = $pdo->query("SELECT COUNT(*) FROM documents $where_clause")->fetchColumn();
        $stats['success'] = $pdo->query("SELECT COUNT(*) FROM documents $where_success")->fetchColumn();
        $stats['pending'] = $pdo->query("SELECT COUNT(*) FROM documents $where_pending")->fetchColumn();
        $stats['late']    = $pdo->query("SELECT COUNT(*) FROM documents $where_late")->fetchColumn();
        $time_logs['stats_queries'] = microtime(true) - $time_logs['stats_queries'];

        // 2. Recent Docs
        $time_logs['recent_docs_query'] = microtime(true);
        $sql = "SELECT d.*, dt.type_name
                FROM documents d
                LEFT JOIN document_type dt ON d.type_id = dt.type_id ";
        if (!$is_admin) { $sql .= " WHERE d.created_by = $user_id "; }
        $sql .= " ORDER BY d.created_at DESC LIMIT 10";

        $stmt = $pdo->query($sql);
        $recent_docs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $time_logs['recent_docs_query'] = microtime(true) - $time_logs['recent_docs_query'];
    }
} catch (PDOException $e) {}

$total_time = microtime(true) - $start_time;

function getStatusBadge($status) {
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

// ส่งค่าเวลาไปให้ JavaScript
$jsVars = "const SERVER_TIME_MS = " . number_format($total_time * 1000, 2) . ";";

// ตั้งค่าสำหรับ topbar
$page_title = "Dashboard (ภาพรวม)";
$header_class = "header-dashboard";
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
                <div class="col-md-3">
                    <div class="p-4 rounded-5 text-center text-white shadow-sm position-relative overflow-hidden" style="background: linear-gradient(135deg, #4FC3F7, #29B6F6);">
                        <i class="fas fa-folder-open fa-4x position-absolute" style="opacity:0.2; right:-10px; bottom:-10px;"></i>
                        <h2 class="fw-bold mb-0"><?php echo number_format($stats['total']); ?></h2>
                        <small>เอกสารทั้งหมด</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-4 rounded-5 text-center text-white shadow-sm" style="background: linear-gradient(135deg, #81C784, #66BB6A);">
                        <h2 class="fw-bold mb-0"><?php echo number_format($stats['success']); ?></h2>
                        <small>สำเร็จ</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-4 rounded-5 text-center text-white shadow-sm" style="background: linear-gradient(135deg, #FFB74D, #FFA726);">
                        <h2 class="fw-bold mb-0"><?php echo number_format($stats['pending']); ?></h2>
                        <small>ค้างส่ง</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-4 rounded-5 text-center text-white shadow-sm" style="background: linear-gradient(135deg, #E57373, #EF5350);">
                        <h2 class="fw-bold mb-0"><?php echo number_format($stats['late']); ?></h2>
                        <small>ล่าช้า</small>
                    </div>
                </div>
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
                                        <div class="mt-1 text-muted small">
                                            <i class="far fa-eye"></i> <?php echo number_format($doc['view_count'] ?? 0); ?> ครั้ง
                                        </div>
                                    </td>
                                    <td>
                                        <button onclick="showQRModal('<?php echo htmlspecialchars($doc['document_code']); ?>', '<?php echo htmlspecialchars($doc['title']); ?>')" class="btn btn-sm btn-light border rounded-pill shadow-sm text-dark">
                                            <i class="fas fa-qrcode text-success"></i> QR
                                        </button>
                                        <a href="print_cover.php?code=<?php echo $doc['document_code']; ?>" class="btn btn-sm btn-light border rounded-circle shadow-sm ms-1">
                                            <i class="fas fa-print"></i>
                                        </a>
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

<!-- Modal QR Code -->
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

<!-- Modal รายละเอียดเอกสาร -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0 bg-primary text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="fas fa-file-alt me-2"></i>รายละเอียดเอกสาร</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div id="modalLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>

                <div id="modalContent" style="display:none;">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <h4 id="d_title" class="fw-bold text-primary mb-3">...</h4>
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
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>

<style>
.doc-link {
    color: #29B6F6; font-weight: bold; text-decoration: none;
    background: rgba(41, 182, 246, 0.1); padding: 5px 10px; border-radius: 20px; transition: 0.2s;
}
.doc-link:hover { background: #29B6F6; color: white; }
.view-count-badge {
    font-size: 0.85rem; color: #555; background: #eee;
    padding: 5px 10px; border-radius: 15px;
    display: inline-flex; align-items: center; gap: 5px;
}
</style>
