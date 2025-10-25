# Thaiprompt MLM Plugin

![Version](https://img.shields.io/badge/version-2.0.0-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.8+-green.svg)
![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)
![License](https://img.shields.io/badge/license-GPL--2.0-red.svg)

ระบบจัดการ MLM (Multi-Level Marketing) ครบวงจรสำหรับ WordPress พร้อมระบบ LINE Official Account, AI Chatbot, Rich Menu Builder, Flex Message Builder, Genealogy Tree แบบ 3D, ระบบ Wallet และ Landing Page Builder

---

## 📋 สารบัญ

- [คุณสมบัติ](#-คุณสมบัติ)
- [ความต้องการของระบบ](#-ความต้องการของระบบ)
- [การติดตั้ง](#-การติดตั้ง)
- [การตั้งค่า](#-การตั้งค่า)
- [LINE OA Integration](#-line-oa-integration)
- [AI Chatbot](#-ai-chatbot)
- [Rich Menu Builder](#-rich-menu-builder)
- [Flex Message Builder](#-flex-message-builder)
- [Wallet System](#-wallet-system)
- [Landing Page Builder](#-landing-page-builder)
- [FAQ](#-faq)
- [Changelog](#-changelog)
- [Support](#-support)

---

## ✨ คุณสมบัติ

### 🎯 Core Features

#### 1. **MLM Network Management**
- ✅ Binary Tree Structure (แบบต้นไม้ทวิภาค)
- ✅ Interactive 3D Genealogy Tree (GSAP Animation)
- ✅ Auto Placement System (วางตำแหน่งอัตโนมัติ)
- ✅ Spillover Management (ระบบการล้น)
- ✅ Direct & Indirect Referrals Tracking
- ✅ Real-time Network Statistics

#### 2. **Commission System**
- ✅ Multi-level Commission (คอมมิชชั่นหลายระดับ)
- ✅ Direct Sales Commission (คอมมิชชั่นขายตรง)
- ✅ Binary Bonus (โบนัสคู่)
- ✅ Matching Bonus (โบนัสแนะนำ)
- ✅ Generation Bonus (โบนัสตามรุ่น)
- ✅ Flexible Commission Rules
- ✅ Auto Commission Calculation
- ✅ WooCommerce & Dokan Integration

#### 3. **Rank Management**
- ✅ Customizable Rank System
- ✅ Rank Requirements (Sales, Team, etc.)
- ✅ Rank Progress Tracking
- ✅ Auto Rank Promotion
- ✅ Rank Badges & Colors
- ✅ Rank-based Commissions

#### 4. **Wallet System**
- ✅ Virtual Wallet for Each Member
- ✅ Balance Tracking (Available & Pending)
- ✅ Withdrawal Management
- ✅ Transaction History
- ✅ Admin Approval System
- ✅ **NEW:** WooCommerce Wallet Top-up
- ✅ Commission Auto-deposit
- ✅ Payment Gateway Integration

#### 5. **Landing Page Builder**
- ✅ Visual Landing Page Creator
- ✅ Multiple Templates
- ✅ Image Upload (3 images support)
- ✅ Custom Headlines & Descriptions
- ✅ **NEW:** Preview Mode (ทุกสถานะ)
- ✅ **NEW:** Approval System
- ✅ Analytics (Views & Conversions)
- ✅ Referral Link Integration
- ✅ Social Sharing Buttons

### 🤖 LINE OA Integration (NEW in 2.0)

#### 6. **LINE Official Account**
- ✅ Complete LINE Messaging API Integration
- ✅ Webhook Handler with Signature Validation
- ✅ Auto User Registration from LINE Profile
- ✅ LINE User ID Tracking
- ✅ Profile Picture Import
- ✅ Push & Reply Messages
- ✅ Event Handling (Follow, Unfollow, Join, Leave)

#### 7. **AI Chatbot** 🤖
- ✅ **ChatGPT Integration** (OpenAI)
  - GPT-4o, GPT-4o-mini, GPT-4-turbo
- ✅ **Google Gemini Integration**
  - Gemini 2.0 Flash, 1.5 Flash, 1.5 Pro
- ✅ **DeepSeek Integration**
  - DeepSeek Chat
- ✅ Context-aware Conversations
- ✅ Conversation History (10 exchanges)
- ✅ Custom System Prompts
- ✅ Thai/English Auto-detection
- ✅ Fallback Messages
- ✅ Error Handling & Logging

#### 8. **Rich Menu Builder** 🎨
- ✅ Visual Rich Menu Creator
- ✅ 4 Predefined Templates:
  - 2 Buttons (Horizontal)
  - 3 Buttons (Horizontal)
  - 4 Buttons (2x2 Grid)
  - 6 Buttons (3x2 Grid) ⭐ Most Popular
- ✅ Image Upload with Preview
- ✅ Button Actions (Message, URL, Postback)
- ✅ Area Coordinate System
- ✅ Rich Menu Management (List, Delete, Set Default)
- ✅ Figma Template Links

#### 9. **Flex Message Builder** 💬
- ✅ 5 Professional Templates:
  - **Product Card** - Showcase products
  - **Service Card** - Promote services
  - **Announcement** - Important messages
  - **Referral Card** - Share referrals
  - **Profile Card** - Member profiles
- ✅ Dynamic Form Fields
- ✅ JSON Generator
- ✅ LINE Simulator Preview
- ✅ Test Message Sending
- ✅ Copy to Clipboard

### 🎨 Portal Features

#### 10. **Member Portal**
- ✅ Modern 3D Glassmorphism Design
- ✅ Responsive Mobile Layout
- ✅ **NEW:** Vertical Hamburger Menu (Mobile)
- ✅ Customizable Portal Branding
- ✅ Custom Logo Support
- ✅ Custom Header & Subtitle
- ✅ Slideshow Integration
- ✅ 7 Main Tabs:
  - Dashboard
  - Network Tree
  - Referrals
  - Wallet
  - Commissions
  - Rank Progress
  - Landing Page

### 🔗 Integrations

#### 11. **WooCommerce**
- ✅ Order Commission Calculation
- ✅ Product-based Commissions
- ✅ Cart Integration
- ✅ **NEW:** Wallet Top-up Products
  - Hidden from Shop (Private)
  - Excluded from Commissions
  - Auto Balance Update
  - 6 Preset Amounts (100-10,000 THB)

#### 12. **Dokan Multi-vendor**
- ✅ Vendor Commission Split
- ✅ Multi-vendor Support
- ✅ Vendor-specific Rules

---

## 🔧 ความต้องการของระบบ

### Server Requirements
- **PHP:** 7.4 or higher (8.0+ recommended)
- **WordPress:** 5.8 or higher
- **MySQL:** 5.7 or higher / MariaDB 10.3+
- **Memory Limit:** 256MB+ (512MB recommended)
- **Max Execution Time:** 300 seconds+

### Required WordPress Plugins
- None (Standalone plugin)

### Optional Plugins (for extended features)
- **WooCommerce** 6.0+ - For e-commerce integration & wallet top-up
- **Dokan** 3.0+ - For multi-vendor support

### API Keys (Optional)
For AI Chatbot features:
- **OpenAI API Key** - For ChatGPT integration
- **Google Gemini API Key** - For Gemini integration
- **DeepSeek API Key** - For DeepSeek integration

For LINE OA features:
- **LINE Developer Account** - For LINE Official Account
- **LINE Channel ID, Secret & Access Token**

---

## 💾 การติดตั้ง

### Method 1: Upload via WordPress Admin

1. ดาวน์โหลดไฟล์ plugin (`thaiprompt-mlm.zip`)
2. ไปที่ **WordPress Admin > Plugins > Add New**
3. คลิก **Upload Plugin**
4. เลือกไฟล์ `thaiprompt-mlm.zip`
5. คลิก **Install Now**
6. คลิก **Activate Plugin**

### Method 2: FTP Upload

1. แตกไฟล์ `thaiprompt-mlm.zip`
2. อัพโหลดโฟลเดอร์ `thaiprompt-mlm` ไปที่ `/wp-content/plugins/`
3. ไปที่ **WordPress Admin > Plugins**
4. หา **Thaiprompt MLM** และคลิก **Activate**

### Post-Installation

หลังจากติดตั้ง plugin จะสร้าง:
- ✅ Database tables สำหรับ MLM
- ✅ Default settings
- ✅ Portal page (`/mlm-portal/`)
- ✅ Landing page rewrite rules

---

## ⚙️ การตั้งค่า

### 1. Basic Settings

ไปที่ **MLM > Settings**

#### General
- **Company Name** - ชื่อบริษัท
- **Commission Rules** - กฎการคำนวณคอมมิชชั่น
- **Auto Placement** - เปิด/ปิดการวางตำแหน่งอัตโนมัติ

#### Portal Customization
- **Portal Logo** - โลโก้ที่แสดงใน Portal
- **Header Text** - ข้อความหัวเรื่อง (รองรับ `{name}`)
- **Subtitle** - ข้อความรอง
- **Slideshow Images** - รูปภาพสำหรับ Slideshow (สูงสุด 5 รูป)
- **Slideshow Speed** - ความเร็วในการเปลี่ยนรูป (วินาที)

### 2. LINE OA Setup

ไปที่ **MLM > LINE Settings**

#### Step 1: Create LINE Official Account
1. ไปที่ [LINE Developers Console](https://developers.line.biz/)
2. Create new **Provider**
3. Create **Messaging API Channel**

#### Step 2: Get Credentials
1. Copy **Channel ID**
2. Copy **Channel Secret**
3. Generate **Channel Access Token** (Long-lived)
4. Copy **OA ID** (เช่น @123abcde)

#### Step 3: Configure Plugin
1. วาง credentials ในหน้า LINE Settings
2. เปิดใช้งาน **Webhook**
3. เปิดใช้งาน **Auto Registration**
4. ตั้งค่า **Welcome Message**

#### Step 4: Set Webhook URL
1. Copy Webhook URL จากหน้า LINE Settings
2. ไปที่ LINE Developers Console
3. Paste URL ในส่วน **Webhook URL**
4. เปิดใช้งาน **Webhook**
5. ปิด **Auto-reply messages** (ถ้าไม่ต้องการ)

#### Step 5: Test Connection
1. คลิก **Test Connection** ในหน้า LINE Settings
2. ตรวจสอบว่าเชื่อมต่อสำเร็จ ✅

### 3. AI Chatbot Setup

ไปที่ **MLM > LINE Settings > AI Integration**

#### Choose AI Provider
- **Disabled** - ไม่ใช้ AI
- **ChatGPT** - OpenAI GPT models
- **Gemini** - Google Gemini models
- **DeepSeek** - DeepSeek Chat

#### ChatGPT Setup
1. Get API Key from [OpenAI Platform](https://platform.openai.com/api-keys)
2. Paste API Key
3. Select Model (GPT-4o-mini recommended)
4. Configure System Prompt

#### Gemini Setup
1. Get API Key from [Google AI Studio](https://aistudio.google.com/app/apikey)
2. Paste API Key
3. Select Model (Gemini 2.0 Flash recommended)
4. Configure System Prompt

#### DeepSeek Setup
1. Get API Key from [DeepSeek Platform](https://platform.deepseek.com/api_keys)
2. Paste API Key
3. Configure System Prompt

---

## 💬 LINE OA Integration

### Features

#### Auto Registration
เมื่อผู้ใช้ Add Friend:
1. ระบบดึง LINE Profile (ชื่อ, รูป)
2. สร้าง WordPress Account อัตโนมัติ
3. ดาวน์โหลดรูปโปรไฟล์
4. เก็บ LINE User ID
5. ส่งข้อความต้อนรับ

#### Bot Commands
- `/help` - ดูคำสั่งทั้งหมด
- `/profile` - ดูข้อมูลสมาชิก
- `/referral` - ดูลิงก์แนะนำและสถิติ

---

## 🤖 AI Chatbot

### Supported Providers

#### 1. ChatGPT (OpenAI)
**Models:**
- `gpt-4o` - Latest GPT-4 Optimized
- `gpt-4o-mini` - Faster, Cost-effective ⭐ Recommended
- `gpt-4-turbo` - GPT-4 Turbo

**Best For:**
- Natural conversations
- Complex queries
- Multi-language support

#### 2. Google Gemini
**Models:**
- `gemini-2.0-flash-exp` - Experimental 2.0 ⭐ Recommended
- `gemini-1.5-flash` - Fast responses
- `gemini-1.5-pro` - Advanced reasoning

**Best For:**
- Long conversations
- Context understanding
- Free tier available

#### 3. DeepSeek
**Models:**
- `deepseek-chat` - General purpose

**Best For:**
- Cost-effective solution
- Thai language support
- Technical queries

### Conversation Features

- **Context Memory:** Remembers last 10 exchanges
- **Context Timeout:** 1 hour
- **Language Detection:** Auto Thai/English
- **Concise Responses:** Optimized for LINE chat

---

## 🎨 Rich Menu Builder

### Templates

#### 1. 2 Buttons (Horizontal)
- **Size:** 2500 x 843 px
- **Layout:** Two buttons side by side

#### 2. 3 Buttons (Horizontal)
- **Size:** 2500 x 843 px
- **Layout:** Three buttons in a row

#### 3. 4 Buttons (2x2 Grid)
- **Size:** 2500 x 1686 px
- **Layout:** 2 rows, 2 columns

#### 4. 6 Buttons (3x2 Grid) ⭐
- **Size:** 2500 x 1686 px
- **Layout:** 2 rows, 3 columns (Most Popular)

### Button Actions

- **Send Message** - ส่งข้อความ text
- **Open URL** - เปิด website/landing page
- **Postback** - ส่งข้อมูลแบบซ่อน

---

## 💬 Flex Message Builder

### Templates

#### 1. Product Card
- Showcase products with images and pricing

#### 2. Service Card
- Promote services with feature lists

#### 3. Announcement
- Important messages with icons

#### 4. Referral Card
- Share referral links with benefits

#### 5. Profile Card
- Member profiles with stats and rank

---

## 💰 Wallet System

### Features

#### Wallet Top-up (NEW in 2.0)

**Preset Amounts:**
- 100 THB
- 500 THB
- 1,000 THB
- 2,000 THB
- 5,000 THB
- 10,000 THB

**Process:**
1. Member clicks top-up amount
2. Redirects to WooCommerce Checkout
3. Completes payment
4. Balance updates automatically
5. Transaction logged

**Hidden Products:**
- Products are **Private**
- Not visible in shop
- Excluded from search
- No commission calculated

---

## 🎨 Landing Page Builder

### NEW in 2.0: Preview & Approval

#### Preview Mode
- **Access:** Owner + Admin can preview
- **URL:** `?preview=true` parameter
- **Status Banner:** Shows current status
- **View Tracking:** Doesn't count preview views

#### Approval System

**Status:**
- ⏳ **Pending** (Orange) - Waiting for approval
- ✅ **Approved** (Green) - Live and public
- ❌ **Rejected** (Red) - Not approved

---

## ❓ FAQ

### General

**Q: รองรับภาษาไทยไหม?**
A: ใช่ รองรับภาษาไทยเต็มรูปแบบ

**Q: ต้องมี WooCommerce ไหม?**
A: ไม่จำเป็น แต่แนะนำให้ติดตั้งเพื่อใช้ฟีเจอร์เติมเงิน Wallet

**Q: รองรับ Dokan ไหม?**
A: ใช่ รองรับ Dokan Multi-vendor

### LINE OA

**Q: ต้องมี LINE Official Account แบบ Verified ไหม?**
A: ไม่จำเป็น แบบ Unverified ใช้งานได้เหมือนกัน

**Q: ฟรีไหม?**
A: LINE OA มี Free plan แต่จำกัด 500 messages/month

### AI Chatbot

**Q: AI ไหนดีที่สุด?**
A:
- **ChatGPT (GPT-4o-mini)** - Balance ระหว่างราคาและคุณภาพ ⭐
- **Gemini 2.0 Flash** - มี Free tier
- **DeepSeek** - ราคาถูกที่สุด

---

## 📝 Changelog

### Version 2.0.0 (2025-01-XX)

#### ✨ NEW Features
- **LINE Official Account Integration**
  - LINE Bot with Messaging API
  - Webhook handler
  - Auto user registration
  - Profile import
- **AI Chatbot**
  - ChatGPT (OpenAI) integration
  - Google Gemini integration
  - DeepSeek integration
  - Context-aware conversations
  - Custom system prompts
- **Rich Menu Builder**
  - 4 templates (2, 3, 4, 6 buttons)
  - Visual builder
  - Image upload
  - Button actions
- **Flex Message Builder**
  - 5 professional templates
  - JSON generator
  - Test messaging
- **Wallet Top-up**
  - WooCommerce integration
  - 6 preset amounts
  - Hidden products
  - Auto balance update
- **Landing Page Improvements**
  - Preview mode
  - Approval system
  - Status indicators
  - Owner verification

#### 🎨 Improvements
- **Portal UI** - Vertical hamburger menu on mobile
- **Performance** - Optimized queries
- **Security** - Enhanced validation

### Version 1.7.1 (Previous)
- Landing page builder
- Portal slideshow
- Custom branding
- Initial release features

---

## 🆘 Support

### Get Help
- **Email:** support@thaiprompt.com
- **Website:** https://thaiprompt.com

### Resources
- [LINE Developers](https://developers.line.biz/)
- [OpenAI Documentation](https://platform.openai.com/docs)
- [Google AI Studio](https://ai.google.dev/)
- [Flex Message Simulator](https://developers.line.biz/flex-simulator/)

---

## 📜 License

This plugin is licensed under the GPL v2 or later.

Copyright (C) 2025 Thaiprompt

---

## 🙏 Credits

### Built With
- WordPress
- WooCommerce
- GSAP (Animation)
- LINE Messaging API
- OpenAI API
- Google Gemini API
- DeepSeek API

### Author
**Thaiprompt**
Website: [https://thaiprompt.com](https://thaiprompt.com)

---

**Made with ❤️ in Thailand 🇹🇭**
