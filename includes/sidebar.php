<div class="sidebar d-flex flex-column">
    <div class="text-center mb-4 pt-2">
        <h4 class="fw-bold text-secondary">
            <i class="fas fa-file-signature text-info me-2"></i>EDE System
        </h4>
    </div>

    <!-- ลิงก์หน้าหลัก (รวมเมนู) -->
    <a href="<?php echo SITE_URL; ?>" class="nav-btn bg-secondary text-white mb-3">
        <i class="fas fa-th-large"></i> เมนูหลัก
    </a>

    <!-- ลิงก์ Dashboard (แยกออกมาแล้ว) -->
    <a href="<?php echo SITE_URL; ?>/dashboard/" class="nav-btn btn-dashboard">
        <i class="fas fa-chart-pie"></i> แดชบอร์ด
    </a>

    <a href="<?php echo SITE_URL; ?>/register/" class="nav-btn btn-register">
        <i class="fas fa-edit"></i> ลงทะเบียน
    </a>

    <a href="<?php echo SITE_URL; ?>/tracking/" class="nav-btn btn-tracking">
        <i class="fas fa-search"></i> ติดตาม
    </a>

    <a href="<?php echo SITE_URL; ?>/report/" class="nav-btn btn-report">
        <i class="fas fa-chart-bar"></i> รายงาน
    </a>

    <a href="<?php echo SITE_URL; ?>/settings/" class="nav-btn btn-settings">
        <i class="fas fa-cog"></i> ตั้งค่า
    </a>
    <a href="<?php echo SITE_URL; ?>/scan-history/" class="nav-btn btn-settings">
        <i class="fas fa-history"></i> ประวัติการสแกน
    </a>
    <a href="<?php echo SITE_URL; ?>/workflow-settings/" class="nav-btn btn-status_settings">
        <i class="fas fa-project-diagram"></i> จัดการ สถานะ
    </a>
</div>