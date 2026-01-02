import 'package:firebase_analytics/firebase_analytics.dart';
import 'package:flutter/foundation.dart';

class AnalyticsService {
  static AnalyticsService? _instance;
  static AnalyticsService get instance => _instance ??= AnalyticsService._();
  AnalyticsService._();

  final FirebaseAnalytics _analytics = FirebaseAnalytics.instance;

  // Event names
  static const String _appOpened = 'app_opened';
  static const String _userRegistered = 'user_registered';
  static const String _userLoggedIn = 'user_logged_in';
  static const String _userLoggedOut = 'user_logged_out';
  static const String _serviceViewed = 'service_viewed';
  static const String _serviceBooked = 'service_booked';
  static const String _appointmentCancelled = 'appointment_cancelled';
  static const String _reviewSubmitted = 'review_submitted';
  static const String _favoriteAdded = 'favorite_added';
  static const String _favoriteRemoved = 'favorite_removed';
  static const String _promotionViewed = 'promotion_viewed';
  static const String _promotionUsed = 'promotion_used';
  static const String _galleryViewed = 'gallery_viewed';
  static const String _searchPerformed = 'search_performed';
  static const String _chatbotUsed = 'chatbot_used';
  static const String _paymentInitiated = 'payment_initiated';
  static const String _paymentCompleted = 'payment_completed';
  static const String _paymentFailed = 'payment_failed';
  static const String _whatsappContacted = 'whatsapp_contacted';
  static const String _mapOpened = 'map_opened';
  static const String _socialLoginUsed = 'social_login_used';
  static const String _notificationReceived = 'notification_received';
  static const String _notificationOpened = 'notification_opened';
  static const String _themeChanged = 'theme_changed';
  static const String _languageChanged = 'language_changed';

  // User properties
  static const String _userType = 'user_type';
  static const String _loyaltyLevel = 'loyalty_level';
  static const String _preferredLanguage = 'preferred_language';
  static const String _preferredTheme = 'preferred_theme';
  static const String _totalBookings = 'total_bookings';
  static const String _totalSpent = 'total_spent';

  // Initialize analytics
  Future<void> initialize() async {
    try {
      await _analytics.setAnalyticsCollectionEnabled(true);
      await _analytics.setSessionTimeoutDuration(const Duration(minutes: 30));
      debugPrint('📊 Analytics initialized successfully');
    } catch (e) {
      debugPrint('❌ Analytics initialization failed: $e');
    }
  }

  // Set user properties
  Future<void> setUserProperties({
    String? userId,
    String? userType,
    String? loyaltyLevel,
    String? preferredLanguage,
    String? preferredTheme,
    int? totalBookings,
    double? totalSpent,
  }) async {
    try {
      if (userId != null) await _analytics.setUserId(id: userId);
      if (userType != null)
        await _analytics.setUserProperty(name: _userType, value: userType);
      if (loyaltyLevel != null)
        await _analytics.setUserProperty(
          name: _loyaltyLevel,
          value: loyaltyLevel,
        );
      if (preferredLanguage != null)
        await _analytics.setUserProperty(
          name: _preferredLanguage,
          value: preferredLanguage,
        );
      if (preferredTheme != null)
        await _analytics.setUserProperty(
          name: _preferredTheme,
          value: preferredTheme,
        );
      if (totalBookings != null)
        await _analytics.setUserProperty(
          name: _totalBookings,
          value: totalBookings.toString(),
        );
      if (totalSpent != null)
        await _analytics.setUserProperty(
          name: _totalSpent,
          value: totalSpent.toString(),
        );
    } catch (e) {
      debugPrint('❌ Set user properties failed: $e');
    }
  }

  // App events
  Future<void> logAppOpened() async {
    await _logEvent(_appOpened, {
      'timestamp': DateTime.now().millisecondsSinceEpoch,
      'platform': defaultTargetPlatform.name,
    });
  }

  // User events
  Future<void> logUserRegistered({
    required String method,
    String? email,
  }) async {
    await _logEvent(_userRegistered, {
      'method': method,
      'email_domain': email?.split('@').last,
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });
  }

  Future<void> logUserLoggedIn({required String method}) async {
    await _logEvent(_userLoggedIn, {
      'method': method,
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });
  }

  Future<void> logUserLoggedOut() async {
    await _logEvent(_userLoggedOut, {
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });
  }

  // Service events
  Future<void> logServiceViewed({
    required String serviceId,
    required String serviceName,
    required String category,
    required double price,
  }) async {
    await _logEvent(_serviceViewed, {
      'service_id': serviceId,
      'service_name': serviceName,
      'category': category,
      'price': price,
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });
  }

  Future<void> logServiceBooked({
    required String serviceId,
    required String serviceName,
    required String category,
    required double price,
    required String appointmentId,
    required DateTime appointmentDate,
  }) async {
    await _logEvent(_serviceBooked, {
      'service_id': serviceId,
      'service_name': serviceName,
      'category': category,
      'price': price,
      'appointment_id': appointmentId,
      'appointment_date': appointmentDate.millisecondsSinceEpoch,
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });
  }

  Future<void> logAppointmentCancelled({
    required String appointmentId,
    required String reason,
  }) async {
    await _logEvent(_appointmentCancelled, {
      'appointment_id': appointmentId,
      'reason': reason,
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });
  }

  // Review events
  Future<void> logReviewSubmitted({
    required String serviceId,
    required int rating,
    required bool hasComment,
    required int commentLength,
  }) async {
    await _logEvent(_reviewSubmitted, {
      'service_id': serviceId,
      'rating': rating,
      'has_comment': hasComment,
      'comment_length': commentLength,
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });
  }

  // Favorite events
  Future<void> logFavoriteAdded({
    required String itemId,
    required String itemType,
    required String itemName,
  }) async {
    await _logEvent(_favoriteAdded, {
      'item_id': itemId,
      'item_type': itemType,
      'item_name': itemName,
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });
  }

  Future<void> logFavoriteRemoved({
    required String itemId,
    required String itemType,
  }) async {
    await _logEvent(_favoriteRemoved, {
      'item_id': itemId,
      'item_type': itemType,
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });
  }

  // Promotion events
  Future<void> logPromotionViewed({
    required String promotionId,
    required String promotionName,
    required String promotionType,
  }) async {
    await _logEvent(_promotionViewed, {
      'promotion_id': promotionId,
      'promotion_name': promotionName,
      'promotion_type': promotionType,
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });
  }

  Future<void> logPromotionUsed({
    required String promotionId,
    required String promotionName,
    required double discountAmount,
  }) async {
    await _logEvent(_promotionUsed, {
      'promotion_id': promotionId,
      'promotion_name': promotionName,
      'discount_amount': discountAmount,
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });
  }

  // Gallery events
  Future<void> logGalleryViewed({
    required String galleryType,
    required int imageCount,
  }) async {
    await _logEvent(_galleryViewed, {
      'gallery_type': galleryType,
      'image_count': imageCount,
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });
  }

  // Search events
  Future<void> logSearchPerformed({
    required String query,
    required String searchType,
    required int resultCount,
  }) async {
    await _logEvent(_searchPerformed, {
      'query': query,
      'search_type': searchType,
      'result_count': resultCount,
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });
  }

  // Chatbot events
  Future<void> logChatbotUsed({
    required String message,
    required String responseType,
    required int responseTime,
  }) async {
    await _logEvent(_chatbotUsed, {
      'message_length': message.length,
      'response_type': responseType,
      'response_time': responseTime,
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });
  }

  // Payment events
  Future<void> logPaymentInitiated({
    required String paymentMethod,
    required double amount,
    required String currency,
  }) async {
    await _logEvent(_paymentInitiated, {
      'payment_method': paymentMethod,
      'amount': amount,
      'currency': currency,
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });
  }

  Future<void> logPaymentCompleted({
    required String paymentMethod,
    required double amount,
    required String currency,
    required String transactionId,
  }) async {
    await _logEvent(_paymentCompleted, {
      'payment_method': paymentMethod,
      'amount': amount,
      'currency': currency,
      'transaction_id': transactionId,
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });
  }

  Future<void> logPaymentFailed({
    required String paymentMethod,
    required double amount,
    required String errorCode,
  }) async {
    await _logEvent(_paymentFailed, {
      'payment_method': paymentMethod,
      'amount': amount,
      'error_code': errorCode,
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });
  }

  // Integration events
  Future<void> logWhatsappContacted() async {
    await _logEvent(_whatsappContacted, {
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });
  }

  Future<void> logMapOpened({required String destination}) async {
    await _logEvent(_mapOpened, {
      'destination': destination,
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });
  }

  Future<void> logSocialLoginUsed({required String provider}) async {
    await _logEvent(_socialLoginUsed, {
      'provider': provider,
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });
  }

  // Notification events
  Future<void> logNotificationReceived({
    required String notificationType,
    required String title,
  }) async {
    await _logEvent(_notificationReceived, {
      'notification_type': notificationType,
      'title_length': title.length,
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });
  }

  Future<void> logNotificationOpened({
    required String notificationType,
    required String title,
  }) async {
    await _logEvent(_notificationOpened, {
      'notification_type': notificationType,
      'title_length': title.length,
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });
  }

  // Settings events
  Future<void> logThemeChanged({required String themeMode}) async {
    await _logEvent(_themeChanged, {
      'theme_mode': themeMode,
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });
  }

  Future<void> logLanguageChanged({required String language}) async {
    await _logEvent(_languageChanged, {
      'language': language,
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });
  }

  // Helper method
  Future<void> _logEvent(String name, Map<String, dynamic> parameters) async {
    try {
      // Convert dynamic to Object for Firebase Analytics
      final Map<String, Object> convertedParams = parameters.map(
        (key, value) => MapEntry(key, value as Object),
      );
      await _analytics.logEvent(name: name, parameters: convertedParams);
      debugPrint('📊 Event logged: $name');
    } catch (e) {
      debugPrint('❌ Analytics event failed: $e');
    }
  }
}







