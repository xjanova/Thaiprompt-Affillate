# Thaiprompt MLM - WordPress MLM Plugin

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.8+-green.svg)
![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)
![License](https://img.shields.io/badge/license-GPL--2.0-red.svg)

ระบบจัดการ MLM (Multi-Level Marketing) ครบวงจรสำหรับ WordPress ที่มีความสวยงาม ทันสมัย และใช้งานง่าย พร้อมผังสายงานแบบ GSAP Animation ระดับโปรแกรมชั้นนำ

## ✨ Features

### 🎯 Core Features
- **ระบบ MLM ครบวงจร** - Binary tree, Unilevel, และ Matrix compensation plans
- **Genealogy Tree with GSAP** - ผังสายงานสวยงามพร้อม Animation แบบ GSAP
- **ระบบ Wallet** - กระเป๋าเงินดิจิทัลในตัว พร้อมระบบถอนเงิน
- **ระบบ Rank** - ระดับยศพร้อมโบนัส
- **Multi-Commission Types** - Level Commission, Fast Start Bonus, Binary Bonus, Rank Bonus
- **Flexible Placement** - Auto placement, Left spillover, Right spillover, Balanced
- **Product-Level Settings** - ตั้งค่าค่าคอมมิชชั่นแยกตามสินค้า

### 🔌 Integrations
- **WooCommerce** - ทำงานร่วมกับ WooCommerce อย่างสมบูรณ์
- **Dokan** - รองรับระบบ Multi-vendor ของ Dokan
- **WordPress Users** - ผสานรวมกับระบบ user ของ WordPress

### 💰 Commission System
- Level Commissions (ระดับ 1-10 หรือมากกว่า)
- Fast Start Bonus (โบนัสเริ่มต้นเร็ว)
- Binary Commission (คอมมิชชั่นแบบไบนารี่)
- Rank Achievement Bonus (โบนัสจากการขึ้นยศ)
- Monthly Rank Bonus (โบนัสรายเดือนตามยศ)

### 📊 Dashboard & Reports
- User Dashboard พร้อมสถิติแบบ Real-time
- Admin Dashboard พร้อม Charts และกราฟ
- Commission Reports
- Network Reports
- Sales Reports

### 🎨 UI/UX
- Modern และสวยงาม
- Responsive Design
- GSAP Animations
- Card-based Layout
- Gradient Backgrounds

## 📋 Requirements

- WordPress 5.8 หรือสูงกว่า
- PHP 7.4 หรือสูงกว่า
- WooCommerce 5.0 หรือสูงกว่า (แนะนำ)
- MySQL 5.7 หรือสูงกว่า

## 🚀 Installation

### วิธีที่ 1: Upload ผ่าน WordPress Admin

1. ดาวน์โหลด Plugin เป็นไฟล์ ZIP
2. ไปที่ WordPress Admin > Plugins > Add New
3. คลิก "Upload Plugin" และเลือกไฟล์ ZIP
4. คลิก "Install Now" และ "Activate"

### วิธีที่ 2: FTP Upload

1. แตกไฟล์ ZIP
2. อัพโหลดโฟลเดอร์ `thaiprompt-mlm` ไปที่ `/wp-content/plugins/`
3. เปิดใช้งาน Plugin ผ่าน WordPress Admin

### วิธีที่ 3: Git Clone

```bash
cd wp-content/plugins/
git clone [repository-url] thaiprompt-mlm
```

## ⚙️ Configuration

### การตั้งค่าเบื้องต้น

1. **เปิดใช้งาน Plugin**
   - ไปที่ WordPress Admin > Plugins
   - เปิดใช้งาน "Thaiprompt MLM"

2. **ตั้งค่า MLM Settings**
   - ไปที่ Thaiprompt MLM > Settings
   - กำหนด:
     - Placement Type (Auto/Left/Right/Balanced)
     - Max Commission Level
     - Commission Percentages for each level
     - Fast Start Bonus settings
     - Minimum Payout amount

3. **สร้าง Ranks**
   - Ranks พื้นฐานจะถูกสร้างอัตโนมัติเมื่อ activate
   - สามารถแก้ไขได้ที่ Thaiprompt MLM > Ranks

4. **ตั้งค่า Product**
   - แก้ไขสินค้าใน WooCommerce
   - เลื่อนไปที่ "MLM Settings" tab
   - เปิดใช้งาน MLM และกำหนดค่าคอมมิชชั่น

### การตั้งค่า WooCommerce Integration

Plugin จะ integrate กับ WooCommerce อัตโนมัติเมื่อตรวจพบ WooCommerce

Features:
- เพิ่มฟิลด์ referral code ในหน้า registration
- คำนวณค่าคอมมิชชั่นอัตโนมัติเมื่อ order completed
- เพิ่ม MLM tabs ใน My Account page

## 📱 Shortcodes

### Dashboard
```
[mlm_dashboard]
```
แสดง MLM Dashboard พร้อมสถิติทั้งหมด

### Genealogy Tree
```
[mlm_genealogy user_id="123" max_depth="5"]
```
แสดงผังสายงาน (รองรับ GSAP Animation)

### Wallet
```
[mlm_wallet]
```
แสดงกระเป๋าเงินและประวัติการทำรายการ

### Referral Link
```
[mlm_referral_link]
```
แสดงลิงก์แนะนำพร้อมปุ่มคัดลอก

### Team Stats
```
[mlm_team_stats]
```
แสดงสถิติทีม

### Rank Progress
```
[mlm_rank_progress]
```
แสดงความคืบหน้าของ rank

### Commissions
```
[mlm_commissions]
```
แสดงประวัติค่าคอมมิชชั่น

### Leaderboard
```
[mlm_leaderboard limit="50"]
```
แสดง Leaderboard

## 🎓 Usage

### สำหรับ Admin

#### การเพิ่ม User เข้าระบบ MLM
1. ไปที่ Thaiprompt MLM > Network
2. คลิก "Add User to Network"
3. เลือก User และ Sponsor
4. ระบบจะ place อัตโนมัติตาม placement type ที่ตั้งค่าไว้

#### การอนุมัติ Commission
1. ไปที่ Thaiprompt MLM > Commissions
2. เลือก Commission ที่ต้องการอนุมัติ
3. คลิก "Approve"

#### การอนุมัติการถอนเงิน
1. ไปที่ Thaiprompt MLM > Wallet & Withdrawals
2. ดู withdrawal requests
3. คลิก "Approve" หรือ "Reject"

#### การจัดการ Ranks
1. ไปที่ Thaiprompt MLM > Ranks
2. แก้ไข rank requirements
3. กำหนด bonus และสี

### สำหรับ Users

#### การสมัครสมาชิก
1. คลิกลิงก์แนะนำจากผู้อื่น
2. สมัครสมาชิกผ่านหน้า registration
3. ระบบจะบันทึก sponsor อัตโนมัติ

#### การดู Dashboard
1. Login เข้าระบบ
2. ไปที่ My Account > MLM Dashboard
3. ดูสถิติและข้อมูลต่างๆ

#### การถอนเงิน
1. ไปที่ My Account > My Wallet
2. คลิก "Withdraw"
3. กรอกข้อมูลและจำนวนเงิน
4. รอ admin อนุมัติ

## 🗄️ Database Schema

Plugin จะสร้าง tables ต่อไปนี้:

- `wp_thaiprompt_mlm_network` - โครงสร้าง MLM network
- `wp_thaiprompt_mlm_commissions` - บันทึก commissions
- `wp_thaiprompt_mlm_wallet` - กระเป๋าเงิน
- `wp_thaiprompt_mlm_transactions` - ประวัติการทำรายการ
- `wp_thaiprompt_mlm_ranks` - ระดับยศ
- `wp_thaiprompt_mlm_user_ranks` - ระดับยศของ users
- `wp_thaiprompt_mlm_product_settings` - การตั้งค่า MLM ของสินค้า
- `wp_thaiprompt_mlm_withdrawals` - คำขอถอนเงิน

## 🎨 Customization

### CSS Customization

แก้ไขไฟล์:
- `public/css/thaiprompt-mlm-public.css` - Frontend styles
- `admin/css/thaiprompt-mlm-admin.css` - Admin styles

### JavaScript Customization

แก้ไขไฟล์:
- `public/js/thaiprompt-mlm-public.js` - Frontend JS
- `public/js/thaiprompt-mlm-genealogy.js` - Genealogy Tree with GSAP
- `admin/js/thaiprompt-mlm-admin.js` - Admin JS

### Template Customization

Copy template files จาก:
```
plugins/thaiprompt-mlm/public/partials/
```
ไปยัง:
```
themes/your-theme/thaiprompt-mlm/
```

## 🔧 Hooks & Filters

### Actions

```php
// User registered in MLM
do_action('thaiprompt_mlm_user_registered', $user_id, $sponsor_id, $placement_id, $position);

// Order processed
do_action('thaiprompt_mlm_order_processed', $order_id, $customer_id);

// Commission approved
do_action('thaiprompt_mlm_commission_approved', $commission_id, $commission);

// Rank changed
do_action('thaiprompt_mlm_rank_changed', $user_id, $old_rank, $new_rank);

// Withdrawal requested
do_action('thaiprompt_mlm_withdrawal_requested', $user_id, $amount, $withdrawal_id);
```

### Filters

```php
// Customize referral parameter
$referral_param = apply_filters('thaiprompt_mlm_referral_param', 'ref');

// Customize commission calculation
$commission_amount = apply_filters('thaiprompt_mlm_commission_amount', $amount, $level, $user_id);
```

## 🐛 Troubleshooting

### Commission ไม่ถูกคำนวณ
- ตรวจสอบว่า user มี sponsor
- ตรวจสอบว่า product เปิดใช้งาน MLM
- ตรวจสอบ order status (ต้องเป็น completed/processing)

### Genealogy Tree ไม่แสดง
- ตรวจสอบว่า GSAP library โหลดสำเร็จ
- ตรวจสอบ console errors
- ลอง reload หน้าเว็บ

### Withdrawal ไม่สำเร็จ
- ตรวจสอบ minimum withdrawal amount
- ตรวจสอบยอดเงินคงเหลือ
- ตรวจสอบสถานะ withdrawal request

## 📝 Changelog

### Version 1.0.0
- Initial release
- Complete MLM system
- GSAP genealogy tree
- Wallet system
- WooCommerce & Dokan integration
- Multiple commission types
- Rank system
- Admin dashboard
- User dashboard

## 🤝 Support

หากมีปัญหาหรือต้องการความช่วยเหลือ:
- Email: support@thaiprompt.com
- Documentation: https://thaiprompt.com/docs/mlm-plugin

## 📄 License

GPL-2.0 License

Copyright (c) 2024 Thaiprompt

## 👨‍💻 Author

Created by **Thaiprompt Team**

---

**Note:** Plugin นี้ต้องใช้ร่วมกับ WooCommerce เพื่อให้ได้ประสิทธิภาพสูงสุด
