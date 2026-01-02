# 🖥️ Bold Beauty Lounge - Web Admin Panel Specification

## 📋 Design Reference: LuxeLooks Admin Panel

Based on the LuxeLooks example, the admin panel should have:

### 🎨 Design Features:
- **Left Sidebar Navigation** with purple/pink accent color
- **Clean, modern interface** with white/gray backgrounds
- **Statistics Dashboard** with cards showing key metrics
- **Table-based data views** with search and filtering
- **Professional layout** with consistent spacing

---

## 🏗️ Structure

### 1. **Login Page** (`/auth/login`)
- Email and password fields
- "Forgot Password?" link
- "Create Account" option (if needed)
- Branded background image on right side

### 2. **Dashboard** (`/dashboard`)
- **Statistics Cards:**
  - Total Customers
  - Total Appointments
  - Gender Distribution (pie chart)
  - Services Count
  - Products Count
  - Revenue (chart)
- **Upcoming Appointments** calendar view
- **Recent Activity** feed

### 3. **Appointments** (`/appointments`)
- Table with columns:
  - ID (#APTXXX)
  - Date
  - Time
  - Customer Name
  - Gender
  - Phone
  - Service
  - Stylist/Specialist
  - Status (Pending/Confirmed/Completed/Cancelled)
- Search bar
- Filter by status
- "Add New" button
- Pagination

### 4. **Employees** (`/employees`)
- Table with columns:
  - ID (EMPXXXX)
  - Name
  - Gender
  - Role
  - Phone
  - Email
  - Joining Date
  - Status (Active/In-Active)
- Search functionality
- "Add New" button

### 5. **Customers** (`/customers`)
- Table with columns:
  - ID (CLXXX)
  - Customer Name
  - Email
  - Phone
  - Last Visit
- Search bar
- "Add New" button
- Pagination

### 6. **Offers** (`/offers`)
- Card-based layout
- Offer details (Title, Dates, Description)
- "Add New" button
- Search functionality

### 7. **Services** (`/services`)
- **Categories Section:**
  - Horizontal scrollable cards with images
  - Category names (Nails, Cleaning, Haircut, Waxing, etc.)
- **Services Table:**
  - ID (#STXXX)
  - Name
  - Category
  - Duration
  - Price
  - Status
- "Add New" button

### 8. **Transactions** (`/transactions`)
- Table with columns:
  - Transaction ID (#TXNXXX)
  - Customer
  - Date & Time
  - Service
  - Amount
  - Status (Paid/Pending)
- Search functionality
- Filter by date range

### 9. **Reviews** (`/reviews`)
- Grid layout of review cards
- Each card shows:
  - Customer name and avatar
  - Star rating
  - Review text
  - Time posted
  - Reply section
- "Reply" button for each review

### 10. **Products** (`/products`)
- Table or grid view
- Product details
- Inventory management
- "Add New" button

### 11. **Settings** (`/settings`)
- Sub-navigation:
  - General (Saloon name, Country, City, Address)
  - Users
  - Roles
  - Master Settings
- Form-based interface
- "Save" button

---

## 🎨 Color Scheme

- **Primary Color:** Purple/Pink (#E9D7C2 or similar to Bold Beauty brand)
- **Active Item:** Dark purple/pink background
- **Status Colors:**
  - Active/Completed: Green
  - Pending: Orange
  - Inactive/Cancelled: Red
- **Background:** White for content, Light gray for sidebar

---

## 📱 Navigation Sidebar

Left sidebar with:
- LuxeLooks logo at top
- Navigation items:
  - Dashboard
  - Appointments
  - Employees
  - Customers
  - Offers
  - Services
  - Transactions
  - Reviews
  - Products
  - Settings

---

## 🔧 Technology Stack Options

### Option 1: Flutter Web (Recommended)
- Same codebase as mobile app
- Reuse admin screens
- Single codebase for web and mobile

### Option 2: React/Next.js
- Modern web framework
- Better SEO
- Faster development for web-specific features

### Option 3: Vue.js
- Lightweight framework
- Easy to learn
- Good for admin panels

---

## 🚀 Implementation Plan

1. **Create Flutter Web Project** for admin panel
2. **Design UI Components** matching LuxeLooks style
3. **Implement Navigation** with sidebar
4. **Create Dashboard** with statistics
5. **Build Data Tables** for each section
6. **Connect to Firebase** (same backend as mobile app)
7. **Add Search & Filters**
8. **Implement CRUD Operations**

---

## 📊 Key Features

✅ **Real-time Updates** from Firestore
✅ **Search & Filter** on all tables
✅ **Pagination** for large datasets
✅ **Status Management** (Active/Inactive, Pending/Confirmed)
✅ **Responsive Design** for different screen sizes
✅ **Authentication** with Firebase Auth
✅ **Role-based Access** (Admin only)

---

## 🔗 Integration

- **Same Firebase Project** as mobile app
- **Same Firestore Database**
- **Real-time Sync** between mobile app and web admin
- **Shared Authentication** system

---

This web admin panel will be completely separate from the mobile app and accessible via browser at a dedicated URL.




