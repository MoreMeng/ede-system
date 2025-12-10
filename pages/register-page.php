<?php
// Register Page - Logic Section

// ดึงข้อมูลประเภทเอกสารสำหรับ Dropdown (Document Types)
$types = [];
try {
    if(isset($pdo)) {
        $stmt = $pdo->query("SELECT * FROM document_type");
        $types = $stmt->fetchAll();
    }
} catch (PDOException $e) {}

// ตั้งค่าสำหรับ topbar
$page_title = "ลงทะเบียน";
$header_class = "header-register";
?>

<div class="page-content">
            <h5 class="mb-4 fw-bold text-secondary">**ลงทะเบียนเอกสารใหม่**</h5>

            <form action="api/save_document.php" method="POST" class="mx-auto" style="max-width: 900px;" id="registerForm">
                <input type="hidden" name="created_by" value="<?php echo $_SESSION['user_id'] ?? 1; ?>">
                <input type="hidden" name="current_status" id="initialStatusInput" value="">
                <input type="hidden" name="workflow_id" id="workflowIdInput" value="">

                <!-- 1. ชื่อเรื่อง -->
                <div class="row mb-4 align-items-center">
                    <div class="col-md-3 text-md-end">
                        <label class="fw-bold text-secondary">ชื่อเรื่อง</label> <span class="text-danger">*</span>
                    </div>
                    <div class="col-md-9">
                        <input type="text" name="title" required class="form-control custom-input" placeholder="ระบุชื่อเรื่องเอกสาร...">
                    </div>
                </div>

                <!-- 2. เลขที่อ้างอิง -->
                <div class="row mb-4 align-items-center">
                    <div class="col-md-3 text-md-end">
                        <label class="fw-bold text-secondary">เลขที่เอกสาร</label>
                    </div>
                    <div class="col-md-9">
                        <input type="text" name="reference_no" class="form-control custom-input" placeholder="เช่น ศธ 0512/123 (ถ้ามี)">
                    </div>
                </div>

                <!-- 3. ประเภทเอกสาร -->
                <div class="row mb-4 align-items-center">
                    <div class="col-md-3 text-md-end">
                        <label class="fw-bold text-secondary">ประเภท</label>
                    </div>
                    <div class="col-md-9">
                        <select name="type_id" class="form-select custom-input">
                            <?php if (!empty($types)): foreach ($types as $type): ?>
                                <option value="<?php echo $type['type_id']; ?>"><?php echo htmlspecialchars($type['type_name']); ?></option>
                            <?php endforeach; else: ?>
                                <option value="1">หนังสือภายนอก</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <!-- 4. ผู้ส่ง -->
                <div class="row mb-4 align-items-center">
                    <div class="col-md-3 text-md-end">
                        <label class="fw-bold text-secondary">ผู้ส่ง</label> <span class="text-danger">*</span>
                    </div>
                    <div class="col-md-9">
                        <input type="text" name="sender_name" required class="form-control custom-input"
                               value="<?php echo htmlspecialchars($_SESSION['fullname'] ?? ''); ?>">
                    </div>
                </div>

                <!-- 5. หมวดหมู่สถานะ -->
                <div class="row mb-4 align-items-center bg-light p-3 rounded border border-secondary border-opacity-25 mx-0">
                    <div class="col-md-3 text-md-end">
                        <label class="fw-bold text-primary"><i class="fas fa-project-diagram me-1"></i> หมวดหมู่สถานะ</label> <span class="text-danger">*</span>
                    </div>
                    <div class="col-md-9">
                        <select id="workflowSelect" class="form-select custom-input border-primary" required>
                            <option value="" selected disabled>-- กรุณาเลือกเส้นทางการทำงาน --</option>
                            <!-- JS จะโหลดข้อมูลมาใส่ที่นี่ -->
                        </select>
                        <small class="text-muted mt-2 d-block" id="statusPreview">
                            <i class="fas fa-arrow-right"></i> สถานะเริ่มต้น: <span class="text-secondary">-</span>
                        </small>
                        <div class="form-text text-danger mt-1 small">
                            * ระบบจะแสดงเฉพาะหมวดหมู่ที่คุณเป็นคนสร้างเท่านั้น
                        </div>
                    </div>
                </div>

                <input type="hidden" name="receiver_name" value="-">

                <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                    <button type="reset" class="btn btn-danger rounded-pill px-4 me-2">ยกเลิก</button>
                    <button type="submit" id="btnSubmit" class="btn btn-success rounded-pill px-5 btn-disabled-custom">
                        <i class="fas fa-save me-1"></i> บันทึก
                    </button>
                </div>
            </form>
        </div>
</div>

<style>
/* CSS ปรับแต่งสถานะปุ่ม */
.btn-disabled-custom {
    background-color: #e9ecef !important;
    color: #adb5bd !important;
    border: 1px solid #dee2e6 !important;
    cursor: not-allowed;
    pointer-events: none;
}
</style>
