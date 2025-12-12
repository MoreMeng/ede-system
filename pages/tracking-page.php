<?php
    $page_title   = "ติดตามเอกสาร";
    $header_class = "header-tracking";
    include 'includes/topbar.php';

    $search_query = $_GET['search'] ?? '';
    $doc_data = null;
    $logs = [];

    // ตรวจสอบสิทธิ์
    $is_admin = ( stripos( $_SESSION['role'], 'admin' ) !== false );
    $user_id = $_SESSION['user_id'];

    // ฟังก์ชันคำนวณเวลา
    function time_elapsed_string( $datetime, $full = false ) {
        $now = new DateTime;
        $ago = new DateTime( $datetime );
        $diff = $now->diff( $ago );
        $string = array( 'y' => 'ปี', 'm' => 'เดือน', 'd' => 'วัน', 'h' => 'ชั่วโมง', 'i' => 'นาที' );
        foreach ( $string as $k => &$v ) {
            if ( $diff->$k ) $v = $diff->$k . ' ' . $v; else unset( $string[$k] );
        }
        if ( !$full ) $string = array_slice( $string, 0, 2 );
        return $string ? implode( ', ', $string ) : 'เมื่อสักครู่';
    }

    // ฟังก์ชันสีสถานะ
    function getStatusColor( $status ) {
        switch ( $status ) {
            case 'Received': return 'success';
            case 'Sent': return 'warning';
            case 'Registered': return 'info';
            case 'Late': return 'danger';
            default: return 'secondary';
        }
    }

    // ค้นหาเอกสาร
    if ( !empty( $search_query ) ) {
        $sql = "SELECT d.*, dt.type_name, u.fullname as creator_name FROM documents d LEFT JOIN document_type dt ON d.type_id = dt.type_id LEFT JOIN users u ON d.created_by = u.user_id WHERE (d.document_code = ? OR d.title LIKE ?)";

        if ( !$is_admin ) {
            $search_params = [$search_query, "%$search_query%", $user_id];
            $sql .= " AND d.created_by = ?";
        } else {
            $search_params = [$search_query, "%$search_query%"];
        }

        $doc_result = CON::selectArrayDB( $search_params, $sql );
        $doc_data = ( $doc_result && count( $doc_result ) > 0 ) ? $doc_result[0] : null;

        if ( $doc_data ) {
            $sql_log = "SELECT l.*, u.fullname as db_user_name FROM document_status_log l LEFT JOIN users u ON l.action_by = u.user_id WHERE l.document_id = ? ORDER BY l.action_time DESC";
            $logs = CON::selectArrayDB( [$doc_data['document_id']], $sql_log ) ?? [];
        }
    }

    // สร้าง HTML timeline
    $timelineHtml = '';
    if ( count( $logs ) > 0 ) {
        foreach ( $logs as $index => $log ) {
            $actor_name = !empty( $log['actor_name_snapshot'] ) ? $log['actor_name_snapshot'] : ( $log['db_user_name'] ?? 'Unknown' );
            $actor_pic = $log['actor_pic_snapshot'] ?? '';
            $status = $log['status'] ?? '';
            $action_time = date( 'd/m/Y H:i', strtotime( $log['action_time'] ) );
            $device_info = $log['device_info'] ?? '';
            $active_class = ( $index === 0 ) ? 'active' : '';

            $pic_html = $actor_pic
                ? "<img src='$actor_pic' class='rounded-circle me-2 border' width='30' height='30'>"
                : "<div class='bg-secondary text-white rounded-circle me-2 d-flex align-items-center justify-content-center' style='width:30px;height:30px;font-size:12px;'><i class='fas fa-user'></i></div>";

            $device_html = !empty( $device_info )
                ? "<small class='text-muted' style='font-size: 0.7rem;'><i class='fas fa-mobile-alt me-1'></i>$device_info</small>"
                : '';

            $timelineHtml .= "<div class='timeline-item'>
                <div class='timeline-dot $active_class'></div>
                <div class='ps-3'>
                    <div class='d-flex justify-content-between align-items-start mb-1'>
                        <h6 class='fw-bold text-dark mb-0'>$status</h6>
                        <span class='badge bg-light text-secondary border'><i class='far fa-clock me-1'></i>$action_time</span>
                    </div>
                    <div class='d-flex align-items-center mt-2'>
                        $pic_html
                        <div>
                            <p class='text-muted small mb-0'>ดำเนินการโดย: <strong>$actor_name</strong></p>
                            $device_html
                        </div>
                    </div>
                </div>
            </div>";
        }
    } else if ( $doc_data ) {
        $timelineHtml = '<p class="text-muted ps-3">ยังไม่มีประวัติ</p>';
    }

?>

<div class="page-content">
    <h5 class="mb-4 fw-bold text-secondary text-center">**🔍 ค้นหาและติดตามเอกสาร**</h5>

    <?php if ( !$is_admin ): ?>
        <div class="text-center text-muted mb-3 small"><i class="fas fa-info-circle"></i> คุณสามารถค้นหาได้เฉพาะเอกสารที่คุณเป็นผู้สร้างเท่านั้น</div>
    <?php endif; ?>

    <form method="GET" action="<?php echo SITE_URL; ?>/index.php" class="row justify-content-center mb-5">
        <input type="hidden" name="dev" value="tracking">
        <div class="col-md-8">
            <div class="input-group shadow-sm rounded-pill overflow-hidden bg-white border p-1">
                <span class="input-group-text border-0 bg-white ps-3 text-muted"><i class="fas fa-search"></i></span>
                <input type="text" name="search" class="form-control border-0 shadow-none" placeholder="ระบุเลขที่ทะเบียน หรือ ชื่อเรื่อง..." value="<?php echo htmlspecialchars( $search_query ); ?>">
                <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold" style="background-color: var(--color-tracking); border:none;">ค้นหา</button>
            </div>
        </div>
    </form>

    <?php if ( $doc_data ): ?>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mx-auto animate-fade-in" style="max-width: 900px;">
            <div class="card-header border-0 p-4 d-flex justify-content-between align-items-center" style="background-color: rgba(102, 187, 106, 0.1);">
                <div>
                    <h5 class="mb-1 text-success fw-bold"><i class="far fa-file-alt me-2"></i><?php echo htmlspecialchars( $doc_data['title'] ?? '' ); ?></h5>
                    <small class="text-muted">เลขทะเบียน: <strong><?php echo htmlspecialchars( $doc_data['document_code'] ?? '' ); ?></strong> | ระยะเวลา: <strong><?php echo time_elapsed_string( $doc_data['created_at'] ?? '' ); ?></strong></small>
                </div>
                <span class="badge rounded-pill bg-<?php echo getStatusColor( $doc_data['current_status'] ?? '' ); ?> text-uppercase px-3 py-2"><?php echo htmlspecialchars( $doc_data['current_status'] ?? '' ); ?></span>
            </div>
            <div class="card-body p-4">
                <div class="timeline">
                    <?php echo $timelineHtml; ?>
                </div>
            </div>
        </div>
    <?php elseif ( !empty( $search_query ) ): ?>
        <div class="text-center py-5">
            <h5 class="text-secondary">ไม่พบเอกสาร</h5>
            <p class="text-muted small">คุณอาจไม่มีสิทธิ์เข้าถึงเอกสารนี้ หรือรหัสผิด</p>
        </div>
    <?php endif; ?>
</div>
