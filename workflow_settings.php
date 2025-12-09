<?php 
session_start();
require_once 'config/db.php'; // เชื่อมต่อ DB เพื่อตรวจสอบ Session หรือดึงข้อมูล user
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ตั้งค่าหมวดหมู่และสถานะเอกสาร - EDE System</title>
    
    <!-- Bootstrap 5 & FontAwesome (ตามไฟล์หลัก) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- CSS หลักของระบบ -->
    <link href="assets/css/style.css" rel="stylesheet"> 

    <!-- CSS เฉพาะหน้านี้ (Add-on) -->
    <style>
        /* ปรับพื้นหลังให้กลมกลืน */
        body { background-color: #f8f9fa; }
        
        /* Card Workflow สไตล์ */
        .workflow-card { 
            border-left: 5px solid #0d6efd; 
            transition: all 0.2s ease-in-out; 
            border-radius: 8px; 
        }
        .workflow-card:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 10px 20px rgba(0,0,0,0.1); 
        }
        
        /* Status Badge */
        .status-badge { 
            font-size: 0.85rem; 
            padding: 6px 15px; 
            border-radius: 50px; 
            display: inline-block; 
            min-width: 100px; 
            text-align: center;
            font-weight: 500;
        }
        
        /* ปุ่มลบเล็กๆ */
        .btn-del-status { font-size: 0.7rem; opacity: 0.6; transition: 0.2s; text-decoration: none; }
        .btn-del-status:hover { opacity: 1; text-decoration: underline; }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- 1. เรียกใช้ Sidebar หลัก -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- 2. ส่วนเนื้อหา (Content Wrapper) -->
    <div class="content-wrapper p-4 w-100">
        
        <!-- Header ส่วนหัว (สไตล์เดียวกับ status_settings.php) -->
        <h3 class="mb-4 text-primary">
            <i class="fas fa-project-diagram"></i> ตั้งค่าหมวดหมู่สถานะ (Workflows)
        </h3>
        
        <div class="alert alert-light border shadow-sm mb-4">
            <i class="fas fa-info-circle text-info"></i> 
            <strong>คำแนะนำ:</strong> ใช้หน้านี้เพื่อกำหนดลำดับขั้นตอนการทำงานของเอกสารแต่ละประเภท (เช่น งานการเงิน: รับเรื่อง -> ตรวจสอบ -> อนุมัติ)
        </div>

        <div class="row mb-3">
            <div class="col-12 text-end">
                <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="fas fa-plus-circle"></i> เพิ่มหมวดหมู่ใหม่
                </button>
            </div>
        </div>

        <!-- Container สำหรับแสดงรายการ Workflow -->
        <div id="workflowContainer">
            <div class="text-center py-5 text-muted bg-white rounded shadow-sm border">
                <i class="fas fa-spinner fa-spin fa-2x mb-3"></i><br>กำลังโหลดข้อมูล...
            </div>
        </div>

    </div>
</div>

<!-- ================= MODALS (ส่วนป๊อปอัพ) ================= -->

<!-- Modal เพิ่มหมวดหมู่ -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-folder-plus"></i> เพิ่มหมวดหมู่ใหม่</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <label class="form-label fw-bold">ชื่อหมวดหมู่</label>
                <input type="text" id="newCatName" class="form-control form-control-lg" placeholder="เช่น งานการเงิน, งานพัสดุ">
                <div class="form-text text-muted">สร้างหมวดหมู่เพื่อจัดกลุ่มสถานะการทำงานที่เกี่ยวข้องกัน</div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary px-4" onclick="addCategory()">บันทึก</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal เพิ่มสถานะในหมวดหมู่ -->
<div class="modal fade" id="addStatusModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-tag"></i> เพิ่มสถานะในหมวด: <span id="modalCatName" class="fw-bold"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="targetCatId">
                <div class="mb-3">
                    <label class="form-label fw-bold">ชื่อสถานะ</label>
                    <input type="text" id="newStatusName" class="form-control" placeholder="เช่น รับเอกสาร, ตรวจสอบแล้ว">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">สีป้ายสถานะ</label>
                    <div class="d-flex gap-2 flex-wrap justify-content-center">
                        <!-- Custom Color Selection UI -->
                        <input type="radio" class="btn-check" name="statusColor" id="colorSec" value="secondary" checked>
                        <label class="btn btn-outline-secondary rounded-pill px-3" for="colorSec">ทั่วไป</label>

                        <input type="radio" class="btn-check" name="statusColor" id="colorInfo" value="info">
                        <label class="btn btn-outline-info rounded-pill px-3" for="colorInfo">ดำเนินการ</label>

                        <input type="radio" class="btn-check" name="statusColor" id="colorWarn" value="warning">
                        <label class="btn btn-outline-warning rounded-pill px-3" for="colorWarn">รอ/ระวัง</label>

                        <input type="radio" class="btn-check" name="statusColor" id="colorPrim" value="primary">
                        <label class="btn btn-outline-primary rounded-pill px-3" for="colorPrim">ส่งต่อ</label>

                        <input type="radio" class="btn-check" name="statusColor" id="colorSucc" value="success">
                        <label class="btn btn-outline-success rounded-pill px-3" for="colorSucc">สำเร็จ</label>

                        <input type="radio" class="btn-check" name="statusColor" id="colorDang" value="danger">
                        <label class="btn btn-outline-danger rounded-pill px-3" for="colorDang">ยกเลิก</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-info text-white px-4" onclick="addStatus()">เพิ่มสถานะ</button>
            </div>
        </div>
    </div>
</div>

<!-- Script การทำงาน -->
<script>
    const API_URL = 'api/manage_workflow.php'; // ตรวจสอบให้แน่ใจว่าไฟล์นี้มีอยู่จริงใน folder api
    let workflowData = [];

    document.addEventListener('DOMContentLoaded', loadWorkflows);

    function loadWorkflows() {
        fetch(`${API_URL}?action=list`)
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    workflowData = res.data;
                    renderWorkflows();
                }
            })
            .catch(err => {
                document.getElementById('workflowContainer').innerHTML = 
                    '<div class="alert alert-danger text-center"><i class="fas fa-exclamation-triangle"></i> ไม่สามารถโหลดข้อมูลได้ (ตรวจสอบไฟล์ api/manage_workflow.php)</div>';
            });
    }

    function renderWorkflows() {
        const container = document.getElementById('workflowContainer');
        container.innerHTML = '';

        if (workflowData.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5 bg-white rounded shadow-sm border">
                    <i class="fas fa-folder-open text-muted fa-3x mb-3"></i>
                    <h5 class="text-muted">ยังไม่มีข้อมูล Workflow</h5>
                    <p class="text-muted small">เริ่มสร้างหมวดหมู่แรกเพื่อกำหนดขั้นตอนการทำงาน</p>
                </div>`;
            return;
        }

        workflowData.forEach(cat => {
            let statusHtml = '';
            if (cat.statuses.length > 0) {
                statusHtml = cat.statuses.map((st, index) => `
                    <div class="d-flex flex-column align-items-center mb-2">
                        <span class="badge bg-${st.color} status-badge shadow-sm">${st.name}</span>
                        <div class="mt-1">
                            <button class="btn-del-status btn btn-link text-danger p-0" onclick="deleteStatus('${cat.id}', '${st.id}')">
                                <i class="fas fa-times"></i> ลบ
                            </button>
                        </div>
                    </div>
                    ${index < cat.statuses.length - 1 ? '<div class="mx-2 mb-4 text-muted"><i class="fas fa-chevron-right"></i></div>' : ''}
                `).join('');
            } else {
                statusHtml = '<div class="text-muted w-100 text-center py-3 bg-light rounded"><small>ยังไม่มีสถานะในหมวดนี้ กดปุ่ม "เพิ่มสถานะ" เพื่อเริ่มสร้าง Flow</small></div>';
            }

            const card = `
                <div class="card workflow-card mb-4 shadow-sm bg-white">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width:40px; height:40px;">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <div>
                                <h5 class="m-0 fw-bold text-dark">${cat.name}</h5>
                                <small class="text-muted" style="font-size: 0.8rem;">ID: ${cat.id}</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary btn-sm" onclick="openAddStatusModal('${cat.id}', '${cat.name}')">
                                <i class="fas fa-plus"></i> เพิ่มสถานะ
                            </button>
                            <button class="btn btn-outline-danger btn-sm" onclick="deleteCategory('${cat.id}')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body bg-light">
                        <div class="d-flex align-items-center flex-wrap p-3 bg-white rounded border">
                            ${statusHtml}
                        </div>
                    </div>
                </div>
            `;
            container.innerHTML += card;
        });
    }

    function addCategory() {
        const name = document.getElementById('newCatName').value;
        if (!name) return alert('กรุณาใส่ชื่อหมวดหมู่');

        const formData = new FormData();
        formData.append('category_name', name);

        fetch(`${API_URL}?action=add_category`, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('addCategoryModal')).hide();
                    document.getElementById('newCatName').value = '';
                    loadWorkflows();
                }
            });
    }

    function openAddStatusModal(catId, catName) {
        document.getElementById('targetCatId').value = catId;
        document.getElementById('modalCatName').innerText = catName;
        document.getElementById('colorSec').checked = true; // Reset radio
        new bootstrap.Modal(document.getElementById('addStatusModal')).show();
    }

    function addStatus() {
        const catId = document.getElementById('targetCatId').value;
        const name = document.getElementById('newStatusName').value;
        const color = document.querySelector('input[name="statusColor"]:checked').value;

        if (!name) return alert('กรุณาใส่ชื่อสถานะ');

        const formData = new FormData();
        formData.append('category_id', catId);
        formData.append('status_name', name);
        formData.append('color_class', color);

        fetch(`${API_URL}?action=add_status`, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('addStatusModal')).hide();
                    document.getElementById('newStatusName').value = '';
                    loadWorkflows();
                }
            });
    }

    function deleteCategory(id) {
        if (!confirm('ยืนยันการลบหมวดหมู่นี้? ข้อมูลสถานะทั้งหมดในหมวดนี้จะหายไปด้วย')) return;
        const formData = new FormData();
        formData.append('id', id);
        fetch(`${API_URL}?action=delete_category`, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => { if(res.success) loadWorkflows(); });
    }

    function deleteStatus(catId, stId) {
        if (!confirm('ต้องการลบสถานะนี้ออกจากลำดับขั้นตอน?')) return;
        const formData = new FormData();
        formData.append('category_id', catId);
        formData.append('status_id', stId);
        fetch(`${API_URL}?action=delete_status`, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => { if(res.success) loadWorkflows(); });
    }
</script>

</body>
</html>