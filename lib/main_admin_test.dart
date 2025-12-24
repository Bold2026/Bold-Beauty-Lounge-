import 'package:flutter/material.dart';
import 'screens/admin_web/admin_login_screen.dart';

void main() {
  runApp(const AdminTestApp());
}

class AdminTestApp extends StatelessWidget {
  const AdminTestApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Bold Beauty Lounge - Admin Test',
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
      home: Scaffold(
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Text(
                'Admin Panel Test',
                style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 20),
              ElevatedButton(
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => const AdminLoginScreen(),
                    ),
                  );
                },
                child: const Text('Go to Admin Login'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}







