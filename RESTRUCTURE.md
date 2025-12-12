# EDE System - โครงสร้างที่ปรับปรุงแล้ว

## สรุปการปรับปรุง

### ✅ สิ่งที่ดำเนินการเสร็จแล้ว

1. **สร้างโครงสร้างแบบ modular**
   - ✅ สร้างโฟลเดอร์ `pages/` สำหรับแยกหน้าต่างๆ (9 ไฟล์)
   - ✅ สร้างโฟลเดอร์ `_scripts/` สำหรับ JavaScript source
   - ✅ ปรับปรุง `index.php` ให้ใช้ switch case routing
   - ✅ สร้างโฟลเดอร์ `_styles/` สำหรับ SCSS

2. **JavaScript Files**
   - ✅ `_scripts/global.js` - ฟังก์ชันที่ใช้ร่วมกัน
   - ✅ `_scripts/dashboard.js` - JavaScript สำหรับ Dashboard
   - ✅ `_scripts/register.js` - JavaScript สำหรับลงทะเบียน
   - ✅ Minified versions อยู่ในโฟลเดอร์ `js/` (ใช้อยู่)

3. **Pages Files ใน pages/** (9 ไฟล์)
   - ✅ `main-menu.php` - หน้าเมนูหลัก
   - ✅ `dashboard-page.php` - หน้า Dashboard (มีเนื้อหา)
   - ✅ `register-page.php` - หน้าลงทะเบียน (มีเนื้อหา)
   - ✅ `tracking-page.php` - หน้าติดตามเอกสาร (มีเนื้อหา)
   - ✅ `report-page.php` - หน้ารายงาน (ยังไม่มีเนื้อหา)
   - ✅ `settings-page.php` - หน้าตั้งค่า (ยังไม่มีเนื้อหา)
   - ✅ `scan-history-page.php` - หน้าประวัติการสแกน (ยังไม่มีเนื้อหา)
   - ✅ `workflow-settings-page.php` - หน้าจัดการสถานะ (ยังไม่มีเนื้อหา)
   - ✅ `page-not-found.php` - หน้า 404

4. **Routing System**
   - ✅ Routing ทำงานแล้ว (switch case ใน index.php)
   - ✅ URL ใช้ parameter `dev` (`index.php?dev=dashboard`)
   - ✅ อัพเดท `includes/sidebar.php` ให้ใช้ลิงก์แบบใหม่

### ⚠️ สิ่งที่ต้องทำต่อ

1. **เพิ่มเนื้อหาหน้าที่ยังไม่เสร็จ**
   - `report-page.php` - ยังต้องเพิ่มโค้ดเต็ม
   - `settings-page.php` - ยังต้องเพิ่มโค้ดเต็ม
   - `scan-history-page.php` - ยังต้องเพิ่มโค้ดเต็ม
   - `workflow-settings-page.php` - ยังต้องเพิ่มโค้ดเต็ม

2. **จัดระเบียบ CSS**
   - รวม inline styles ไปไว้ใน `css/main.min.css`
   - ลบ `<style>` tags ที่ซ้ำซ้อน

3. **ไฟล์เก่าที่ยังอยู่**
   - `dashboard.php`, `register.php`, `tracking.php`, `liff_scan.php`, `settings_form.php`
   - สามารถลบได้หลังจากทดสอบว่าระบบใหม่ทำงานได้แล้ว

4. **JavaScript Files**
   - ตรวจสอบว่า `_scripts/` ใช้ หรือค่อนข้างใช้ไฟล์ใน `js/` (minified files)
   - ปัจจุบันระบบโหลด `js/global.min.js` และ `js/dashboard.min.js` เป็นต้น

### 📁 โครงสร้างปัจจุบัน

```
ede-system/
├── index.php                    # Main routing file (switch case ทำงาน)
├── login.php                    # หน้า login
├── logout.php                   # หน้า logout
├── liff_scan.php                # LIFF Scan (เดิม)
├── settings_form.php            # Settings Form (เดิม)
├── print/                       # พิมพ์ใบปะหน้า
├── _scripts/                    # JavaScript sources (ยังไม่ใช้)
│   ├── global.js
│   ├── dashboard.js
│   └── register.js
├── _styles/                     # SCSS sources
│   └── main.scss
├── pages/                       # Page components ✅ ครบ 9 หน้า
│   ├── main-menu.php
│   ├── dashboard-page.php
│   ├── register-page.php
│   ├── tracking-page.php
│   ├── report-page.php
│   ├── settings-page.php
│   ├── scan-history-page.php
│   ├── workflow-settings-page.php
│   └── page-not-found.php
├── js/                         # Minified JavaScript files (ใช้อยู่)
│   ├── global.min.js
│   ├── dashboard.min.js
│   └── register.min.js
├── css/                        # Compiled CSS
│   └── main.min.css
├── includes/                   # Common includes
│   ├── sidebar.php            # อัพเดทลิงก์แล้ว
│   └── topbar.php
├── api/                        # API endpoints
├── assets/                     # Bootstrap, FontAwesome, SweetAlert2, Select2
│   ├── bootstrap/
│   ├── @fortawesome/
│   ├── sweetalert2/
│   └── select2/
├── config/                     # Configuration files
├── data/                       # JSON data
└── database/                   # Database schemas
```

### 🔗 URL Routing (ทำงานแล้ว)

| หน้า | URL | ไฟล์หน้า | JavaScript |
|------|-----|--------|-----------|
| เมนูหลัก | `index.php` หรือ `index.php?dev=main` | `pages/main-menu.php` | - |
| Dashboard | `index.php?dev=dashboard` | `pages/dashboard-page.php` | `js/dashboard.min.js` |
| ลงทะเบียน | `index.php?dev=register` | `pages/register-page.php` | `js/register.min.js` |
| ติดตาม | `index.php?dev=tracking` | `pages/tracking-page.php` | - |
| รายงาน | `index.php?dev=report` | `pages/report-page.php` | - |
| ตั้งค่า | `index.php?dev=settings` | `pages/settings-page.php` | - |
| ประวัติสแกน | `index.php?dev=scan-history` | `pages/scan-history-page.php` | - |
| จัดการสถานะ | `index.php?dev=workflow-settings` | `pages/workflow-settings-page.php` | - |
| Page Not Found | (default) | `pages/page-not-found.php` | - |

### 📝 หมายเหตุ

- ✅ Routing system ทำงานแล้ว (ใช้ parameter `dev` แทน `page`)
- ✅ Pages ครบแล้ว (9 ไฟล์)
- ✅ JavaScript ที่จำเป็นกำลังโหลด (minified จากโฟลเดอร์ `js/`)
- ⚠️ `_scripts/` และ `_styles/` คือ source files ยังไม่ใช้อยู่ (ใช้ minified files จากโฟลเดอร์ `js/` และ `css/`)
- ⚠️ ไฟล์เก่า (`dashboard.php`, `register.php`, `tracking.php` ฯลฯ) ยังคงอยู่ เสี่ยงความสับสน
- ⏳ ยังต้องทำ: เพิ่มเนื้อหา report, settings, scan-history, workflow-settings และจัดระเบียบ CSS
