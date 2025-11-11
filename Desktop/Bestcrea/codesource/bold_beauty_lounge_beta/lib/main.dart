import 'package:flutter/material.dart';
import 'screens/introduction/onboarding_screen.dart';
import 'screens/home/offline_home_screen.dart';
import 'package:lucide_icons/lucide_icons.dart';
import 'screens/booking/rdv_history_screen.dart';
import 'screens/chatbot/enhanced_chatbot_screen.dart';
import 'screens/profile/offline_profile_screen.dart';
import 'services/theme_service.dart';
import 'services/localization_service.dart';
import 'services/firebase_service.dart';
import 'services/analytics_service.dart';
import 'services/notification_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Initialize services
  await ThemeService().initializeTheme();
  await LocalizationService().initializeLocalization();

  // Initialize Firebase
  try {
    await FirebaseService.instance.initialize();
    await AnalyticsService.instance.initialize();
    await NotificationService.instance.initialize();
    print('🔥 All services initialized successfully');
  } catch (e) {
    print('❌ Service initialization failed: $e');
  }

  runApp(const BoldBeautyLoungeApp());
}

class BoldBeautyLoungeApp extends StatelessWidget {
  const BoldBeautyLoungeApp({super.key});

  @override
  Widget build(BuildContext context) {
    final themeService = ThemeService();

    return MaterialApp(
      title: 'Bold Beauty Lounge Beta',
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
      home: const _LaunchToOnboarding(),
    );
  }
}

class _LaunchToOnboarding extends StatefulWidget {
  const _LaunchToOnboarding();

  @override
  State<_LaunchToOnboarding> createState() => _LaunchToOnboardingState();
}

class _LaunchToOnboardingState extends State<_LaunchToOnboarding> {
  @override
  void initState() {
    super.initState();
    Future.delayed(const Duration(seconds: 3), () {
      if (!mounted) return;
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const OnBoardingScreen()),
      );
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: SafeArea(
        child: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 140,
                height: 140,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  gradient: const LinearGradient(
                    colors: [Color(0xFFE9D7C2), Colors.white],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                ),
                padding: const EdgeInsets.all(6),
                child: Container(
                  decoration: const BoxDecoration(
                    color: Colors.black,
                    shape: BoxShape.circle,
                  ),
                  padding: const EdgeInsets.all(24),
                  child: Image.asset(
                    'assets/logo/app_icon.png',
                    fit: BoxFit.contain,
                  ),
                ),
              ),
              const SizedBox(height: 24),
              const Text(
                'Bold Beauty Lounge',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 20,
                  fontWeight: FontWeight.w700,
                  letterSpacing: 0.8,
                ),
              ),
              const SizedBox(height: 8),
              const Text(
                'Votre beauté, notre passion.',
                style: TextStyle(
                  color: Colors.white70,
                  fontSize: 14,
                  letterSpacing: 0.5,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class MainNavigationWidget extends StatefulWidget {
  const MainNavigationWidget({super.key});

  @override
  State<MainNavigationWidget> createState() => _MainNavigationState();
}

class _MainNavigationState extends State<MainNavigationWidget> {
  int _index = 0;

  final List<Widget> _screens = [
    OfflineHomeScreen(),
    EnhancedChatbotScreen(),
    RdvHistoryScreen(),
    OfflineProfileScreen(),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: _screens[_index],
      bottomNavigationBar: SafeArea(
        minimum: const EdgeInsets.fromLTRB(20, 0, 20, 24),
        child: LayoutBuilder(
          builder: (context, constraints) {
            final double barHeight = 72;
            final items = [
              _NavItem(label: 'Accueil', icon: LucideIcons.home),
              _NavItem(label: 'Assistant', icon: LucideIcons.bot),
              _NavItem(label: 'RDV', icon: LucideIcons.calendarCheck2),
              _NavItem(label: 'Profil', icon: LucideIcons.userCircle2),
            ];

            return Container(
              width: constraints.maxWidth,
              height: barHeight,
              decoration: BoxDecoration(
                color: const Color(0xFF0D0D0D),
                borderRadius: BorderRadius.circular(barHeight / 2),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.45),
                    blurRadius: 24,
                    offset: const Offset(0, 18),
                  ),
                ],
                border: Border.all(
                  color: Colors.white.withOpacity(0.06),
                  width: 1,
                ),
              ),
              child: Row(
                children: List.generate(items.length, (index) {
                  final item = items[index];
                  final bool isSelected = _index == index;
                  return Expanded(
                    child: GestureDetector(
                      onTap: () => setState(() => _index = index),
                      child: AnimatedContainer(
                        duration: const Duration(milliseconds: 220),
                        curve: Curves.easeOutCubic,
                        margin: const EdgeInsets.symmetric(
                          horizontal: 6,
                          vertical: 10,
                        ),
                        decoration: BoxDecoration(
                          color: isSelected
                              ? const Color(0xFFE9D7C2)
                              : Colors.black.withOpacity(0.35),
                          borderRadius: BorderRadius.circular(28),
                          boxShadow: isSelected
                              ? [
                                  BoxShadow(
                                    color: const Color(
                                      0xFFE9D7C2,
                                    ).withOpacity(0.45),
                                    blurRadius: 16,
                                    offset: const Offset(0, 8),
                                  ),
                                ]
                              : [],
                        ),
                        child: Center(
                          child: Icon(
                            item.icon,
                            size: 28,
                            color: isSelected
                                ? Colors.black
                                : Colors.white.withOpacity(0.9),
                          ),
                        ),
                      ),
                    ),
                  );
                }),
              ),
            );
          },
        ),
      ),
    );
  }
}

class _NavItem {
  final String label;
  final IconData icon;

  _NavItem({required this.label, required this.icon});
}
