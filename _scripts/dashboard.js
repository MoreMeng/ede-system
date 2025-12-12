// dashboard.js - JavaScript สำหรับหน้า Dashboard

// คำนวณและแสดงเวลาโหลด (รวม Server + Client)
window.addEventListener('load', function() {
    const navTiming = performance.getEntriesByType('navigation')[0];

    // ดึงค่า serverTimeMs จาก global variable ที่ถูกกำหนดใน PHP
    const serverTimeMs = (typeof SERVER_TIME_MS !== 'undefined') ? SERVER_TIME_MS : 0;

    const clientRenderTime = navTiming ? navTiming.domInteractive - navTiming.fetchStart : 0;
    const totalLoadTime = (performance.now() / 1000).toFixed(3);

    const loadTimeElement = document.getElementById('loadTime');
    if (loadTimeElement) {
        loadTimeElement.textContent = totalLoadTime;
    }
});

// ฟังก์ชันแสดง QR Code Modal
function showQRModal(docCode, docTitle) {
    document.getElementById('modalDocCode').innerText = "รหัส: " + docCode;
    document.getElementById('modalDocTitle').innerText = docTitle;
    document.getElementById('btnPrintLink').href = '../print/' + docCode;
    document.getElementById('btnPrintLink').target = '_blank';

    const qrContainer = document.getElementById("qrcode");
    qrContainer.innerHTML = "";

    // สร้าง QR Code (ต้องแน่ใจว่ามี QRCode library)
    if (typeof QRCode !== 'undefined') {
        new QRCode(qrContainer, {
            text: docCode,
            width: 180,
            height: 180
        });
    }

    // แสดง Modal
    const qrModal = new bootstrap.Modal(document.getElementById('qrModal'));
    qrModal.show();
}

// ฟังก์ชันแสดงรายละเอียดเอกสาร
async function openDetailModal(code) {
    const detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
    detailModal.show();

    document.getElementById('modalLoading').style.display = 'block';
    document.getElementById('modalContent').style.display = 'none';

    try {
        // เรียก API เพื่อดึงข้อมูลเอกสาร
        const res = await fetch(`${site_url}/api/getdocinfo/${code}/`);
        const data = await res.json();

        if(data.error) throw new Error(data.error);

        const doc = data.doc;

        // แสดงข้อมูลเอกสาร
        document.getElementById('d_title').innerText = doc.title;
        document.getElementById('d_code').innerText = doc.document_code;
        document.getElementById('d_status').innerText = doc.current_status;
        document.getElementById('d_type').innerText = doc.type_name || '-';
        document.getElementById('d_date').innerText = doc.created_at;
        document.getElementById('d_sender').innerText = doc.sender_name;
        document.getElementById('d_receiver').innerText = doc.receiver_name;

        // ใส่ตัวเลขยอดวิวลงไป
        const viewsElement = document.getElementById('d_views');
        if (viewsElement) {
            viewsElement.innerText = doc.view_count || 0;
        }

        // สร้าง Timeline
        let html = '';
        if(data.logs && data.logs.length > 0) {
            data.logs.forEach((log, index) => {
                const activeClass = index === 0 ? 'active' : '';
                const actor = log.actor_name_snapshot || log.fullname || 'Unknown';
                const actorPic = log.actor_pic_snapshot ?
                    `<img src="${log.actor_pic_snapshot}" class="rounded-circle me-1" width="20">` :
                    '<i class="fas fa-user-circle me-1"></i>';

                html += `
                    <div class="timeline-item">
                        <div class="timeline-dot ${activeClass}"></div>
                        <div class="ps-4">
                            <div class="d-flex justify-content-between">
                                <strong class="text-dark">${log.status}</strong>
                                <small class="text-muted">${log.action_time}</small>
                            </div>
                            <small class="text-secondary d-flex align-items-center mt-1">
                                โดย: ${actorPic} ${actor}
                            </small>
                            ${log.location_note ? `<br><small class="text-danger"><i class="fas fa-map-marker-alt"></i> ${log.location_note}</small>` : ''}
                        </div>
                    </div>`;
            });
        } else {
            html = '<p class="text-muted ms-4">ยังไม่มีประวัติ</p>';
        }

        document.getElementById('d_timeline').innerHTML = html;

        // แสดงเนื้อหา
        document.getElementById('modalLoading').style.display = 'none';
        document.getElementById('modalContent').style.display = 'block';

    } catch (err) {
        alert("ไม่สามารถโหลดข้อมูลได้: " + err.message);
    }
}
