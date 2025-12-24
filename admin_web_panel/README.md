# 🌐 Bold Beauty Lounge - Web Admin Panel

## 🚀 Quick Start

### Run the Application

```bash
cd admin_web_panel
flutter run -d chrome
```

The application will open automatically in Chrome browser.

### Access URL

Once running, the admin panel will be available at:
- **Local:** `http://localhost:8080` (or the port shown in terminal)

---

## 📋 Features

### ✅ Implemented:
- **Login Screen** - Split layout with form and background
- **Dashboard** - Statistics cards and charts
- **Sidebar Navigation** - Left sidebar with all menu items
- **Top Header** - User profile and notifications
- **Responsive Layout** - Professional admin panel design

### 🚧 To Be Added:
- Firebase Authentication integration
- Real data from Firestore
- All admin screens (Appointments, Employees, Customers, etc.)
- Search and filter functionality
- CRUD operations

---

## 🎨 Design

Based on LuxeLooks admin panel:
- **Left Sidebar** with navigation
- **Purple/Pink accent color** (#9C27B0)
- **Clean white/gray backgrounds**
- **Statistics cards** on dashboard
- **Professional layout**

---

## 📁 Project Structure

```
admin_web_panel/
├── lib/
│   ├── main.dart
│   ├── layouts/
│   │   └── admin_layout.dart
│   └── screens/
│       ├── auth/
│       │   └── login_screen.dart
│       └── dashboard/
│           └── dashboard_screen.dart
└── pubspec.yaml
```

---

## 🔧 Next Steps

1. **Add Firebase Configuration**
   - Copy `firebase_options.dart` from mobile app
   - Initialize Firebase in `main.dart`

2. **Create All Admin Screens**
   - Appointments
   - Employees
   - Customers
   - Services
   - Transactions
   - Reviews
   - Products
   - Settings

3. **Connect to Firestore**
   - Query bookings
   - Display real data
   - Add CRUD operations

4. **Deploy to Web**
   ```bash
   flutter build web --release
   # Deploy build/web folder to Firebase Hosting, Netlify, or Vercel
   ```

---

## 🌐 Deployment

### Firebase Hosting
```bash
flutter build web --release
firebase init hosting
firebase deploy --only hosting
```

### Netlify
```bash
flutter build web --release
# Drag and drop build/web folder to Netlify
```

---

## 📝 Login Credentials (Demo)

For now, you can click "Login" with any email/password to see the dashboard.

**Note:** Real authentication will be added with Firebase Auth.

---

Enjoy your admin panel! 🎉
