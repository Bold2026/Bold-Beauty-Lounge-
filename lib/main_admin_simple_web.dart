import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_auth/firebase_auth.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'providers/admin/admin_auth_provider.dart';
import 'providers/admin/bookings_provider.dart';
import 'providers/admin/services_provider.dart';
import 'providers/admin/dashboard_provider.dart';
import 'providers/admin/time_slots_provider.dart';
import 'repositories/admin/admin_repository.dart';
import 'repositories/admin/bookings_repository.dart';
import 'repositories/admin/services_repository.dart';
import 'repositories/admin/time_slots_repository.dart';
import 'screens/admin_web/admin_login_screen.dart';
import 'screens/admin_web/admin_main_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Initialize Firebase without Storage
  try {
    await Firebase.initializeApp();
    debugPrint('🔥 Firebase initialized successfully');
  } catch (e) {
    debugPrint('❌ Firebase initialization error: $e');
  }
  
  runApp(const AdminWebApp());
}

class AdminWebApp extends StatelessWidget {
  const AdminWebApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(
          create: (_) => AdminAuthProvider(
            adminRepository: AdminRepository(),
          ),
        ),
        ChangeNotifierProvider(
          create: (_) => BookingsProvider(
            repository: BookingsRepository(),
          ),
        ),
        ChangeNotifierProvider(
          create: (_) => ServicesProvider(
            repository: ServicesRepository(),
          ),
        ),
        ChangeNotifierProvider(
          create: (_) => DashboardProvider(
            repository: BookingsRepository(),
          ),
        ),
        ChangeNotifierProvider(
          create: (_) => TimeSlotsProvider(
            repository: TimeSlotsRepository(),
          ),
        ),
      ],
      child: MaterialApp(
        title: 'Bold Beauty Lounge - Administration',
        debugShowCheckedModeBanner: false,
        theme: ThemeData(
          useMaterial3: true,
          colorScheme: ColorScheme.fromSeed(
            seedColor: const Color(0xFFDDD1BC),
            brightness: Brightness.light,
          ),
          scaffoldBackgroundColor: Colors.grey[50],
          cardColor: Colors.white,
          appBarTheme: const AppBarTheme(
            backgroundColor: Colors.white,
            foregroundColor: Colors.black,
            elevation: 0,
          ),
        ),
        home: Consumer<AdminAuthProvider>(
          builder: (context, authProvider, _) {
            if (authProvider.isAuthenticated) {
              return const AdminMainScreen();
            }
            return const AdminLoginScreen();
          },
        ),
      ),
    );
  }
}







