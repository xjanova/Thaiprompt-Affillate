# Changelog

All notable changes to Thaiprompt MLM Plugin will be documented in this file.

## [1.7.0] - 2024-01-24 (In Progress)

### ✨ New Features - Landing Page Builder (Phase 1)

Landing page builder with admin approval system (partial implementation).

### Added - Landing Page System
- ✅ **Database Table** - Landing pages table with approval fields
- ✅ **Landing Page Builder UI** - Complete form in Portal
- ✅ **Image Upload** - Support for up to 3 images (max 5MB each)
- ✅ **Form Fields** - Title, headline, description, CTA text
- ✅ **Status System** - Pending, approved, rejected states
- ✅ **Preview Display** - Live preview of landing page
- ✅ **AJAX Handler** - Save/update with image upload
- ✅ **Edit Functionality** - Edit existing landing pages
- ✅ **Validation** - File type and size validation (both frontend & backend)
- ✅ **JavaScript** - Form submission, image preview, UI interactions
- ✅ **Image Preview** - Client-side preview before upload
- ✅ **Loading States** - Button states during form submission
- ✅ **Admin Approval Page** - Complete admin interface for reviewing pages
- ✅ **Admin Menu Item** - "Landing Pages" in admin sidebar
- ✅ **Statistics Dashboard** - Pending, approved, rejected counts
- ✅ **Preview Cards** - Grid layout with image previews
- ✅ **Approve/Reject Actions** - AJAX-powered approval workflow
- ✅ **Admin Notes** - Add notes when approving/rejecting
- ✅ **Status Filtering** - Filter by pending, approved, rejected
- ✅ **Pagination** - Handle large numbers of landing pages

### Pending Implementation
- ⏳ **Public Template** - Frontend landing page display
- ⏳ **Notifications** - Email notifications for approval/rejection
- ⏳ **Analytics** - View and conversion tracking
- ⏳ **Landing Page URL** - Public URL routing system

### Technical Implementation
- New table: wp_thaiprompt_mlm_landing_pages
- AJAX endpoints: mlm_save_landing_page, thaiprompt_mlm_approve_landing_page, thaiprompt_mlm_reject_landing_page
- Image upload via wp_handle_upload()
- Status workflow: pending → approved/rejected
- Max 3 images per landing page (5MB each)
- Image validation (type, size) - frontend & backend
- jQuery form submission with FormData
- FileReader API for image preview
- Client-side validation before upload
- Loading states and user feedback
- Admin menu integration
- Grid-based admin UI with modal dialogs
- Statistics tracking (views, conversions)
- Pagination support for admin pages

### Next Steps (v1.7.1+)
1. Create public landing page template
2. Add landing page URL routing system
3. Implement email notifications
4. Add analytics tracking dashboard
5. SEO optimization for landing pages

## [1.6.0] - 2024-01-24

### ✨ New Features - Mobile First & Referral System

Major responsive redesign with modern 3D UI and advanced referral system.

### Added - Mobile Responsive
- ✅ **Hamburger Menu** - Slide-in navigation for mobile devices
- ✅ **Mobile Menu Overlay** - Dark overlay with blur effect
- ✅ **Responsive Grid** - Adaptive layouts for all screen sizes
- ✅ **Touch Optimized** - Larger touch targets for mobile
- ✅ **Safe Area Support** - iPhone notch support
- ✅ **No Horizontal Scroll** - Prevents overflow on small screens

### Added - Referral Code System
- ✅ **Unique Referral Codes** - Each user gets unique code (not user ID)
- ✅ **Code Generator** - Auto-generates memorable codes
- ✅ **Sponsor Info Display** - Shows sponsor name and code
- ✅ **QR Code Generation** - Google Charts API integration
- ✅ **QR Code Download** - One-click download functionality
- ✅ **Copy Code Button** - Quick copy referral code
- ✅ **Session Tracking** - Tracks referrals via session/cookie

### Added - 3D Modern Design
- ✅ **Glassmorphism Effects** - Modern frosted glass UI
- ✅ **3D Card Transforms** - Perspective and rotation effects
- ✅ **Gradient Buttons** - Smooth gradient animations
- ✅ **Shadow Depth** - Multi-layer shadows for depth
- ✅ **Smooth Transitions** - Cubic bezier animations
- ✅ **Hover Effects** - Interactive 3D transformations

### Changed - Portal UI
- 🔄 **Header Layout** - Improved mobile-friendly header
- 🔄 **Button Icons** - Added icons for better UX
- 🔄 **Network Tab** - Complete redesign with code/QR display
- 🔄 **Sidebar Navigation** - Mobile slide-in behavior
- 🔄 **Card Layouts** - Responsive grid systems

### Technical Implementation
- New Referral class for code management
- Google Charts API for QR codes
- CSS3 transforms and animations
- Mobile-first CSS approach
- Session and cookie-based tracking
- Touch event optimizations

### Known Limitations
- Landing Page Builder - Planned for v1.7.0
- Admin Approval System - Planned for v1.7.0

## [1.5.0] - 2024-01-23

### ✨ New Features - Portal Customization & Rank Management

Major update adding portal customization and complete rank management system.

### Added - Portal Settings
- ✅ **Portal Logo Upload** - Custom logo for portal header
- ✅ **Header Text Customization** - Change portal title and subtitle
- ✅ **Dynamic Subtitle** - Use {name} placeholder for user's name
- ✅ **Portal Slideshow** - Upload multiple images for dashboard slideshow
- ✅ **Slideshow Controls** - Configurable speed and navigation
- ✅ **Image Preview** - See uploaded images in settings

### Added - Rank Management
- ✅ **Add New Rank** - Complete form for creating new ranks
- ✅ **Edit Rank** - Modify existing rank settings
- ✅ **Delete Rank** - Remove ranks with confirmation
- ✅ **Rank Color Picker** - Visual color selection
- ✅ **Rank Requirements** - Personal sales, group sales, active legs
- ✅ **Rank Bonuses** - Percentage bonus and achievement bonus
- ✅ **Rank Status** - Active/Inactive toggle
- ✅ **Auto Order** - Automatic rank_order suggestion

### Changed - Portal Template
- 🔄 **Dynamic Header** - Uses settings for logo and text
- 🔄 **Slideshow Integration** - Shows slideshow when enabled
- 🔄 **Responsive Design** - Mobile-friendly slideshow

### Changed - Admin UI
- 🔄 **Ranks Page** - Completely redesigned with Add/Edit/Delete
- 🔄 **Settings Page** - Added Portal Settings section
- 🔄 **Form Validation** - Required fields and data validation

### Technical Details
- Portal settings stored in wp_options table
- Logo and slideshow images uploaded via wp_handle_upload()
- Rank CRUD operations with wpdb
- Nonce verification for all forms
- Responsive slideshow with vanilla JavaScript
- Auto-advance slideshow with configurable speed

## [1.4.0] - 2024-01-23

### ✨ New Features - Native Genealogy Tree
Replaced shortcode-based genealogy with native portal implementation.

### Added
- ✅ **Native Genealogy Tree** - Built directly into portal without shortcodes
- ✅ **Interactive Tree Controls** - User selection and depth controls
- ✅ **AJAX Tree Loading** - Dynamic tree loading with animations
- ✅ **Public AJAX Handler** - Secure genealogy data endpoint for logged-in users
- ✅ **Modern Tree Design** - Glassmorphism cards with connection lines
- ✅ **Tree Animations** - Smooth fade-in and scale effects
- ✅ **Responsive Tree Layout** - Mobile-friendly column layout

### Removed
- ❌ **[mlm_genealogy] Shortcode** - No longer needed

### Changed
- 🔄 **Portal Template** - Updated genealogy tab with native implementation
- 🔄 **Portal JavaScript** - Added tree rendering and AJAX methods
- 🔄 **Portal CSS** - Added comprehensive tree styles

### Technical Details
- Tree data fetched via `wp_ajax_thaiprompt_mlm_get_genealogy_public`
- Security: Users can only view their own tree or upline
- Tree nodes show: name, level, personal/group sales, left/right counts
- Supports up to 10 levels deep
- Auto-loads on tab switch

## [1.3.0] - 2024-01-22

### ⚠️ BREAKING CHANGES - Portal-Only Focus
Major cleanup removing all non-portal functionality. Plugin now focuses exclusively on the MLM Portal.

### Removed
- ❌ **All Shortcodes** - Removed mlm_dashboard, mlm_genealogy, mlm_wallet, mlm_network, etc.
- ❌ **Old Partial Files** - Removed dashboard.php, genealogy.php, network.php, wallet.php
- ❌ **Old Public Assets** - Removed thaiprompt-mlm-public.css/js, genealogy.js
- ❌ **WooCommerce Endpoints** - Removed mlm-dashboard, mlm-network, mlm-wallet
- ❌ **Auto-Created Pages** - Only creates MLM Portal page now (7 pages removed)

### Kept & Improved
- ✅ **MLM Portal** - Complete portal with all MLM features
- ✅ **Portal Assets** - Portal CSS/JS only
- ✅ **Portal Partials** - commissions.php, rank-progress.php, leaderboard.php
- ✅ **Core Classes** - All business logic intact
- ✅ **Admin Interface** - Complete admin panel unchanged
- ✅ **Integrations** - WooCommerce/Dokan fully functional

### Benefits
- 🎯 **Focused** - Single portal interface
- ⚡ **Faster** - No loading unused assets
- 🧹 **Cleaner** - Removed 1000+ lines of code
- 🔧 **Maintainable** - Single interface to maintain

## [1.2.1] - 2024-01-21

### Fixed
- 🐛 **Portal Tab Navigation** - Fixed portal menu tabs not working when clicked
  - Added vanilla JavaScript fallback for tab navigation
  - Improved reliability when jQuery loading is delayed
  - Added console logging for debugging
  - Tabs now switch smoothly with fade animations
- 🐛 **Missing Database Method** - Added get_all_ranks() method to Database class
  - Fixed fatal error: Call to undefined method
  - Returns all active ranks ordered by rank_order
  - Required for rank-progress.php partial template
- 🐛 **Portal Content Display** - All portal tabs now display correctly
  - Fixed timing issues in tab switching
  - Improved scroll-to-top behavior
  - Better active state management

### Technical
- Vanilla JavaScript tab navigation for better compatibility
- DOM ready state checking
- Event delegation for dynamic content
- Console logging for troubleshooting

## [1.2.0] - 2024-01-20

### Added
- ✨ **MLM Portal Page** - Comprehensive portal with modern glassmorphism design
  - Custom full-page template (mlm-portal-template.php)
  - Purple/pink gradient theme with sparkle effects
  - Six integrated tabs: Dashboard, Genealogy, Network, Wallet, Commissions, Rank Progress
  - Real-time data updates via AJAX
  - Particle animations and smooth transitions
  - Fully responsive for all devices
- ✨ **Portal CSS** - thaiprompt-mlm-portal.css
  - Glassmorphism effects with backdrop-filter
  - Animated gradient backgrounds
  - Custom scrollbar styling
  - Progress bars and badges
  - Mobile-optimized responsive design
- ✨ **Portal JavaScript** - thaiprompt-mlm-portal.js
  - SPA-like tab navigation
  - Animated statistics counters
  - Floating particle effects
  - Auto-refresh functionality
  - Copy and share features
- ✨ **WooCommerce Integration** - Portal link in My Account menu
- ✨ **Auto-creation** - Portal page created automatically on activation
- ✨ **Template System** - Custom WordPress page template registration

### Improved
- 🔧 **User Experience** - Unified portal interface for all MLM functions
- 🔧 **Design Language** - Modern purple theme with international standards
- 🔧 **Performance** - Optimized asset loading for portal pages only
- 🔧 **Navigation** - Seamless access from WordPress account page
- 🔧 **Independence** - Complete separation from WordPress theme styling

### Technical
- Custom page template system integration
- Conditional asset enqueuing for portal pages
- WordPress rewrite endpoint registration
- Template include filter implementation
- Page meta template assignment on creation

## [1.1.0] - 2024-01-15

### Added
- ✅ **Complete Admin Dashboard** - Fully functional admin interface with real statistics
- ✅ **Admin Network Management** - View and manage MLM network with genealogy visualization
- ✅ **Commissions Management** - Approve and manage commissions with filtering
- ✅ **Wallet & Withdrawals** - Process withdrawal requests with detailed information
- ✅ **Ranks Management** - View and manage MLM ranks with member distribution
- ✅ **Reports & Analytics** - Comprehensive reporting with charts and top performers
- ✅ **Settings Page** - Complete configuration interface for all MLM settings
- ✅ **Auto-create Pages** - Automatically create required WordPress pages on activation
- ✅ **User Menu Integration** - Add MLM menu items to WooCommerce My Account
- ✅ **Version Control** - Proper version tracking and changelog

### Improved
- 🔧 **Admin UI/UX** - Modern and responsive admin interface
- 🔧 **Statistics Display** - Real-time data visualization with charts
- 🔧 **User Management** - Better user search and filtering capabilities
- 🔧 **Commission Tracking** - Enhanced commission approval workflow
- 🔧 **Withdrawal Processing** - Streamlined withdrawal approval/rejection

### Fixed
- 🐛 **Empty Admin Pages** - Fixed issue where admin pages were blank
- 🐛 **Missing Partials** - Added all required admin partial templates
- 🐛 **Data Display** - Fixed statistics and data visualization issues

## [1.0.0] - 2024-01-10

### Initial Release
- ✨ **MLM Core System** - Binary tree network structure
- ✨ **GSAP Genealogy Tree** - Beautiful animated genealogy visualization
- ✨ **Wallet System** - Complete digital wallet with transactions
- ✨ **Commission Engine** - Multi-level commission calculation
  - Level Commissions (1-10+ levels)
  - Fast Start Bonus
  - Binary Commission
  - Rank Achievement Bonus
- ✨ **Rank System** - 6 default ranks (Member to Diamond)
- ✨ **Placement Algorithms**
  - Auto placement (BFS)
  - Left spillover
  - Right spillover
  - Balanced placement
- ✨ **WooCommerce Integration** - Seamless integration with WooCommerce
- ✨ **Dokan Support** - Multi-vendor marketplace compatibility
- ✨ **Product-Level Settings** - Configure MLM per product
- ✨ **Frontend Dashboard** - User dashboard with statistics
- ✨ **Referral System** - Referral link generation and tracking
- ✨ **Shortcodes** - 8 ready-to-use shortcodes
- ✨ **Responsive Design** - Mobile-friendly UI
- ✨ **Modern UI/UX** - Gradient designs and card layouts
- ✨ **Database Schema** - 8 optimized database tables
- ✨ **API & Hooks** - Extensible with WordPress hooks and filters

### Technical
- PHP 7.4+ compatibility
- WordPress 5.8+ compatibility
- WooCommerce 5.0+ integration
- GSAP 3.12 animations
- Chart.js for analytics
- jQuery for interactions
- Responsive CSS with CSS Grid and Flexbox

---

## Version History

### [1.2.0] - MLM Portal with Modern Design
Major feature release adding a comprehensive, standalone MLM Portal with modern glassmorphism design, purple theme, and complete functionality in a unified interface.

### [1.1.0] - Admin Improvements & Bug Fixes
Major update focusing on making the admin interface fully functional with proper data display and management capabilities.

### [1.0.0] - Initial Release
First public release with complete MLM functionality including network management, commissions, wallet, and integrations.

---

## Upgrade Notes

### Upgrading to 1.2.0
- New MLM Portal page will be created automatically
- Portal accessible from WooCommerce My Account menu
- No database migration required
- All existing settings and data preserved
- Portal template registered automatically
- Flush permalinks may be required (Settings > Permalinks > Save)

### Upgrading to 1.1.0
- All admin pages now display actual data
- New pages will be auto-created on plugin activation
- No database migration required
- Settings will be preserved

### Future Versions
We're committed to continuous improvement. Upcoming features:
- Email notifications for commissions and withdrawals
- Advanced reporting with PDF export
- Mobile app integration
- SMS notifications
- Multi-currency support
- Advanced genealogy views
- Automated rank calculations
- Performance optimizations

---

## Support

For support, bug reports, or feature requests:
- Email: support@thaiprompt.com
- Documentation: https://thaiprompt.com/docs/mlm-plugin
- GitHub Issues: https://github.com/thaiprompt/mlm-plugin/issues

---

## Credits

Developed by **Thaiprompt Team**
- Lead Developer: [Your Name]
- UI/UX Design: Thaiprompt Design Team
- Testing: Thaiprompt QA Team

Special thanks to:
- GSAP for amazing animations
- Chart.js for beautiful charts
- WooCommerce team for excellent e-commerce platform
- WordPress community

---

*Generated with ❤️ by Thaiprompt*
