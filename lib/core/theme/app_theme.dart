import 'package:flutter/material.dart';

class AppTheme {
  static ThemeData light() {
    return ThemeData(
      colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xff721c80)),
      useMaterial3: true,
      scaffoldBackgroundColor: const Color(0xFFF8F8F8),
    );
  }

  static ThemeData dark() {
    final base = ThemeData.dark(useMaterial3: true);
    return base.copyWith(
      colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xff721c80), brightness: Brightness.dark),
    );
  }
}
