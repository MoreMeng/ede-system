<div class="d-flex justify-content-center w-100">
    <div class="col-md-5 col-sm-8 col-12">
        <!-- 1. หน้าสแกน -->
        <div id="tab-scan" class="page-section active">
            <h4 class="fw-bold mb-3"><i class="fas fa-qrcode text-success me-2"></i>สแกนเอกสาร</h4>

            <div class="camera-box mb-3">
                <div id="reader"></div>
                <div id="cameraStatus" class="position-absolute top-50 start-50 translate-middle text-white text-center w-100" style="display:none; pointer-events:none;">
                    <div class="spinner-border text-light mb-2"></div>
                    <div>กำลังประมวลผล...</div>
                </div>
            </div>

            <div class="d-flex align-items-center bg-white p-3 rounded-4 shadow-sm">
                <div id="userImg" class="rounded-circle me-3 d-flex align-items-center justify-content-center bg-success text-white fw-bold" width="50" style="width: 50px; height: 50px; font-size: 1.5rem;">
                    <i class="fas fa-user"></i>
                </div>
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
                <div class="text-center py-5">
                    <div class="spinner-border text-success"></div>
                </div>
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