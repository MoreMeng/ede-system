<?php
    $page_title   = "รายงาน";
    $header_class = "header-report";
    include 'includes/topbar.php';

    // ดึงข้อมูลรายงาน
    $is_admin = ( stripos( $_SESSION['role'], 'admin' ) !== false );
    $user_dept = $_SESSION['department'] ?? '';

    $start_date = $_GET['start_date'] ?? date( 'Y-m-01' );
    $end_date   = $_GET['end_date'] ?? date( 'Y-m-t' );

    $report_data = [];

    // SQL ดึงข้อมูลแผนก
    $sql_dept = "SELECT DISTINCT department FROM users WHERE department IS NOT NULL AND department != ''";
    if ( !$is_admin && !empty( $user_dept ) ) {
        $sql_dept .= " AND department = ?";
        $dept_params = [$user_dept];
    } else {
        $dept_params = [];
    }
    $departments = CON::selectArrayDB( $dept_params, $sql_dept ) ?? [];

    // สร้าง HTML rows
    $reportRows = '';
    $total_sent = 0;
    $total_received = 0;

    if ( count( $departments ) > 0 ) {
        foreach ( $departments as $dept_row ) {
            $dept = $dept_row['department'] ?? '';

            // A. ส่งออก (Count)
            $sql_sent = "SELECT COUNT(*) as count FROM documents d JOIN users u ON d.created_by = u.user_id WHERE u.department = ? AND DATE(d.created_at) BETWEEN ? AND ?";
            $sent_result = CON::selectArrayDB( [$dept, $start_date, $end_date], $sql_sent );
            $sent_count = ( $sent_result && count( $sent_result ) > 0 ) ? $sent_result[0]['count'] : 0;

            // B. รับเข้า (Count)
            $sql_recv = "SELECT COUNT(*) as count FROM documents d JOIN users u ON d.receiver_name = u.fullname WHERE u.department = ? AND DATE(d.created_at) BETWEEN ? AND ?";
            $recv_result = CON::selectArrayDB( [$dept, $start_date, $end_date], $sql_recv );
            $recv_count = ( $recv_result && count( $recv_result ) > 0 ) ? $recv_result[0]['count'] : 0;

            $total_sent += $sent_count;
            $total_received += $recv_count;

            $reportRows .= "<tr>
                <td class='text-start ps-5 fw-bold text-secondary'>$dept</td>
                <td class='text-success fw-bold' style='font-size: 1.1rem;'>" . number_format( $sent_count ) . "</td>
                <td class='text-primary fw-bold' style='font-size: 1.1rem;'>" . number_format( $recv_count ) . "</td>
                <td class='text-secondary fw-bold'>" . number_format( $sent_count + $recv_count ) . "</td>
            </tr>";
        }
        $reportRows .= "<tr class='table-secondary fw-bold'>
            <td class='text-end pe-3'>รวมทั้งสิ้น</td>
            <td class='text-success'>" . number_format( $total_sent ) . "</td>
            <td class='text-primary'>" . number_format( $total_received ) . "</td>
            <td>" . number_format( $total_sent + $total_received ) . "</td>
        </tr>";
    } else {
        $reportRows = '<tr><td colspan="4" class="py-5 text-muted">ไม่พบข้อมูล</td></tr>';
    }

?>

<div class="page-content">
    <h5 class="mb-4 fw-bold text-secondary">**📊 สรุปการรับ-ส่งเอกสารตามหน่วยงาน** <?php echo $is_admin ? '(ทั้งหมด)' : "(เฉพาะแผนก $user_dept)"; ?></h5>

    <form method="GET" action="index.php" class="row justify-content-center mb-5">
        <input type="hidden" name="dev" value="report">
        <div class="col-md-9 text-center">
            <div class="d-flex align-items-center justify-content-center gap-2 bg-light p-3 rounded-pill shadow-sm border">
                <span class="fw-bold text-secondary"><i class="far fa-calendar-alt"></i> ช่วงเวลา:</span>
                <input type="date" name="start_date" class="form-control rounded-pill border-0 custom-input py-2" style="max-width: 160px;" value="<?php echo $start_date; ?>">
                <span class="text-muted">ถึง</span>
                <input type="date" name="end_date" class="form-control rounded-pill border-0 custom-input py-2" style="max-width: 160px;" value="<?php echo $end_date; ?>">
                <button type="submit" class="btn btn-danger rounded-circle shadow-sm" style="width: 40px; height: 40px;"><i class="fas fa-search"></i></button>
                <a href="index.php?dev=report" class="btn btn-secondary rounded-circle shadow-sm" style="width: 40px; height: 40px; display:flex; align-items:center; justify-content:center;"><i class="fas fa-sync-alt"></i></a>
            </div>
        </div>
    </form>

    <div class="table-responsive rounded-4 shadow-sm border mx-auto bg-white" style="max-width: 900px;">
        <table id="reportTable" class="table table-hover mb-0 text-center align-middle">
            <thead class="table-light">
                <tr>
                    <th class="py-3 bg-light text-secondary">แผนก / หน่วยงาน</th>
                    <th class="py-3 bg-light text-success">📤 ส่งออก (รายการ)</th>
                    <th class="py-3 bg-light text-primary">📥 รับเข้า (รายการ)</th>
                    <th class="py-3 bg-light text-secondary">รวมทั้งหมด</th>
                </tr>
            </thead>
            <tbody>
                <?php echo $reportRows; ?>
            </tbody>
        </table>
    </div>

    <div class="text-end mt-4 mx-auto" style="max-width: 900px;">
        <button onclick="window.print()" class="btn btn-outline-dark border-0 fw-bold rounded-pill px-4"><i class="fas fa-print me-2"></i>พิมพ์</button>
        <button onclick="exportTableToExcel('reportTable', 'Report')" class="btn btn-success border-0 fw-bold rounded-pill px-4 ms-2" style="background-color: #1D6F42;"><i class="fas fa-file-excel me-2"></i>Export</button>
    </div>
</div>

<script>
    function exportTableToExcel(tableID, filename = '') {
        const table = document.getElementById(tableID);
        let html = "<table>";
        for (let row of table.rows) {
            html += "<tr>";
            for (let cell of row.cells) {
                html += "<td>" + cell.textContent + "</td>";
            }
            html += "</tr>";
        }
        html += "</table>";

        const link = document.createElement("a");
        const blob = new Blob([html], { type: "application/vnd.ms-excel" });
        link.href = URL.createObjectURL(blob);
        link.download = filename + ".xls";
        link.click();
    }
</script>
