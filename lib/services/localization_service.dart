import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

class LocalizationService {
  static const String _languageKey = 'selected_language';
  static const String _countryKey = 'selected_country';

  // Singleton
  static final LocalizationService _instance = LocalizationService._internal();
  factory LocalizationService() => _instance;
  LocalizationService._internal();

  // Supported locales
  static const List<Locale> supportedLocales = [
    Locale('fr', 'MA'), // French (Morocco)
    Locale('ar', 'MA'), // Arabic (Morocco)
    Locale('en', 'US'), // English (US)
  ];

  // Current locale
  Locale _currentLocale = const Locale('fr', 'MA');
  Locale get currentLocale => _currentLocale;

  // Initialize localization
  Future<void> initializeLocalization() async {
    final prefs = await SharedPreferences.getInstance();
    final languageCode = prefs.getString(_languageKey) ?? 'fr';
    final countryCode = prefs.getString(_countryKey) ?? 'MA';

    _currentLocale = Locale(languageCode, countryCode);
  }

  // Set locale
  Future<void> setLocale(Locale locale) async {
    if (!supportedLocales.contains(locale)) return;

    _currentLocale = locale;

    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_languageKey, locale.languageCode);
    await prefs.setString(_countryKey, locale.countryCode ?? '');
  }

  // Get locale display name
  String getLocaleDisplayName(Locale locale) {
    switch (locale.languageCode) {
      case 'fr':
        return 'Français';
      case 'ar':
        return 'العربية';
      case 'en':
        return 'English';
      default:
        return 'Français';
    }
  }

  // Check if RTL
  bool isRTL(Locale locale) {
    return locale.languageCode == 'ar';
  }

  // Get text direction
  TextDirection getTextDirection(Locale locale) {
    return isRTL(locale) ? TextDirection.rtl : TextDirection.ltr;
  }
}
