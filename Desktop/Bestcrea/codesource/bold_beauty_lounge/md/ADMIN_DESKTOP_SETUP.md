# 🖥️ Quick Setup: Desktop Admin Application

## 📦 Step-by-Step Setup

### 1. Create Desktop Project

```bash
# Create new Flutter desktop project
flutter create --platforms=windows,macos,linux bold_beauty_admin_desktop
cd bold_beauty_admin_desktop
```

### 2. Copy Admin Files

Copy these directories from mobile app to desktop app:

```bash
# From mobile app root
cp -r lib/screens/admin/ ../bold_beauty_admin_desktop/lib/screens/
cp -r lib/services/admin_service.dart ../bold_beauty_admin_desktop/lib/services/
```

### 3. Update pubspec.yaml

```yaml
name: bold_beauty_admin_desktop
description: "Bold Beauty Lounge - Desktop Admin Panel"

dependencies:
  flutter:
    sdk: flutter
  firebase_core: ^2.24.2
  firebase_auth: ^4.15.3
  cloud_firestore: ^4.13.6
  intl: ^0.20.2
  provider: ^6.1.5
```

### 4. Create main.dart

```dart
import 'package:flutter/material.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_auth/firebase_auth.dart';
import 'firebase_options.dart';
import 'screens/admin/admin_access_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await Firebase.initializeApp(
    options: DefaultFirebaseOptions.currentPlatform,
  );
  runApp(const AdminApp());
}

class AdminApp extends StatelessWidget {
  const AdminApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Bold Beauty Lounge - Admin Panel',
      theme: ThemeData(
        primarySwatch: Colors.blue,
        useMaterial3: true,
      ),
      home: const AdminAccessScreen(),
      debugShowCheckedModeBanner: false,
    );
  }
}
```

### 5. Run Desktop App

```bash
# Windows
flutter run -d windows

# macOS
flutter run -d macos

# Linux
flutter run -d linux
```

### 6. Build for Production

```bash
# Windows
flutter build windows --release

# macOS
flutter build macos --release

# Linux
flutter build linux --release
```

---

## 🔑 Admin Login

1. Open desktop application
2. Enter admin credentials
3. System verifies `isAdmin: true` in Firestore
4. Access admin dashboard

---

## 📊 What You'll See

- **Dashboard**: Statistics and overview
- **Bookings**: All user bookings from mobile app
- **Statistics**: Analytics and reports

All data is synchronized in real-time with the mobile app! 🎉
