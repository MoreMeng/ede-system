<?php
// ตั้งค่าสำหรับ topbar
$page_title = "เมนูหลัก (Main Menu)";
$header_class = "header-menu";
?>

<div class="page-content bg-light">
    <div class="container py-4">
        <div class="text-center mb-5">
                    <h2 class="fw-bold text-secondary">ยินดีต้อนรับสู่ระบบ EDE</h2>
                    <p class="text-muted">กรุณาเลือกเมนูที่ต้องการใช้งาน</p>
                </div>

                <div class="row g-4 justify-content-center">

                    <!-- 1. Dashboard -->
                    <div class="col-md-6 col-lg-4">
                        <a href="<?php echo SITE_URL; ?>/dashboard/" class="text-decoration-none">
                            <div class="card menu-card shadow-sm rounded-4 p-4 text-center">
                                <div class="menu-icon-box shadow-sm" style="background: var(--color-dashboard);">
                                    <i class="fas fa-chart-pie"></i>
                                </div>
                                <h4 class="fw-bold text-dark">Dashboard</h4>
                                <p class="text-muted small">ดูภาพรวมสถานะเอกสาร กราฟสรุปผล</p>
                            </div>
                        </a>
                    </div>

                    <!-- 2. ลงทะเบียน -->
                    <div class="col-md-6 col-lg-4">
                        <a href="<?php echo SITE_URL; ?>/register/" class="text-decoration-none">
                            <div class="card menu-card shadow-sm rounded-4 p-4 text-center">
                                <div class="menu-icon-box shadow-sm" style="background: var(--color-register);">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <h4 class="fw-bold text-dark">ลงทะเบียน</h4>
                                <p class="text-muted small">สร้างเอกสารใหม่ ออกเลข และพิมพ์ใบปะหน้า</p>
                            </div>
                        </a>
                    </div>

                    <!-- 3. ติดตามเอกสาร -->
                    <div class="col-md-6 col-lg-4">
                        <a href="<?php echo SITE_URL; ?>/tracking/" class="text-decoration-none">
                            <div class="card menu-card shadow-sm rounded-4 p-4 text-center">
                                <div class="menu-icon-box shadow-sm" style="background: var(--color-tracking);">
                                    <i class="fas fa-search"></i>
                                </div>
                                <h4 class="fw-bold text-dark">ติดตามเอกสาร</h4>
                                <p class="text-muted small">ค้นหา ตรวจสอบสถานะ และดู Timeline</p>
                            </div>
                        </a>
                    </div>

                    <!-- 4. รายงาน -->
                    <div class="col-md-6 col-lg-4">
                        <a href="<?php echo SITE_URL; ?>/report/" class="text-decoration-none">
                            <div class="card menu-card shadow-sm rounded-4 p-4 text-center">
                                <div class="menu-icon-box shadow-sm" style="background: var(--color-report);">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <h4 class="fw-bold text-dark">รายงาน</h4>
                                <p class="text-muted small">สรุปยอดประจำเดือน Export ข้อมูล</p>
                            </div>
                        </a>
                    </div>

                    <!-- 5. ตั้งค่า -->
                    <div class="col-md-6 col-lg-4">
                        <a href="<?php echo SITE_URL; ?>/settings/" class="text-decoration-none">
                            <div class="card menu-card shadow-sm rounded-4 p-4 text-center">
                                <div class="menu-icon-box shadow-sm" style="background: var(--color-settings);">
                                    <i class="fas fa-cogs"></i>
                                </div>
                                <h4 class="fw-bold text-dark">ตั้งค่าระบบ</h4>
                                <p class="text-muted small">จัดการผู้ใช้งาน สิทธิ์ และข้อมูลพื้นฐาน</p>
                            </div>
                        </a>
                    </div>

                    <!-- 6. ประวัติการทำงาน -->
                    <div class="col-md-6 col-lg-4">
                        <a href="<?php echo SITE_URL; ?>/scan-history/" class="text-decoration-none">
                            <div class="card menu-card shadow-sm rounded-4 p-4 text-center">
                                <div class="menu-icon-box shadow-sm" style="background: #546E7A;">
                                    <i class="fas fa-history"></i>
                                </div>
                                <h4 class="fw-bold text-dark">ประวัติการสแกน</h4>
                                <p class="text-muted small">ดูรายการที่คุณเคยสแกนหรืออัปเดต</p>
                            </div>
                        </a>
                    </div>

                    <!-- 7. จัดการสถานะ (Workflow) -->
                    <div class="col-md-6 col-lg-4">
                        <a href="<?php echo SITE_URL; ?>/workflow-settings/" class="text-decoration-none">
                            <div class="card menu-card shadow-sm rounded-4 p-4 text-center">
                                <div class="menu-icon-box shadow-sm" style="background: #ffd740;">
                                    <i class="fas fa-project-diagram"></i>
                                </div>
                                <h4 class="fw-bold text-dark">จัดการสถานะ</h4>
                                <p class="text-muted small">ตั้งค่าหมวดหมู่และลำดับสถานะงาน</p>
                            </div>
                        </a>
                    </div>

                </div>
            </div>
        </div>
</div>

<style>
/* สไตล์พิเศษสำหรับหน้าเมนูหลัก */
.menu-card {
    transition: transform 0.3s, box-shadow 0.3s;
    cursor: pointer;
    border: none;
    height: 100%;
}
.menu-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
}
.menu-icon-box {
    width: 80px; height: 80px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 20px;
    font-size: 2.5rem; color: white;
}
</style>
