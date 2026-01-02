import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class TypographyService {
  // Font families
  static const String primaryFont = 'Poppins';
  static const String secondaryFont = 'Inter';
  static const String displayFont = 'Playfair Display';
  static const String monoFont = 'JetBrains Mono';

  // Font weights
  static const FontWeight light = FontWeight.w300;
  static const FontWeight regular = FontWeight.w400;
  static const FontWeight medium = FontWeight.w500;
  static const FontWeight semiBold = FontWeight.w600;
  static const FontWeight bold = FontWeight.w700;
  static const FontWeight extraBold = FontWeight.w800;
  static const FontWeight black = FontWeight.w900;

  // Text styles for light theme
  static TextTheme get lightTextTheme {
    return TextTheme(
      // Display styles
      displayLarge: GoogleFonts.playfairDisplay(
        fontSize: 57,
        fontWeight: black,
        letterSpacing: -0.25,
        color: Colors.black,
      ),
      displayMedium: GoogleFonts.playfairDisplay(
        fontSize: 45,
        fontWeight: bold,
        letterSpacing: 0,
        color: Colors.black,
      ),
      displaySmall: GoogleFonts.playfairDisplay(
        fontSize: 36,
        fontWeight: bold,
        letterSpacing: 0,
        color: Colors.black,
      ),

      // Headline styles
      headlineLarge: GoogleFonts.poppins(
        fontSize: 32,
        fontWeight: semiBold,
        letterSpacing: 0,
        color: Colors.black,
      ),
      headlineMedium: GoogleFonts.poppins(
        fontSize: 28,
        fontWeight: semiBold,
        letterSpacing: 0,
        color: Colors.black,
      ),
      headlineSmall: GoogleFonts.poppins(
        fontSize: 24,
        fontWeight: semiBold,
        letterSpacing: 0,
        color: Colors.black,
      ),

      // Title styles
      titleLarge: GoogleFonts.poppins(
        fontSize: 22,
        fontWeight: medium,
        letterSpacing: 0,
        color: Colors.black,
      ),
      titleMedium: GoogleFonts.poppins(
        fontSize: 16,
        fontWeight: medium,
        letterSpacing: 0.15,
        color: Colors.black,
      ),
      titleSmall: GoogleFonts.poppins(
        fontSize: 14,
        fontWeight: medium,
        letterSpacing: 0.1,
        color: Colors.black,
      ),

      // Body styles
      bodyLarge: GoogleFonts.inter(
        fontSize: 16,
        fontWeight: regular,
        letterSpacing: 0.5,
        color: Colors.black87,
      ),
      bodyMedium: GoogleFonts.inter(
        fontSize: 14,
        fontWeight: regular,
        letterSpacing: 0.25,
        color: Colors.black87,
      ),
      bodySmall: GoogleFonts.inter(
        fontSize: 12,
        fontWeight: regular,
        letterSpacing: 0.4,
        color: Colors.black54,
      ),

      // Label styles
      labelLarge: GoogleFonts.poppins(
        fontSize: 14,
        fontWeight: medium,
        letterSpacing: 0.1,
        color: Colors.black,
      ),
      labelMedium: GoogleFonts.poppins(
        fontSize: 12,
        fontWeight: medium,
        letterSpacing: 0.5,
        color: Colors.black,
      ),
      labelSmall: GoogleFonts.poppins(
        fontSize: 11,
        fontWeight: medium,
        letterSpacing: 0.5,
        color: Colors.black54,
      ),
    );
  }

  // Text styles for dark theme
  static TextTheme get darkTextTheme {
    return TextTheme(
      // Display styles
      displayLarge: GoogleFonts.playfairDisplay(
        fontSize: 57,
        fontWeight: black,
        letterSpacing: -0.25,
        color: Colors.white,
      ),
      displayMedium: GoogleFonts.playfairDisplay(
        fontSize: 45,
        fontWeight: bold,
        letterSpacing: 0,
        color: Colors.white,
      ),
      displaySmall: GoogleFonts.playfairDisplay(
        fontSize: 36,
        fontWeight: bold,
        letterSpacing: 0,
        color: Colors.white,
      ),

      // Headline styles
      headlineLarge: GoogleFonts.poppins(
        fontSize: 32,
        fontWeight: semiBold,
        letterSpacing: 0,
        color: Colors.white,
      ),
      headlineMedium: GoogleFonts.poppins(
        fontSize: 28,
        fontWeight: semiBold,
        letterSpacing: 0,
        color: Colors.white,
      ),
      headlineSmall: GoogleFonts.poppins(
        fontSize: 24,
        fontWeight: semiBold,
        letterSpacing: 0,
        color: Colors.white,
      ),

      // Title styles
      titleLarge: GoogleFonts.poppins(
        fontSize: 22,
        fontWeight: medium,
        letterSpacing: 0,
        color: Colors.white,
      ),
      titleMedium: GoogleFonts.poppins(
        fontSize: 16,
        fontWeight: medium,
        letterSpacing: 0.15,
        color: Colors.white,
      ),
      titleSmall: GoogleFonts.poppins(
        fontSize: 14,
        fontWeight: medium,
        letterSpacing: 0.1,
        color: Colors.white,
      ),

      // Body styles
      bodyLarge: GoogleFonts.inter(
        fontSize: 16,
        fontWeight: regular,
        letterSpacing: 0.5,
        color: Colors.white70,
      ),
      bodyMedium: GoogleFonts.inter(
        fontSize: 14,
        fontWeight: regular,
        letterSpacing: 0.25,
        color: Colors.white70,
      ),
      bodySmall: GoogleFonts.inter(
        fontSize: 12,
        fontWeight: regular,
        letterSpacing: 0.4,
        color: Colors.white60,
      ),

      // Label styles
      labelLarge: GoogleFonts.poppins(
        fontSize: 14,
        fontWeight: medium,
        letterSpacing: 0.1,
        color: Colors.white,
      ),
      labelMedium: GoogleFonts.poppins(
        fontSize: 12,
        fontWeight: medium,
        letterSpacing: 0.5,
        color: Colors.white,
      ),
      labelSmall: GoogleFonts.poppins(
        fontSize: 11,
        fontWeight: medium,
        letterSpacing: 0.5,
        color: Colors.white60,
      ),
    );
  }

  // Custom text styles for specific use cases
  static TextStyle get appBarTitle => GoogleFonts.poppins(
    fontSize: 20,
    fontWeight: semiBold,
    letterSpacing: 0.15,
  );

  static TextStyle get buttonText =>
      GoogleFonts.poppins(fontSize: 16, fontWeight: medium, letterSpacing: 0.1);

  static TextStyle get cardTitle =>
      GoogleFonts.poppins(fontSize: 18, fontWeight: semiBold, letterSpacing: 0);

  static TextStyle get cardSubtitle =>
      GoogleFonts.inter(fontSize: 14, fontWeight: regular, letterSpacing: 0.25);

  static TextStyle get priceText =>
      GoogleFonts.poppins(fontSize: 20, fontWeight: bold, letterSpacing: 0);

  static TextStyle get ratingText =>
      GoogleFonts.inter(fontSize: 12, fontWeight: medium, letterSpacing: 0.4);

  static TextStyle get navigationLabel =>
      GoogleFonts.poppins(fontSize: 12, fontWeight: medium, letterSpacing: 0.5);

  static TextStyle get inputLabel =>
      GoogleFonts.inter(fontSize: 14, fontWeight: medium, letterSpacing: 0.1);

  static TextStyle get inputHint =>
      GoogleFonts.inter(fontSize: 16, fontWeight: regular, letterSpacing: 0.5);

  static TextStyle get errorText =>
      GoogleFonts.inter(fontSize: 12, fontWeight: regular, letterSpacing: 0.4);

  static TextStyle get successText =>
      GoogleFonts.inter(fontSize: 12, fontWeight: regular, letterSpacing: 0.4);

  // Typography scale for responsive design
  static double getScaleFactor(BuildContext context) {
    final screenWidth = MediaQuery.of(context).size.width;
    if (screenWidth < 600) {
      return 1.0; // Small screens
    } else if (screenWidth < 900) {
      return 1.1; // Medium screens
    } else {
      return 1.2; // Large screens
    }
  }

  // Responsive text style
  static TextStyle responsive(BuildContext context, TextStyle baseStyle) {
    final scaleFactor = getScaleFactor(context);
    return baseStyle.copyWith(
      fontSize: (baseStyle.fontSize ?? 14) * scaleFactor,
    );
  }
}





