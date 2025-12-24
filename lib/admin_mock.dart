import 'package:flutter/material.dart';
import 'screens/admin_web/admin_main_screen.dart';

void main() {
  runApp(const AdminMockApp());
}

class AdminMockApp extends StatelessWidget {
  const AdminMockApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
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
      home: const AdminMainScreen(),
    );
  }
}






