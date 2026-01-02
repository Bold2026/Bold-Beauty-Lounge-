import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'localization_service.dart';

class TranslationService {
  static final TranslationService _instance = TranslationService._internal();
  factory TranslationService() => _instance;
  TranslationService._internal();

  Map<String, dynamic> _translations = {};
  final LocalizationService _localizationService = LocalizationService();

  // Initialize translations
  Future<void> initializeTranslations() async {
    await _loadTranslations(_localizationService.currentLanguageCode);
  }

  // Load translations for specific language
  Future<void> _loadTranslations(String languageCode) async {
    try {
      final String jsonString = await rootBundle.loadString(
        'assets/translations/$languageCode.json',
      );
      _translations = json.decode(jsonString);
    } catch (e) {
      print('Error loading translations for $languageCode: $e');
      // Fallback to French
      if (languageCode != 'fr') {
        await _loadTranslations('fr');
      }
    }
  }

  // Get translation
  String translate(String key) {
    final keys = key.split('.');
    dynamic value = _translations;

    for (String k in keys) {
      if (value is Map<String, dynamic> && value.containsKey(k)) {
        value = value[k];
      } else {
        return key; // Return key if translation not found
      }
    }

    return value is String ? value : key;
  }

  // Get translation with parameters
  String translateWithParams(String key, Map<String, String> params) {
    String translation = translate(key);

    params.forEach((paramKey, paramValue) {
      translation = translation.replaceAll('{$paramKey}', paramValue);
    });

    return translation;
  }

  // Change language
  Future<void> changeLanguage(String languageCode) async {
    await _localizationService.setLanguage(languageCode);
    await _loadTranslations(languageCode);
  }

  // Get current language
  String get currentLanguage => _localizationService.currentLanguageCode;

  // Check if RTL
  bool get isRTL => _localizationService.isRTL(currentLanguage);

  // Get text direction
  TextDirection get textDirection => _localizationService.getTextDirection();
}

// Extension for easy access
extension TranslationExtension on String {
  String tr() {
    return TranslationService().translate(this);
  }

  String trParams(Map<String, String> params) {
    return TranslationService().translateWithParams(this, params);
  }
}




