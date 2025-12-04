<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>EDE Mobile</title>
    
    <!-- CSS & Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- JS Libraries -->
    <script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <style>
        body { font-family: 'Prompt', sans-serif; background-color: #f8f9fa; padding-bottom: 70px; }
        
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; width: 100%;
            background: white; box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            display: flex; justify-content: space-around; padding: 10px 0;
            z-index: 1000; border-top-left-radius: 20px; border-top-right-radius: 20px;
        }
        .nav-item { text-align: center; color: #aaa; flex-grow: 1; cursor: pointer; transition: 0.2s; }
        .nav-item i { font-size: 1.5rem; display: block; margin-bottom: 2px; }
        .nav-item span { font-size: 0.75rem; }
        .nav-item.active { color: #00C853; font-weight: bold; }

        .page-section { display: none; padding: 20px; }
        .page-section.active { display: block; animation: fadeIn 0.3s; }

        /* Camera Box */
        .camera-box {
            position: relative;
            background: black;
            border-radius: 20px;
            overflow: hidden;
            min-height: 300px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        #reader { width: 100%; height: 100%; }
        
        .history-card, .search-card {
            background: white; border-radius: 15px; padding: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 15px;
            border-left: 4px solid #ddd; cursor: pointer; transition: 0.2s;
        }
        .history-card:active, .search-card:active { transform: scale(0.98); background: #f0f0f0; }
        .history-card.status-Received { border-left-color: #00C853; }
        .history-card.status-Sent { border-left-color: #FFC107; }

        #detailOverlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: white; z-index: 2000; overflow-y: auto;
            display: none; padding: 20px;
        }
        
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>
</head>
<body>

    <!-- 1. หน้าสแกน -->
    <div id="tab-scan" class="page-section active">
        <h4 class="fw-bold mb-3"><i class="fas fa-qrcode text-success me-2"></i>สแกนเอกสาร</h4>
        
        <div class="camera-box mb-3">
            <div id="reader"></div>
            <!-- ข้อความทับกล้อง -->
            <div id="cameraStatus" class="position-absolute top-50 start-50 translate-middle text-white text-center w-100" style="display:none; pointer-events:none;">
                <div class="spinner-border text-light mb-2"></div>
                <div>กำลังประมวลผล...</div>
            </div>
        </div>
        
        <div class="d-flex align-items-center bg-white p-3 rounded-4 shadow-sm">
            <img id="userImg" src="https://via.placeholder.com/50" class="rounded-circle me-3" width="50">
            <div>
                <small class="text-muted">ผู้ใช้งาน:</small>
                <div id="userName" class="fw-bold">Guest</div>
            </div>
        </div>
    </div>

    <!-- 2. หน้าค้นหา -->
    <div id="tab-search" class="page-section">
        <h4 class="fw-bold mb-3">🔍 ค้นหาเอกสาร</h4>
        <div class="input-group mb-4 shadow-sm">
            <input type="text" id="searchInput" class="form-control border-0 py-3" placeholder="เลขที่เอกสาร หรือ ชื่อเรื่อง...">
            <button class="btn btn-success px-4" onclick="searchDocs()"><i class="fas fa-search"></i></button>
        </div>
        <div id="searchResultArea">
            <p class="text-center text-muted mt-5"><i class="fas fa-search fa-3x opacity-25"></i><br>พิมพ์คำค้นหาด้านบน</p>
        </div>
    </div>

    <!-- 3. หน้าประวัติ -->
    <div id="tab-history" class="page-section">
        <h4 class="fw-bold mb-3">🕒 ประวัติของฉัน</h4>
        <div id="historyListArea">
            <div class="text-center py-5"><div class="spinner-border text-success"></div></div>
        </div>
    </div>

    <!-- 4. หน้าดูรายละเอียด -->
    <div id="detailOverlay">
        <button class="btn btn-light rounded-circle shadow-sm position-absolute top-0 end-0 m-3" onclick="closeDetail()">
            <i class="fas fa-times fa-lg"></i>
        </button>
        <h4 class="fw-bold mt-4 mb-3">📄 รายละเอียด</h4>
        
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-light">
            <h5 id="detailTitle" class="fw-bold text-primary mb-1">...</h5>
            <small id="detailCode" class="text-muted">...</small>
            <div class="mt-3">
                <span class="badge bg-secondary" id="detailStatus">...</span>
                <p class="mt-2 mb-0 small"><strong>ผู้รับปัจจุบัน:</strong> <span id="detailReceiver">...</span></p>
            </div>
        </div>

        <h6 class="fw-bold text-secondary border-bottom pb-2">Timeline</h6>
        <div id="detailTimeline" class="small"></div>

        <div class="d-grid gap-2 mt-4 pt-4 border-top">
            <button class="btn btn-success rounded-pill py-3 fw-bold shadow" onclick="openUpdateModal()">
                <i class="fas fa-edit me-2"></i> อัปเดตสถานะ
            </button>
        </div>
    </div>

    <!-- Bottom Nav -->
    <div class="bottom-nav">
        <div class="nav-item active" onclick="switchTab('scan')">
            <i class="fas fa-qrcode"></i><span>สแกน</span>
        </div>
        <div class="nav-item" onclick="switchTab('search')">
            <i class="fas fa-search"></i><span>ค้นหา</span>
        </div>
        <div class="nav-item" onclick="switchTab('history')">
            <i class="fas fa-history"></i><span>ประวัติ</span>
        </div>
    </div>

    <script>
        const MY_LIFF_ID = "2008591805-LlbR2M99"; 
        
        let html5QrCode;
        let userProfile = {};
        let currentDocCode = '';
        
        // --- ตัวแปรสำคัญ: ล็อกการสแกน ---
        let isProcessing = false; 

        // --- Init ---
        async function main() {
            await liff.init({ liffId: MY_LIFF_ID });
            if (!liff.isLoggedIn()) { liff.login(); return; }
            
            userProfile = await liff.getProfile();
            document.getElementById('userImg').src = userProfile.pictureUrl;
            document.getElementById('userName').innerText = userProfile.displayName;
            
            startCamera(); 
        }

        // --- Camera Logic ---
        function startCamera() {
            if(html5QrCode) return; // ถ้าเปิดอยู่แล้วไม่ต้องเปิดซ้ำ
            
            // ปลดล็อก (Reset Flag) เพื่อให้เริ่มสแกนใหม่ได้
            isProcessing = false;
            document.getElementById('cameraStatus').style.display = 'none'; // ซ่อนสถานะโหลด

            html5QrCode = new Html5Qrcode("reader");
            html5QrCode.start(
                { facingMode: "environment" }, 
                { fps: 10, qrbox: 250 }, 
                onScanSuccess, 
                (err) => {}
            ).catch(err => console.warn("Camera failed", err));
        }
        
        function stopCamera() {
            if(html5QrCode) {
                html5QrCode.stop().then(() => { 
                    html5QrCode.clear();
                    html5QrCode = null; 
                }).catch(err => console.log(err));
            }
        }

        function onScanSuccess(decodedText) {
            // --- 1. เช็คด่านแรก: ถ้ากำลังประมวลผลอยู่ ห้ามทำอะไรทั้งนั้น ---
            if (isProcessing) return; 
            
            // --- 2. ล็อกทันที! ห้ามเฟรมถัดไปเข้ามา ---
            isProcessing = true;

            // --- 3. หยุดกล้องทันที ---
            html5QrCode.stop().then(() => {
                html5QrCode.clear();
                html5QrCode = null;
            }).catch(err => console.log("Stop failed", err));

            // โชว์สถานะบนจอกล้องว่า "กำลังประมวลผล" (Feedback ให้ user รู้ว่าสแกนติดแล้ว)
            document.getElementById('cameraStatus').style.display = 'block';

            // --- 4. ส่งข้อมูลไป API (นับยอด +1) ---
            loadDocDetail(decodedText, true);
        }

        // --- Navigation ---
        function switchTab(tabName) {
            document.querySelectorAll('.page-section').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
            document.getElementById('tab-' + tabName).classList.add('active');
            event.currentTarget.classList.add('active');

            // ถ้าออกจากหน้าสแกน ให้ปิดกล้อง
            if(tabName === 'scan') startCamera(); 
            else stopCamera();

            if(tabName === 'history') loadHistory();
        }

        // --- API Functions ---
        async function searchDocs() {
            const keyword = document.getElementById('searchInput').value;
            if(!keyword) return;
            const res = await fetch(`api/liff_api.php?action=search&keyword=${keyword}`);
            const json = await res.json();
            
            let html = '';
            if(json.data && json.data.length > 0) {
                json.data.forEach(doc => {
                    html += `<div class="search-card" onclick="loadDocDetail('${doc.document_code}', false)">
                                <div class="fw-bold">${doc.title}</div>
                                <small class="text-muted">${doc.document_code}</small>
                                <span class="badge bg-light text-dark float-end">${doc.current_status}</span>
                             </div>`;
                });
            } else { html = '<p class="text-center text-muted">ไม่พบข้อมูล</p>'; }
            document.getElementById('searchResultArea').innerHTML = html;
        }

        async function loadHistory() {
            const res = await fetch(`api/liff_api.php?action=history&line_id=${userProfile.userId}`);
            const json = await res.json();
            
            let html = '';
            if(json.data && json.data.length > 0) {
                json.data.forEach(log => {
                    html += `<div class="history-card status-${log.status}" onclick="loadDocDetail('${log.document_code}', false)">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold text-dark">${log.status}</span>
                                    <small class="text-muted">${log.action_time}</small>
                                </div>
                                <small class="d-block text-truncate">${log.title}</small>
                             </div>`;
                });
            } else { html = '<p class="text-center text-muted mt-5">ยังไม่มีประวัติการสแกน</p>'; }
            document.getElementById('historyListArea').innerHTML = html;
        }

        async function loadDocDetail(code, fromScanner = false) {
            currentDocCode = code;
            
            // ถ้ามาจาก Search ไม่ต้อง Loading ทับ (เพราะมีอยู่แล้ว)
            if(!fromScanner) Swal.fire({ title: 'Loading...', didOpen: () => Swal.showLoading() });
            
            try {
                let url = `api/get_doc_info.php?code=${code}`;
                if (fromScanner) url += '&action=scan';

                const res = await fetch(url);
                const json = await res.json();
                
                if(json.error) throw new Error(json.error);

                const doc = json.doc;
                document.getElementById('detailTitle').innerText = doc.title;
                document.getElementById('detailCode').innerText = doc.document_code;
                document.getElementById('detailStatus').innerHTML = `${doc.current_status} <span class="badge bg-light text-dark ms-2">👁️ ${doc.view_count}</span>`;
                document.getElementById('detailReceiver').innerText = doc.receiver_name || '-';

                let timelineHtml = '';
                json.logs.forEach(log => {
                    const actor = log.actor_name_snapshot || log.fullname || 'Unknown';
                    timelineHtml += `<div class="mb-3 ps-3 border-start border-3 ${log.status === 'Received' ? 'border-success' : 'border-warning'}">
                                        <div class="fw-bold text-dark">${log.status}</div>
                                        <small class="text-muted">${log.action_time}</small><br>
                                        <small>โดย: ${actor}</small>
                                     </div>`;
                });
                document.getElementById('detailTimeline').innerHTML = timelineHtml;

                Swal.close();
                document.getElementById('detailOverlay').style.display = 'block';

            } catch (err) {
                Swal.fire('Error', 'ไม่พบข้อมูลเอกสาร', 'error');
                
                // ถ้าสแกนไม่เจอ ให้เคลียร์ Flag และเปิดกล้องใหม่
                if(document.getElementById('tab-scan').classList.contains('active')) {
                    setTimeout(() => { 
                        isProcessing = false; // ปลดล็อก
                        startCamera(); 
                    }, 1500);
                }
            }
        }

        function closeDetail() {
            document.getElementById('detailOverlay').style.display = 'none';
            // ถ้าอยู่หน้า Scan ให้เปิดกล้องต่อ และปลดล็อก
            if(document.getElementById('tab-scan').classList.contains('active')) {
                isProcessing = false;
                startCamera();
            }
        }

        async function openUpdateModal() {
            const { value: formValues } = await Swal.fire({
                title: 'อัปเดตสถานะ',
                html:
                    '<select id="swal-status" class="form-select mb-3">' +
                    '<option value="Received">ได้รับแล้ว</option>' +
                    '<option value="Sent">ส่งต่อ</option>' +
                    '</select>' +
                    '<input id="swal-receiver" class="form-control" placeholder="ชื่อผู้รับคนต่อไป (ถ้าส่งต่อ)">',
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'บันทึก',
                preConfirm: () => {
                    return [
                        document.getElementById('swal-status').value,
                        document.getElementById('swal-receiver').value
                    ]
                }
            });

            if (formValues) {
                const [status, receiver] = formValues;
                if(status === 'Sent' && !receiver) {
                    Swal.fire('กรุณาระบุชื่อผู้รับ'); return;
                }
                
                const formData = new FormData();
                formData.append('doc_code', currentDocCode);
                formData.append('status', status);
                formData.append('receiver_name', receiver);
                formData.append('line_user_id', userProfile.userId);
                formData.append('display_name', userProfile.displayName);
                formData.append('picture_url', userProfile.pictureUrl);
                formData.append('device_info', liff.getOS());

                await fetch('api/update_status.php', { method: 'POST', body: formData });
                Swal.fire('สำเร็จ', 'บันทึกข้อมูลแล้ว', 'success').then(() => {
                    closeDetail(); 
                });
            }
        }

        main();
    </script>
</body>
</html>