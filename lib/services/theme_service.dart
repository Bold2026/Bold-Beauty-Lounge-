import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'typography_service.dart';

class ThemeService {
  static const String _themeKey = 'theme_mode';
  static const String _isDarkKey = 'is_dark_mode';
  static const String _accentColorKey = 'accent_color';
  static const String _fontSizeKey = 'font_size';

  // Singleton
  static final ThemeService _instance = ThemeService._internal();
  factory ThemeService() => _instance;
  ThemeService._internal();

  // Theme modes
  static const ThemeMode lightMode = ThemeMode.light;
  static const ThemeMode darkMode = ThemeMode.dark;
  static const ThemeMode systemMode = ThemeMode.system;

  // Current theme mode
  ThemeMode _currentThemeMode = ThemeMode.dark;
  bool _isDarkMode = true;
  Color _accentColor = const Color(0xFFE9D7C2);
  double _fontSize = 1.0;

  // Getters
  ThemeMode get currentThemeMode => _currentThemeMode;
  bool get isDarkMode => _isDarkMode;
  Color get accentColor => _accentColor;
  double get fontSize => _fontSize;

  // Initialize theme from storage
  Future<void> initializeTheme() async {
    final prefs = await SharedPreferences.getInstance();
    _isDarkMode = prefs.getBool(_isDarkKey) ?? true;
    _currentThemeMode = _isDarkMode ? darkMode : lightMode;

    // Load accent color
    final accentColorValue = prefs.getInt(_accentColorKey);
    if (accentColorValue != null) {
      _accentColor = Color(accentColorValue);
    }

    // Load font size
    _fontSize = prefs.getDouble(_fontSizeKey) ?? 1.0;
  }

  // Toggle theme
  Future<void> toggleTheme() async {
    _isDarkMode = !_isDarkMode;
    _currentThemeMode = _isDarkMode ? darkMode : lightMode;

    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(_isDarkKey, _isDarkMode);
  }

  // Set specific theme
  Future<void> setThemeMode(ThemeMode mode) async {
    _currentThemeMode = mode;
    _isDarkMode = mode == darkMode;

    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(_isDarkKey, _isDarkMode);
  }

  // Set accent color
  Future<void> setAccentColor(Color color) async {
    _accentColor = color;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setInt(_accentColorKey, color.value);
  }

  // Set font size
  Future<void> setFontSize(double size) async {
    _fontSize = size.clamp(0.8, 1.4); // Limite entre 80% et 140%
    final prefs = await SharedPreferences.getInstance();
    await prefs.setDouble(_fontSizeKey, _fontSize);
  }

  // Reset to default theme
  Future<void> resetTheme() async {
    _isDarkMode = false;
    _currentThemeMode = lightMode;
    _accentColor = const Color(0xFFE9D7C2);
    _fontSize = 1.0;

    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_isDarkKey);
    await prefs.remove(_accentColorKey);
    await prefs.remove(_fontSizeKey);
  }

  // Get theme data for light mode
  static ThemeData get lightTheme {
    return ThemeData(
      useMaterial3: true,
      brightness: Brightness.light,
      colorScheme: const ColorScheme.light(
        primary: Color(0xFFE9D7C2), // Beige principal
        secondary: Color(0xFF8B7355), // Beige foncé
        surface: Colors.white,
        background: Colors.white,
        onPrimary: Colors.white,
        onSecondary: Colors.white,
        onSurface: Colors.black,
        onBackground: Colors.black,
        error: Color(0xFFD32F2F),
        onError: Colors.white,
      ),
      appBarTheme: const AppBarTheme(
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
        elevation: 0,
        centerTitle: true,
      ),
      cardTheme: CardThemeData(
        color: Colors.white,
        elevation: 2,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: const Color(0xFFE9D7C2),
          foregroundColor: Colors.white,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
        ),
      ),
      textTheme: TypographyService.lightTextTheme,
    );
  }

  // Get theme data for dark mode
  static ThemeData get darkTheme {
    return ThemeData(
      useMaterial3: true,
      brightness: Brightness.dark,
      colorScheme: const ColorScheme.dark(
        primary: Color(0xFFE9D7C2), // Beige principal (même couleur)
        secondary: Color(0xFFD4AF37), // Or pour dark mode
        surface: Color(0xFF1A1A1A), // Noir profond
        background: Color(0xFF121212), // Noir plus profond
        onPrimary: Colors.black,
        onSecondary: Colors.black,
        onSurface: Colors.white,
        onBackground: Colors.white,
        error: Color(0xFFCF6679),
        onError: Colors.black,
      ),
      appBarTheme: const AppBarTheme(
        backgroundColor: Color(0xFF1A1A1A),
        foregroundColor: Colors.white,
        elevation: 0,
        centerTitle: true,
      ),
      cardTheme: CardThemeData(
        color: const Color(0xFF2A2A2A),
        elevation: 4,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: const Color(0xFFE9D7C2),
          foregroundColor: Colors.black,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
        ),
      ),
      textTheme: TypographyService.darkTextTheme,
    );
  }
}
