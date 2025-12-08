<?php 
session_start();
require_once 'config/db.php';

// ดึงข้อมูลประเภทเอกสารสำหรับ Dropdown
$types = [];
try { 
    if(isset($pdo)) { 
        $stmt = $pdo->query("SELECT * FROM document_type"); 
        $types = $stmt->fetchAll(); 
    } 
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ลงทะเบียน - EDE System</title>
    <!-- CSS & Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <?php 
            $page_title = "ลงทะเบียน"; 
            $header_class = "header-register"; 
            include 'includes/topbar.php'; 
        ?>

        <div class="page-content">
            <h5 class="mb-5 fw-bold text-secondary">**ลงทะเบียนเอกสารใหม่**</h5>
            
            <form action="api/save_document.php" method="POST" class="mx-auto" style="max-width: 900px;">
                <input type="hidden" name="created_by" value="<?php echo $_SESSION['user_id'] ?? 1; ?>">
                
                <!-- 1. ชื่อเรื่อง -->
                <div class="row mb-4 align-items-center">
                    <div class="col-md-3 text-md-end">
                        <label class="fw-bold text-secondary">ชื่อเรื่อง</label>
                    </div>
                    <div class="col-md-9">
                        <input type="text" name="title" required class="form-control custom-input" placeholder="ระบุชื่อเรื่องเอกสาร...">
                    </div>
                </div>

                <!-- 2. เลขที่อ้างอิง -->
                <div class="row mb-4 align-items-center">
                    <div class="col-md-3 text-md-end">
                        <label class="fw-bold text-secondary">เลขที่อ้างอิง</label>
                    </div>
                    <div class="col-md-9">
                        <input type="text" name="reference_no" class="form-control custom-input" placeholder="เช่น ศธ 0512/123 (ถ้ามี)">
                    </div>
                </div>

                <!-- 3. ประเภทเอกสาร -->
                <div class="row mb-4 align-items-center">
                    <div class="col-md-3 text-md-end">
                        <label class="fw-bold text-secondary">ประเภท</label>
                    </div>
                    <div class="col-md-9">
                        <select name="type_id" class="form-select custom-input">
                            <?php if (!empty($types)): foreach ($types as $type): ?>
                                <option value="<?php echo $type['type_id']; ?>"><?php echo htmlspecialchars($type['type_name']); ?></option>
                            <?php endforeach; else: ?>
                                <option value="1">หนังสือภายนอก</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <!-- 4. ผู้ส่ง -->
                <div class="row mb-4 align-items-center">
                    <div class="col-md-3 text-md-end">
                        <label class="fw-bold text-secondary">ผู้ส่ง</label>
                    </div>
                    <div class="col-md-9">
                        <input type="text" name="sender_name" required class="form-control custom-input" 
                               value="<?php echo htmlspecialchars($_SESSION['fullname'] ?? ''); ?>">
                    </div>
                </div>

                <!-- (ตัดช่องผู้รับออกแล้ว) -->
                <!-- เพื่อป้องกัน Error ฝั่ง API อาจจะส่งค่าว่างไปแทน -->
                <input type="hidden" name="receiver_name" value="-">

                <div class="d-flex justify-content-end mt-5 pt-3 border-top">
                    <button type="reset" class="btn btn-danger rounded-pill px-4 me-2">ยกเลิก</button>
                    <button type="submit" class="btn btn-success rounded-pill px-5" style="background-color: #00E676; border:none; color:black;">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>