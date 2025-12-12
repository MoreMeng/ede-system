<?php
    // ส่วนหัวของหน้าเว็บ (ปรับตามโครงสร้างของคุณ)
    $page_title   = "จัดการสถานะ";
    $header_class = "header-status";
    include 'includes/topbar.php'; // ตรวจสอบ path ให้ถูกต้อง
?>

<div class="page-content container-fluid">
    <h3 class="mb-4 text-primary">
        <i class="fas fa-project-diagram"></i> ตั้งค่าหมวดหมู่สถานะ (Workflows)
    </h3>

    <div class="alert alert-light border shadow-sm mb-4">
        <div class="d-flex">
            <div class="me-3"><i class="fas fa-info-circle text-info fa-2x"></i></div>
            <div>
                <strong>คำแนะนำ:</strong>
                <ul class="mb-0 ps-3">
                    <li>คุณสามารถ <strong>ลากที่ไอคอน <i class="fas fa-grip-vertical"></i></strong> เพื่อจัดลำดับความสำคัญของสถานะได้</li>
                    <li>หมวดหมู่ <strong>"สถานะพื้นฐาน"</strong> จะไม่สามารถลบหรือแก้ไขชื่อได้ แต่สามารถเพิ่มสถานะเพิ่มเติมเข้าไปได้</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12 text-end">
            <button class="btn btn-primary shadow-sm" onclick="openAddCategoryModal()">
                <i class="fas fa-plus-circle"></i> เพิ่มหมวดหมู่ใหม่
            </button>
        </div>
    </div>

    <div id="workflowContainer">
        <div class="text-center py-5 text-muted">
            <i class="fas fa-spinner fa-spin fa-2x mb-2"></i><br>กำลังโหลดข้อมูล...
        </div>
    </div>
</div>

<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white" id="catModalHeader">
                <h5 class="modal-title" id="catModalTitle">เพิ่มหมวดหมู่ใหม่</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="cat_id"> <label class="form-label fw-bold">ชื่อหมวดหมู่</label>
                <input type="text" id="cat_name" class="form-control form-control-lg" placeholder="เช่น งานฝ่ายบุคคล">
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary px-4" onclick="saveCategory()">บันทึก</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="stModalTitle">จัดการสถานะ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="st_catId">
                <input type="hidden" id="st_id">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">ชื่อสถานะ</label>
                    <input type="text" id="st_name" class="form-control" placeholder="เช่น รอตรวจสอบ">
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">สีป้ายสถานะ</label>
                    <div class="d-flex align-items-center">
                        <input type="color" id="st_color" class="form-control form-control-color" value="#6c757d" title="เลือกสี">
                        <span class="ms-2 text-muted small">คลิกเพื่อเลือกสี</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-info text-white px-4" onclick="saveStatus()">บันทึก</button>
            </div>
        </div>
    </div>
</div>

<style>
    .workflow-card {
        border-radius: 10px;
        border: 1px solid #e0e0e0;
        transition: box-shadow 0.2s;
    }
    .workflow-card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }
    .status-list-container {
        min-height: 50px;
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 10px;
    }
    .status-item { 
        background: white; 
        border: 1px solid #dee2e6; 
        border-radius: 6px; 
        padding: 8px 12px; 
        margin-bottom: 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: transform 0.1s, box-shadow 0.1s;
    }
    .status-item:last-child { margin-bottom: 0; }
    .status-item.sortable-ghost { 
        opacity: 0.4; 
        background: #e9ecef; 
        border: 1px dashed #adb5bd;
    }
    .status-item.sortable-drag {
        cursor: grabbing;
    }
    .handle { 
        cursor: grab; 
        color: #adb5bd; 
        padding: 0 10px;
    }
    .handle:hover { color: #6c757d; }
    .status-badge { font-weight: 500; font-size: 0.9rem; min-width: 100px; text-align: center;}
    .action-btn { 
        opacity: 0.4; 
        transition: 0.2s; 
        cursor: pointer;
        padding: 4px;
    }
    .status-item:hover .action-btn { opacity: 1; }
</style>

<script>
    // URL API ให้ชี้ไปที่ไฟล์ backend ของคุณ
    const API_URL = '../api/manage_workflow.php';
    let workflowData = [];

    document.addEventListener('DOMContentLoaded', loadWorkflows);

    function loadWorkflows() {
        fetch(`${API_URL}?action=list`)
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    workflowData = res.data;
                    renderWorkflows();
                } else {
                    console.error('Error loading data:', res);
                }
            })
            .catch(err => {
                console.error('Fetch error:', err);
                document.getElementById('workflowContainer').innerHTML = 
                    '<div class="alert alert-danger">ไม่สามารถเชื่อมต่อ API ได้</div>';
            });
    }

    function renderWorkflows() {
        const container = document.getElementById('workflowContainer');
        container.innerHTML = '';

        if (workflowData.length === 0) {
            container.innerHTML = '<div class="text-center py-5 text-muted">ยังไม่มีข้อมูล Workflow</div>';
            return;
        }

        workflowData.forEach(cat => {
            const isDefault = (cat.id === 'cat_default');
            let statusHtml = '';

            // Render รายการสถานะ
            if (cat.statuses && cat.statuses.length > 0) {
                statusHtml = cat.statuses.map(st => {
                    const isSystemStatus = st.id.startsWith('st_def_');
                    // ปุ่มลบ/แก้ไข จะซ่อนถ้าเป็นสถานะของระบบ (ตาม logic Backend)
                    const editBtn = `<i class="fas fa-pen text-warning action-btn ms-2" title="แก้ไข" onclick="openEditStatus('${cat.id}', '${st.id}', '${st.name}', '${st.color}')"></i>`;
                    const delBtn = isSystemStatus ? '' : `<i class="fas fa-trash-alt text-danger action-btn ms-2" title="ลบ" onclick="deleteStatus('${cat.id}', '${st.id}')"></i>`;
                    
                    let badgeHtml = '';
                    if (st.color && st.color.startsWith('#')) {
                        badgeHtml = `<span class="badge status-badge rounded-pill me-2" style="background-color: ${st.color}; color: #fff;">${st.name}</span>`;
                    } else {
                        badgeHtml = `<span class="badge bg-${st.color} status-badge rounded-pill me-2">${st.name}</span>`;
                    }

                    return `
                    <div class="status-item shadow-sm" data-id="${st.id}">
                        <div class="d-flex align-items-center">
                            <div class="handle"><i class="fas fa-grip-vertical"></i></div>
                            ${badgeHtml}
                        </div>
                        <div>
                            ${editBtn}
                            ${delBtn}
                        </div>
                    </div>`;
                }).join('');
            } else {
                statusHtml = `<div class="text-center text-muted small py-2">ยังไม่มีสถานะ ลากหรือกดเพิ่มเพื่อสร้าง</div>`;
            }

            // ปุ่มจัดการหมวดหมู่ (ซ่อนปุ่มลบ/แก้ไขชื่อ ถ้าเป็น Default)
            let catActions = '';
            if (!isDefault) {
                catActions = `
                    <button class="btn btn-sm btn-outline-secondary me-1" onclick="openEditCategory('${cat.id}', '${cat.name}')">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteCategory('${cat.id}')">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
            } else {
                catActions = `<span class="badge bg-secondary"><i class="fas fa-lock"></i> System Default</span>`;
            }

            const card = `
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card workflow-card h-100 bg-white">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom-0">
                            <h5 class="mb-0 fw-bold text-dark text-truncate" title="${cat.name}">
                                ${cat.name}
                            </h5>
                            <div class="d-flex align-items-center">
                                ${catActions}
                            </div>
                        </div>
                        <div class="card-body pt-0">
                             <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">รายการสถานะ (${cat.statuses.length})</small>
                                <button class="btn btn-sm btn-primary rounded-pill px-3" onclick="openAddStatus('${cat.id}')">
                                    <i class="fas fa-plus"></i> เพิ่ม
                                </button>
                            </div>
                            <div class="status-list-container sortable-list" data-cat-id="${cat.id}">
                                ${statusHtml}
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // สร้างเป็น Row ใหม่ หรือ Append ลง Container
            // เพื่อความสวยงามเราจะใช้ Grid System
            if(container.innerHTML === '') container.innerHTML = '<div class="row" id="cardRow"></div>';
            document.getElementById('cardRow').innerHTML += card;
        });

        initSortable();
    }

    function initSortable() {
        const lists = document.querySelectorAll('.sortable-list');
        lists.forEach(list => {
            new Sortable(list, {
                group: 'shared', // ถ้าอยากให้ข้ามหมวดได้ใช้ชื่อเดียวกัน (แต่ backend ต้องรองรับย้ายหมวดด้วย ซึ่งตอนนี้ backend คุณรองรับแค่ sort ในหมวดเดิม แนะนำอย่าเพิ่งให้ข้ามหมวด)
                animation: 150,
                handle: '.handle',
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                onEnd: function (evt) {
                    const catId = evt.to.getAttribute('data-cat-id');
                    const itemEls = evt.to.querySelectorAll('.status-item');
                    const newOrderIds = Array.from(itemEls).map(el => el.getAttribute('data-id'));
                    
                    // เรียก API reorder_status
                    updateStatusOrder(catId, newOrderIds);
                }
            });
        });
    }

    // --- API Calls ---

    function callApi(action, formData, onSuccess) {
        fetch(`${API_URL}?action=${action}`, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    onSuccess(res);
                } else {
                    alert(res.message || 'เกิดข้อผิดพลาด');
                }
            })
            .catch(err => alert('เชื่อมต่อ Server ไม่ได้'));
    }

    // 1. Category Functions
    function openAddCategoryModal() {
        document.getElementById('cat_id').value = '';
        document.getElementById('cat_name').value = '';
        document.getElementById('catModalTitle').innerText = 'เพิ่มหมวดหมู่ใหม่';
        document.getElementById('catModalHeader').classList.remove('bg-warning');
        document.getElementById('catModalHeader').classList.add('bg-primary');
        new bootstrap.Modal(document.getElementById('categoryModal')).show();
    }

    function openEditCategory(id, name) {
        document.getElementById('cat_id').value = id;
        document.getElementById('cat_name').value = name;
        document.getElementById('catModalTitle').innerText = 'แก้ไขชื่อหมวดหมู่';
        document.getElementById('catModalHeader').classList.remove('bg-primary');
        document.getElementById('catModalHeader').classList.add('bg-warning');
        new bootstrap.Modal(document.getElementById('categoryModal')).show();
    }

    function saveCategory() {
        const id = document.getElementById('cat_id').value;
        const name = document.getElementById('cat_name').value;
        if (!name) return alert('กรุณาระบุชื่อ');

        const fd = new FormData();
        fd.append('category_name', name);
        
        let action = 'add_category';
        if (id) {
            action = 'edit_category';
            fd.append('id', id);
        }

        callApi(action, fd, () => {
            bootstrap.Modal.getInstance(document.getElementById('categoryModal')).hide();
            loadWorkflows();
        });
    }

    function deleteCategory(id) {
        if(!confirm('ยืนยันการลบหมวดหมู่นี้? (ข้อมูลสถานะข้างในจะหายไปด้วย)')) return;
        const fd = new FormData();
        fd.append('id', id);
        callApi('delete_category', fd, loadWorkflows);
    }

    // 2. Status Functions
    function openAddStatus(catId) {
        document.getElementById('st_catId').value = catId;
        document.getElementById('st_id').value = '';
        document.getElementById('st_name').value = '';
        document.getElementById('st_color').value = '#6c757d'; // Default color
        document.getElementById('stModalTitle').innerText = 'เพิ่มสถานะใหม่';
        new bootstrap.Modal(document.getElementById('statusModal')).show();
    }

    function openEditStatus(catId, stId, name, color) {
        document.getElementById('st_catId').value = catId;
        document.getElementById('st_id').value = stId;
        document.getElementById('st_name').value = name;
        
        // แปลงสีเดิม (Bootstrap class) เป็น Hex ถ้าจำเป็น เพื่อแสดงใน Color Picker
        const colorMap = {
            'secondary': '#6c757d', 'info': '#0dcaf0', 'warning': '#ffc107',
            'primary': '#0d6efd', 'success': '#198754', 'danger': '#dc3545'
        };
        // ถ้าเป็น hex อยู่แล้วก็ใช้เลย ถ้าเป็น class ให้แปลง
        const hexColor = colorMap[color] || color;
        document.getElementById('st_color').value = hexColor;

        document.getElementById('stModalTitle').innerText = 'แก้ไขสถานะ';
        new bootstrap.Modal(document.getElementById('statusModal')).show();
    }

    function saveStatus() {
        const catId = document.getElementById('st_catId').value;
        const stId = document.getElementById('st_id').value;
        const name = document.getElementById('st_name').value;
        const color = document.getElementById('st_color').value;

        if(!name) return alert('ระบุชื่อสถานะ');

        const fd = new FormData();
        fd.append('category_id', catId);
        fd.append('status_name', name);
        fd.append('color_class', color);

        let action = 'add_status';
        if (stId) {
            action = 'edit_status';
            fd.append('status_id', stId);
        }

        callApi(action, fd, () => {
            bootstrap.Modal.getInstance(document.getElementById('statusModal')).hide();
            loadWorkflows();
        });
    }

    function deleteStatus(catId, stId) {
        if(!confirm('ต้องการลบสถานะนี้?')) return;
        const fd = new FormData();
        fd.append('category_id', catId);
        fd.append('status_id', stId);
        callApi('delete_status', fd, loadWorkflows);
    }

    // 3. Reorder Function (สำคัญ)
    function updateStatusOrder(catId, sortedIdsArray) {
        const fd = new FormData();
        fd.append('category_id', catId);
        // Backend คุณใช้ชื่อ 'sorted_ids' ในการรับค่า
        fd.append('sorted_ids', JSON.stringify(sortedIdsArray));

        // ส่งข้อมูลไปเงียบๆ ไม่ต้อง reload หน้า
        fetch(`${API_URL}?action=reorder_status`, { method: 'POST', body: fd })
            .then(res => res.json())
            .then(res => {
                if(!res.success) {
                    alert('บันทึกลำดับไม่สำเร็จ: ' + res.message);
                    loadWorkflows(); // โหลดข้อมูลเดิมกลับมาถ้าพลาด
                }
            });
    }
</script>