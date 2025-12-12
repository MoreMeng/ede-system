
        const MY_LIFF_ID = "2008591805-LlbR2M99"; // LIFF ID เดิมของคุณ

        let html5QrCode;
        let userProfile = { userId: '', displayName: 'Guest', pictureUrl: '' };
        let currentDocCode = '';
        let currentDocCreator = 0; // เพิ่มตัวแปรเก็บ ID คนสร้าง
        let isProcessing = false;

        // --- Init ---
        async function main() {
            try {
                await liff.init({ liffId: MY_LIFF_ID });
                if (!liff.isLoggedIn()) { liff.login(); return; }

                userProfile = await liff.getProfile();
                document.getElementById('userImg').src = userProfile.pictureUrl;
                document.getElementById('userName').innerText = userProfile.displayName;

                startCamera();
            } catch (err) {
                console.error('LIFF Init Error:', err);
                startCamera();
            }
        }

        // --- Camera Logic ---
        function startCamera() {
            if(html5QrCode) return;
            isProcessing = false;
            document.getElementById('cameraStatus').style.display = 'none';

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
            if (isProcessing) return;
            isProcessing = true;

            html5QrCode.stop().then(() => {
                html5QrCode.clear();
                html5QrCode = null;
            }).catch(err => console.log("Stop failed", err));

            document.getElementById('cameraStatus').style.display = 'block';
            loadDocDetail(decodedText, true);
        }

        // --- Navigation ---
        function switchTab(tabName) {
            document.querySelectorAll('.page-section').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
            document.getElementById('tab-' + tabName).classList.add('active');
            event.currentTarget.classList.add('active');

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
            if(!fromScanner) Swal.fire({ title: 'Loading...', didOpen: () => Swal.showLoading() });

            try {
                let url = `${site_url}/api/getdocinfo/${code}/`;
                if (fromScanner) url += '?action=scan';

                const res = await fetch(url);
                const json = await res.json();

                if(json.error) throw new Error(json.error);

                const doc = json.doc;

                // เก็บค่า ID คนสร้าง เพื่อเอาไปใช้ตอนเปิด Modal
                currentDocCreator = doc.created_by;

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
                if(document.getElementById('tab-scan').classList.contains('active')) {
                    setTimeout(() => {
                        isProcessing = false;
                        startCamera();
                    }, 1500);
                }
            }
        }

        function closeDetail() {
            document.getElementById('detailOverlay').style.display = 'none';
            if(document.getElementById('tab-scan').classList.contains('active')) {
                isProcessing = false;
                startCamera();
            }
        }

        // --- ส่วนที่ปรับปรุง: ดึง Status จาก JSON และจัดกลุ่ม ---
        async function openUpdateModal() {
            let statusOptions = '';
            try {
                // ส่ง creator_id ไปให้ API เพื่อดึง Workflow ของคนนั้น
                const res = await fetch(`api/liff_api.php?action=get_statuses&creator_id=${currentDocCreator}`);
                const json = await res.json();

                if(json.status === 'success' && json.data.length > 0) {
                    // จัดกลุ่มตาม Category (ถ้ามี)
                    let currentCategory = '';
                    json.data.forEach(s => {
                        // ถ้าเปลี่ยนหมวดหมู่ ให้ปิด optgroup เดิม และเปิดใหม่
                        if (s.category !== currentCategory) {
                            if (currentCategory !== '') statusOptions += '</optgroup>';
                            statusOptions += `<optgroup label="${s.category}">`;
                            currentCategory = s.category;
                        }
                        statusOptions += `<option value="${s.status_name}">${s.status_name}</option>`;
                    });
                    if (currentCategory !== '') statusOptions += '</optgroup>';

                } else {
                    statusOptions = '<option value="Received">ได้รับแล้ว</option><option value="Sent">ส่งต่อ</option>';
                }
            } catch (e) {
                console.error("Fetch Status Error:", e);
                statusOptions = '<option value="Received">ได้รับแล้ว</option><option value="Sent">ส่งต่อ</option>';
            }

            const { value: formValues } = await Swal.fire({
                title: 'อัปเดตสถานะ',
                html:
                    `<label class="form-label text-start w-100">เลือกสถานะ:</label>
                     <select id="swal-status" class="form-select mb-3">${statusOptions}</select>` +
                    `<label class="form-label text-start w-100">ส่งต่อให้ (ถ้ามี):</label>
                     <input id="swal-receiver" class="form-control" placeholder="ระบุชื่อผู้รับคนถัดไป">`,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'บันทึก',
                confirmButtonColor: '#00C853',
                preConfirm: () => {
                    return [
                        document.getElementById('swal-status').value,
                        document.getElementById('swal-receiver').value
                    ]
                }
            });

            if (formValues) {
                const [status, receiver] = formValues;

                const formData = new FormData();
                formData.append('doc_code', currentDocCode);
                formData.append('status', status);
                formData.append('receiver_name', receiver);
                formData.append('line_user_id', userProfile.userId);
                formData.append('display_name', userProfile.displayName);
                formData.append('picture_url', userProfile.pictureUrl);
                formData.append('device_info', liff.getOS());

                await fetch('../api/update_status.php', { method: 'POST', body: formData });
                Swal.fire({
                    title: 'สำเร็จ',
                    text: 'บันทึกข้อมูลเรียบร้อยแล้ว',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    closeDetail();
                });
            }
        }

        main();
