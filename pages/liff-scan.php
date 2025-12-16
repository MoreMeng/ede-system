

<div class="d-flex justify-content-center w-100">
    <div class="col-md-5 col-sm-8 col-12">
        
        <div id="tab-scan" class="page-section active">
            <div class="d-flex align-items-center bg-white p-3 rounded-4 shadow-sm mb-4">
                <img id="userImg" src="" class="rounded-circle me-3 border border-2 border-success" 
                     width="50" height="50" style="object-fit: cover; background-color: #ddd;">
                <div>
                    <small class="text-muted">ผู้ใช้งาน:</small>
                    <div id="userName" class="fw-bold text-truncate" style="max-width: 200px;">Guest</div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 text-center py-5 bg-white">
                <div class="card-body">
                    <div class="mb-4">
                        <i class="fas fa-qrcode fa-6x text-success opacity-50"></i>
                    </div>
                    <h4 class="fw-bold mb-3">สแกนเอกสาร</h4>
                    <p class="text-muted mb-4 small">
                        กดปุ่มด้านล่างเพื่อเปิดกล้อง<br>ผ่านแอปพลิเคชัน LINE
                    </p>
                    
                    <button class="btn btn-success btn-lg rounded-pill px-5 py-3 shadow-sm fw-bold" onclick="openLineScanner()">
                        <i class="fas fa-camera me-2"></i> สแกน QR Code
                    </button>
                </div>
            </div>
        </div>

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

        <div id="tab-history" class="page-section">
            <h4 class="fw-bold mb-3">🕒 ประวัติของฉัน</h4>
            <div id="historyListArea">
                <div class="text-center py-5">
                    <div class="spinner-border text-success"></div>
                </div>
            </div>
        </div>

        <div id="detailOverlay" style="display: none;">
            <button class="btn btn-light rounded-circle shadow-sm position-absolute top-0 end-0 m-3" onclick="closeDetail()" style="z-index: 1050;">
                <i class="fas fa-times fa-lg"></i>
            </button>
            
            <div class="container pt-4 pb-5">
                <h4 class="fw-bold mb-3">📄 รายละเอียด</h4>

                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                    <h5 id="detailTitle" class="fw-bold text-primary mb-1">...</h5>
                    <small id="detailCode" class="text-muted d-block mb-2">...</small>
                    <div class="d-flex align-items-center justify-content-between mt-2">
                        <span class="badge bg-secondary" id="detailStatus">...</span>
                    </div>
                    <p class="mt-3 mb-0 small text-muted border-top pt-2">
                        <strong>ผู้รับปัจจุบัน:</strong> <span id="detailReceiver" class="text-dark">...</span>
                    </p>
                </div>

                <h6 class="fw-bold text-secondary border-bottom pb-2 mb-3">Timeline</h6>
                <div id="detailTimeline" class="small ps-1"></div>

                <div class="d-grid gap-2 mt-4 pt-4">
                    <button class="btn btn-success rounded-pill py-3 fw-bold shadow" onclick="openUpdateModal()">
                        <i class="fas fa-edit me-2"></i> อัปเดตสถานะ
                    </button>
                </div>
            </div>
        </div>
    </div>

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
</div>