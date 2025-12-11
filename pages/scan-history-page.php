<?php
    $page_title   = "ประวัติการทำงานของคุณ";
    $header_class = "header-dashboard";
    include 'includes/topbar.php';

    $history = [];

    $sql     = "SELECT l.*, d.title, d.document_code FROM document_status_log l JOIN documents d ON l.document_id = d.document_id WHERE l.action_by = ? ORDER BY l.action_time DESC LIMIT 50";
    $history = CON::selectArrayDB( [$_SESSION['user_id']], $sql ) ?? [];

    // สร้าง HTML rows
    $historyRows = '';
    if ( count( $history ) > 0 ) {
        foreach ( $history as $row ) {
            $time = date( 'd/m/Y H:i', strtotime( $row['action_time'] ) );
            $code = $row['document_code'] ?? '';
            $title = $row['title'] ?? '';
            $status = $row['status'] ?? '';
            $ip = $row['ip_address'] ?? '';
            $historyRows .= "<tr>
                <td class='ps-4 text-muted small'>$time</td>
                <td><span class='fw-bold text-primary'>$code</span><br><small>$title</small></td>
                <td><span class='badge bg-secondary rounded-pill'>$status</span></td>
                <td class='text-muted small'><i class='fas fa-desktop me-1'></i>$ip</td>
            </tr>";
        }
    } else {
        $historyRows = '<tr><td colspan="4" class="text-center py-5 text-muted">ยังไม่พบประวัติการทำงานของคุณ</td></tr>';
    }

?>

<div class="page-content">
    <h5 class="mb-4 fw-bold text-secondary">**🕒 รายการที่คุณเคยสแกน/อัปเดต**</h5>

    <div class="table-responsive rounded-4 shadow-sm border">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th class="py-3 ps-4">เวลา</th>
                    <th class="py-3">เอกสาร</th>
                    <th class="py-3">การดำเนินการ</th>
                    <th class="py-3">อุปกรณ์</th>
                </tr>
            </thead>
            <tbody>
                <?php echo $historyRows; ?>
            </tbody>
        </table>
    </div>
</div>