// ตั้งค่าตัวแปร Global
const MY_LIFF_ID = "2008591805-LlbR2M99";
let userProfile = { userId: "", displayName: "Guest", pictureUrl: "" };
let currentDocCode = "";

// [แก้ไข 1] เปลี่ยนตัวแปรจาก creator เป็น workflow_id
let currentDocWorkflowId = "cat_default"; 

// ฟังก์ชัน Bypass Cache
const originalFetch = window.fetch;
window.fetch = function(url, options) {
    if (url.toString().startsWith("https://liffsdk.line-scdn.net/xlt/") && url.toString().endsWith(".json")) {
        console.log("Bypassing LIFF Cache:", url);
        url = url + "?ts=" + Math.random();
    }
    return originalFetch(url, options);
};

// --- Main ---
async function main() {
    try {
        await liff.init({ liffId: MY_LIFF_ID });
        if (!liff.isLoggedIn()) { liff.login(); return; }
        userProfile = await liff.getProfile();
        
        if (document.getElementById("userImg")) document.getElementById("userImg").src = userProfile.pictureUrl;
        if (document.getElementById("userName")) document.getElementById("userName").innerText = userProfile.displayName;

        if (!liff.isInClient()) Swal.fire("แจ้งเตือน", "กรุณาเปิดผ่านแอปพลิเคชัน LINE บนมือถือเพื่อใช้งานสแกนเนอร์", "warning");
    } catch (err) {
        console.error("LIFF Init Error:", err);
        Swal.fire("Error", "LIFF Init Failed: " + err, "error");
    }
}

// --- Scanner ---
async function openLineScanner() {
    if (liff.isInClient() && liff.getOS() !== "web") {
        try {
            const result = await liff.scanCodeV2();
            if (result.value) loadDocDetail(result.value, true);
        } catch (err) { console.error("Scan Error:", err); }
    } else {
        Swal.fire({ icon: "error", title: "ไม่รองรับ", text: "ฟีเจอร์นี้ใช้งานได้เฉพาะบนแอป LINE ในมือถือเท่านั้น" });
    }
}

// --- Tabs ---
function switchTab(tabName) {
    document.querySelectorAll(".page-section").forEach(el => el.classList.remove("active"));
    document.querySelectorAll(".nav-item").forEach(el => el.classList.remove("active"));
    document.getElementById("tab-" + tabName).classList.add("active");
    if (event && event.currentTarget) event.currentTarget.classList.add("active");

    if (tabName === "scan") openLineScanner();
    else if (tabName === "history") loadHistory();
}

// --- Search ---
async function searchDocs() {
    const keyword = document.getElementById("searchInput").value;
    if (keyword) {
        document.getElementById("searchResultArea").innerHTML = '<div class="text-center mt-3"><i class="fas fa-spinner fa-spin"></i> กำลังค้นหา...</div>';
        try {
            const res = await fetch(`api/liff_api.php?action=search&keyword=${keyword}`);
            const json = await res.json();
            let html = "";
            if (json.data && json.data.length > 0) {
                json.data.forEach(doc => {
                    html += `<div class="search-card" onclick="loadDocDetail('${doc.document_code}', false)">
                                <div class="fw-bold">${doc.title}</div>
                                <small class="text-muted">${doc.document_code}</small>
                                <span class="badge bg-light text-dark float-end">${doc.current_status}</span>
                             </div>`;
                });
            } else { html = '<p class="text-center text-muted mt-3">ไม่พบข้อมูล</p>'; }
            document.getElementById("searchResultArea").innerHTML = html;
        } catch (err) { console.error(err); document.getElementById("searchResultArea").innerHTML = '<p class="text-center text-danger">เกิดข้อผิดพลาด</p>'; }
    }
}

// --- History ---
async function loadHistory() {
    try {
        const res = await fetch(`api/liff_api.php?action=history&line_id=${userProfile.userId}`);
        const json = await res.json();
        let html = "";
        if (json.data && json.data.length > 0) {
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
        document.getElementById("historyListArea").innerHTML = html;
    } catch (err) { console.error(err); }
}

// --- Load Detail (จุดสำคัญ 1) ---
async function loadDocDetail(code, fromScanner = false) {
    currentDocCode = code;
    if (!fromScanner) Swal.fire({ title: "Loading...", didOpen: () => Swal.showLoading() });
    
    try {
        let url = `${site_url}/api/getdocinfo/${code}/`; 
        if (fromScanner) url += "?action=scan";
        
        const res = await fetch(url);
        const json = await res.json();
        
        if (json.error) throw new Error(json.error);
        const doc = json.doc;

        // [แก้ไข 2] รับค่า workflow_id จากเอกสารมาเก็บไว้ (ถ้าไม่มี ใช้ cat_default)
        currentDocWorkflowId = doc.workflow_id || 'cat_default';

        document.getElementById("detailTitle").innerText = doc.title;
        document.getElementById("detailCode").innerText = doc.document_code;
        document.getElementById("detailStatus").innerHTML = `${doc.current_status} <span class="badge bg-light text-dark ms-2">👁️ ${doc.view_count}</span>`;
        document.getElementById("detailReceiver").innerText = doc.receiver_name || "-";

        let timelineHtml = "";
        if (json.logs) {
            json.logs.forEach(log => {
                const actor = log.actor_name_snapshot || log.fullname || "Unknown";
                timelineHtml += `<div class="mb-3 ps-3 border-start border-3 ${log.status === "Received" ? "border-success" : "border-warning"}">
                                    <div class="fw-bold text-dark">${log.status}</div>
                                    <small class="text-muted">${log.action_time}</small><br>
                                    <small>โดย: ${actor}</small>
                                 </div>`;
            });
        }
        document.getElementById("detailTimeline").innerHTML = timelineHtml;
        
        Swal.close();
        document.getElementById("detailOverlay").style.display = "block";
        
    } catch (err) {
        Swal.fire("Error", "ไม่พบข้อมูลเอกสาร หรือ " + err.message, "error");
    }
}

function closeDetail() { document.getElementById("detailOverlay").style.display = "none"; }

// --- Update Modal (จุดสำคัญ 2) ---
async function openUpdateModal() {
    let statusOptions = "";
    try {
        // [แก้ไข 3] ส่ง workflow_id ไปถาม API (จะได้เฉพาะอันที่ต้องการ)
        const res = await fetch(`api/liff_api.php?action=get_statuses&workflow_id=${currentDocWorkflowId}`);
        const json = await res.json();
        
        if (json.status === "success" && json.data.length > 0) {
            let currentCategory = "";
            json.data.forEach(s => {
                if (s.category !== currentCategory) {
                    if (currentCategory !== "") statusOptions += "</optgroup>";
                    statusOptions += `<optgroup label="${s.category}">`;
                    currentCategory = s.category;
                }
                statusOptions += `<option value="${s.status_name}">${s.status_name}</option>`;
            });
            if (currentCategory !== "") statusOptions += "</optgroup>";
        } else {
            statusOptions = '<option value="Received">ได้รับแล้ว</option><option value="Sent">ส่งต่อ</option>';
        }
    } catch (e) {
        console.error("Fetch Status Error:", e);
        statusOptions = '<option value="Received">ได้รับแล้ว</option><option value="Sent">ส่งต่อ</option>';
    }

    const { value: formValues } = await Swal.fire({
        title: "อัปเดตสถานะ",
        html: `<label class="form-label text-start w-100">เลือกสถานะ:</label>
               <select id="swal-status" class="form-select mb-3">${statusOptions}</select>` +
              `<label class="form-label text-start w-100">*หมายเหตุ (ถ้ามี):</label>
               <input id="swal-receiver" class="form-control" placeholder="ระบุหมายเหตุ">`,
        focusConfirm: false, showCancelButton: true, confirmButtonText: "บันทึก", confirmButtonColor: "#00C853",
        preConfirm: () => [document.getElementById("swal-status").value, document.getElementById("swal-receiver").value]
    });

    if (formValues) {
        const [status, receiver] = formValues;
        const formData = new FormData();
        formData.append("doc_code", currentDocCode);
        formData.append("status", status);
        formData.append("receiver_name", receiver);
        formData.append("line_user_id", userProfile.userId);
        formData.append("display_name", userProfile.displayName);
        formData.append("picture_url", userProfile.pictureUrl);
        formData.append("device_info", liff.getOS());

        await fetch("api/update_status.php", { method: "POST", body: formData });
        Swal.fire({ title: "สำเร็จ", text: "บันทึกข้อมูลเรียบร้อยแล้ว", icon: "success", timer: 1500, showConfirmButton: false })
        .then(() => { closeDetail(); });
    }
}

main();