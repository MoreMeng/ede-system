<?php
    $page_title   = "ตั้งค่าระบบ";
    $header_class = "header-settings";
    include 'includes/topbar.php';

    // ดึงข้อมูลผู้ใช้งาน
    $users = [];
    $sql = "SELECT u.*, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.role_id ORDER BY u.user_id ASC";
    $users = CON::selectArrayDB( [], $sql ) ?? [];

    // สร้าง HTML rows
    $userRows = '';
    if ( count( $users ) > 0 ) {
        foreach ( $users as $user ) {
            $fullname = $user['fullname'] ?? '';
            $username = $user['username'] ?? '';
            $department = $user['department'] ?? '-';
            $roleName = $user['role_name'] ?? '';
            $userId = $user['user_id'] ?? 0;

            $badgeColor = 'bg-secondary';
            if ( stripos( $roleName, 'admin' ) !== false ) $badgeColor = 'bg-primary';
            if ( stripos( $roleName, 'staff' ) !== false ) $badgeColor = 'bg-info text-dark';

            $userRows .= "<tr>
                <td class='ps-4'>
                    <div class='fw-bold'>$fullname</div>
                    <div class='small text-muted'><i class='fas fa-user-circle me-1'></i>$username</div>
                </td>
                <td class='text-center text-secondary'>$department</td>
                <td class='text-center'><span class='badge rounded-pill $badgeColor px-3 py-2'>$roleName</span></td>
                <td class='text-center'>
                    <a href='settings_form.php?id=$userId' class='btn btn-sm btn-light rounded-pill border me-1 text-primary' title='แก้ไข'><i class='fas fa-edit'></i></a>
                    <a href='javascript:void(0);' onclick=\"confirmDelete($userId, '$username')\" class='btn btn-sm btn-light rounded-pill border text-danger' title='ลบ'><i class='fas fa-trash-alt'></i></a>
                </td>
            </tr>";
        }
    } else {
        $userRows = '<tr><td colspan="4" class="text-center py-5 text-muted"><i class="fas fa-users-slash fa-3x mb-3 opacity-50"></i><br>ยังไม่มีข้อมูลผู้ใช้งานในระบบ</td></tr>';
    }

?>

<div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold text-secondary mb-0">**⚙️ จัดการผู้ใช้งาน**</h5>
        <a href="settings_form.php" class="btn btn-success rounded-pill px-4 shadow-sm" style="background-color: #00E676; border:none; color:black; font-weight: bold;">
            <i class="fas fa-user-plus me-2"></i>เพิ่มผู้ใช้งานใหม่
        </a>
    </div>

    <div class="table-responsive rounded-4 shadow-sm border">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light text-center border-bottom">
                <tr>
                    <th class="py-3 bg-light text-secondary">ชื่อ-สกุล</th>
                    <th class="py-3 bg-light text-secondary">แผนก</th>
                    <th class="py-3 bg-light text-secondary">สิทธิ์</th>
                    <th class="py-3 bg-light text-secondary" width="150">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php echo $userRows; ?>
            </tbody>
        </table>
    </div>

    <nav class="mt-4 d-flex justify-content-center">
        <ul class="pagination pagination-sm">
            <li class="page-item disabled"><a class="page-link rounded-start-pill border-0 bg-light" href="#">ก่อนหน้า</a></li>
            <li class="page-item active"><a class="page-link border-0" style="background: var(--color-settings);" href="#">1</a></li>
            <li class="page-item disabled"><a class="page-link rounded-end-pill border-0 bg-light" href="#">ถัดไป</a></li>
        </ul>
    </nav>
</div>

<script>
function confirmDelete(userId, username) {
    if (confirm("คุณต้องการลบผู้ใช้ '" + username + "' ใช่หรือไม่?\nการกระทำนี้ไม่สามารถเรียกคืนได้")) {
        window.location.href = 'api/delete_user.php?id=' + userId;
    }
}
</script>
