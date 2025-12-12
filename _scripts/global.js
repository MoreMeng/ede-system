// global.js - JavaScript สำหรับทั่วทั้งระบบ

// ฟังก์ชันเสริมต่างๆ ที่ใช้ร่วมกันทั้งระบบ

// ฟังก์ชันแสดงข้อความแจ้งเตือน (ถ้ามี SweetAlert2)
function showAlert(title, text, icon = 'info') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            confirmButtonText: 'ตกลง'
        });
    } else {
        alert(text);
    }
}

// ฟังก์ชันสำหรับ format วันที่
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return date.toLocaleDateString('th-TH', options);
}

// ฟังก์ชันสำหรับ debounce (ใช้กับ search)
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ฟังก์ชัน Loading spinner
function showLoading(show = true) {
    const loadingEl = document.getElementById('loading');
    if (loadingEl) {
        loadingEl.style.display = show ? 'block' : 'none';
    }
}

// เพิ่มฟังก์ชันอื่นๆ ที่ใช้ร่วมกันในระบบได้ตามต้องการ
