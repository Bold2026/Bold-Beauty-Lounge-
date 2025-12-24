import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:firebase_core/firebase_core.dart';
import 'firebase_options.dart';
import 'services/theme_service.dart';
import 'services/localization_service.dart';
import 'services/firebase_service.dart';
import 'services/analytics_service.dart';
import 'services/notification_service.dart';
import 'providers/admin/admin_auth_provider.dart';
import 'providers/admin/bookings_provider.dart';
import 'providers/admin/services_provider.dart';
import 'providers/admin/dashboard_provider.dart';
import 'providers/admin/time_slots_provider.dart';
import 'repositories/admin/admin_repository.dart';
import 'repositories/admin/bookings_repository.dart';
import 'repositories/admin/services_repository.dart';
import 'repositories/admin/time_slots_repository.dart';
import 'screens/admin_web/admin_main_screen.dart';

/// Main entry point for Admin Panel
/// 
/// Properly initializes Firebase using flutterfire_cli generated options
void main() async {
  // STEP 1: Ensure Flutter binding is initialized
  WidgetsFlutterBinding.ensureInitialized();

  // STEP 2: Initialize non-Firebase services
  try {
    await ThemeService().initializeTheme();
    await LocalizationService().initializeLocalization();
    print('✅ Non-Firebase services initialized');
  } catch (e) {
    print('⚠️ Non-Firebase service initialization error: $e');
  }

  // STEP 3: Initialize Firebase with proper options
  try {
    await Firebase.initializeApp(
      options: DefaultFirebaseOptions.currentPlatform,
    );
    print('✅ Firebase.initializeApp() completed successfully');

    // STEP 4: Initialize Firebase services
    try {
      await FirebaseService.instance.initialize();
      print('✅ FirebaseService configured');
    } catch (e) {
      print('⚠️ FirebaseService configuration failed: $e');
    }

    try {
      await AnalyticsService.instance.initialize();
      print('✅ AnalyticsService initialized');
    } catch (e) {
      print('⚠️ AnalyticsService initialization failed: $e');
    }

    try {
      await NotificationService.instance.initialize();
      print('✅ NotificationService initialized');
    } catch (e) {
      print('⚠️ NotificationService initialization failed: $e');
    }

    print('🔥 Firebase initialization completed successfully');
  } catch (e, stackTrace) {
    print('❌ Firebase initialization failed: $e');
    print('Stack trace: $stackTrace');
    print('⚠️ Continuing without Firebase - UI will work but Firebase features will be disabled');
  }

  // STEP 5: Run the app
  runApp(const AdminPanelApp());
}

/// Admin Panel App Widget
class AdminPanelApp extends StatelessWidget {
  const AdminPanelApp({super.key});

  @override
  Widget build(BuildContext context) {
    final themeService = ThemeService();

    return MultiProvider(
      providers: [
        ChangeNotifierProvider(
          create: (_) => AdminAuthProvider(adminRepository: AdminRepository()),
        ),
        ChangeNotifierProvider(
          create: (_) => BookingsProvider(repository: BookingsRepository()),
        ),
        ChangeNotifierProvider(
          create: (_) => ServicesProvider(repository: ServicesRepository()),
        ),
        ChangeNotifierProvider(
          create: (_) => DashboardProvider(repository: BookingsRepository()),
        ),
        ChangeNotifierProvider(
          create: (_) => TimeSlotsProvider(repository: TimeSlotsRepository()),
        ),
      ],
      child: MaterialApp(
        title: 'Bold Beauty Lounge - Admin Panel',
        debugShowCheckedModeBanner: false,
        debugShowMaterialGrid: false,
        theme: ThemeService.lightTheme,
        darkTheme: ThemeService.darkTheme,
        themeMode: themeService.currentThemeMode,
        builder: (context, child) {
          return MediaQuery(
            data: MediaQuery.of(
              context,
            ).copyWith(textScaler: const TextScaler.linear(1.0)),
            child: child!,
          );
        },
        home: const AdminMainScreen(),
      ),
    );
  }
}
