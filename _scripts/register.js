// register.js - JavaScript สำหรับหน้าลงทะเบียนเอกสาร

const API_URL = 'api/manage_workflow.php';
let allWorkflows = [];

// ฟังก์ชันหลักที่ทำงานเมื่อโหลดหน้าเว็บเสร็จ
document.addEventListener('DOMContentLoaded', function() {
    // โหลดข้อมูล Workflows
    loadWorkflows();

    // Event Listener สำหรับเลือก Workflow
    document.getElementById('workflowSelect').addEventListener('change', function() {
        const selectedId = this.value;
        const submitBtn = document.getElementById('btnSubmit');
        const statusPreview = document.getElementById('statusPreview');
        const initialStatusInput = document.getElementById('initialStatusInput');
        const workflowIdInput = document.getElementById('workflowIdInput');

        if (selectedId) {
            const selectedCat = allWorkflows.find(cat => cat.id === selectedId);

            if (selectedCat && selectedCat.statuses.length > 0) {
                // เปิดใช้งานปุ่มบันทึก
                submitBtn.classList.remove('btn-disabled-custom');
                submitBtn.style.backgroundColor = '#00E676';
                submitBtn.style.color = 'black';

                // ตั้งค่าสถานะเริ่มต้น
                const firstStatus = selectedCat.statuses[0];
                initialStatusInput.value = firstStatus.name;
                workflowIdInput.value = selectedCat.id;

                // แสดง Preview สถานะ
                statusPreview.innerHTML = `<i class="fas fa-check-circle text-success"></i> สถานะเริ่มต้น: <span class="badge bg-${firstStatus.color}">${firstStatus.name}</span>`;
            } else {
                alert('หมวดหมู่นี้ยังไม่มีการกำหนดสถานะ (Workflow ว่างเปล่า) กรุณาไปตั้งค่าก่อน');
                this.value = "";
                disableSubmit();
            }
        } else {
            disableSubmit();
        }
    });
});

// ฟังก์ชันปิดการใช้งานปุ่ม Submit
function disableSubmit() {
    const btn = document.getElementById('btnSubmit');
    const preview = document.getElementById('statusPreview');

    btn.classList.add('btn-disabled-custom');
    btn.removeAttribute('style');

    preview.innerHTML = `<i class="fas fa-arrow-right"></i> สถานะเริ่มต้น: <span class="text-secondary">-</span>`;
    document.getElementById('initialStatusInput').value = "";
}

// ฟังก์ชันโหลดข้อมูล Workflows จาก API
function loadWorkflows() {
    const select = document.getElementById('workflowSelect');
    select.innerHTML = '<option value="" disabled selected>กำลังโหลดข้อมูล...</option>';

    // ดึง User ID จาก global variable ที่ถูกกำหนดใน PHP
    const userId = (typeof CURRENT_USER_ID !== 'undefined') ? CURRENT_USER_ID : '';

    // ส่ง user_id ไปกับ request เพื่อกรองข้อมูล
    fetch(`${API_URL}?action=list&user_id=${userId}`)
        .then(res => res.json())
        .then(res => {
            select.innerHTML = '<option value="" selected disabled>-- กรุณาเลือกหมวดหมู่สถานะ --</option>';

            if (res.success && res.data.length > 0) {
                allWorkflows = res.data;
                res.data.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.id;
                    option.textContent = cat.name;
                    select.appendChild(option);
                });
            } else {
                select.innerHTML = '<option value="" disabled>ไม่พบข้อมูล (ต้องสร้างหมวดหมู่ก่อน)</option>';
            }
        })
        .catch(err => {
            console.error(err);
            select.innerHTML = '<option value="" disabled>Error loading workflows</option>';
        });
}
