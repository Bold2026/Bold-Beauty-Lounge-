import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:permission_handler/permission_handler.dart';
import 'analytics_service.dart';

class NotificationService {
  static NotificationService? _instance;
  static NotificationService get instance =>
      _instance ??= NotificationService._();
  NotificationService._();

  final FirebaseMessaging _messaging = FirebaseMessaging.instance;
  final FlutterLocalNotificationsPlugin _localNotifications =
      FlutterLocalNotificationsPlugin();
  final AnalyticsService _analytics = AnalyticsService.instance;

  // Notification channels
  static const String _generalChannel = 'general';
  static const String _appointmentsChannel = 'appointments';
  static const String _promotionsChannel = 'promotions';
  static const String _remindersChannel = 'reminders';
  static const String _updatesChannel = 'updates';
  static const String _loyaltyChannel = 'loyalty';
  static const String _reviewsChannel = 'reviews';

  // Initialize notifications
  Future<void> initialize() async {
    try {
      await _initializeLocalNotifications();
      await _initializeFirebaseMessaging();
      await _requestPermissions();
      debugPrint('🔔 Notifications initialized successfully');
    } catch (e) {
      debugPrint('❌ Notifications initialization failed: $e');
    }
  }

  Future<void> _initializeLocalNotifications() async {
    const AndroidInitializationSettings androidSettings =
        AndroidInitializationSettings('@mipmap/ic_launcher');
    const DarwinInitializationSettings iosSettings =
        DarwinInitializationSettings(
          requestAlertPermission: true,
          requestBadgePermission: true,
          requestSoundPermission: true,
        );

    const InitializationSettings settings = InitializationSettings(
      android: androidSettings,
      iOS: iosSettings,
    );

    await _localNotifications.initialize(
      settings,
      onDidReceiveNotificationResponse: _onNotificationTapped,
    );

    // Create notification channels for Android
    await _createNotificationChannels();
  }

  Future<void> _createNotificationChannels() async {
    const channels = [
      AndroidNotificationChannel(
        _generalChannel,
        'Général',
        description: 'Notifications générales',
        importance: Importance.high,
      ),
      AndroidNotificationChannel(
        _appointmentsChannel,
        'Rendez-vous',
        description: 'Notifications de rendez-vous',
        importance: Importance.high,
      ),
      AndroidNotificationChannel(
        _promotionsChannel,
        'Promotions',
        description: 'Notifications de promotions',
        importance: Importance.defaultImportance,
      ),
      AndroidNotificationChannel(
        _remindersChannel,
        'Rappels',
        description: 'Rappels de rendez-vous',
        importance: Importance.high,
      ),
      AndroidNotificationChannel(
        _updatesChannel,
        'Mises à jour',
        description: 'Mises à jour de l\'application',
        importance: Importance.low,
      ),
      AndroidNotificationChannel(
        _loyaltyChannel,
        'Fidélité',
        description: 'Notifications du programme de fidélité',
        importance: Importance.defaultImportance,
      ),
      AndroidNotificationChannel(
        _reviewsChannel,
        'Avis',
        description: 'Notifications d\'avis',
        importance: Importance.defaultImportance,
      ),
    ];

    for (final channel in channels) {
      await _localNotifications
          .resolvePlatformSpecificImplementation<
            AndroidFlutterLocalNotificationsPlugin
          >()
          ?.createNotificationChannel(channel);
    }
  }

  Future<void> _initializeFirebaseMessaging() async {
    // Get FCM token
    final token = await _messaging.getToken();
    debugPrint('📱 FCM Token: $token');

    // Handle background messages
    FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);

    // Handle foreground messages
    FirebaseMessaging.onMessage.listen(_handleForegroundMessage);

    // Handle notification taps
    FirebaseMessaging.onMessageOpenedApp.listen(_handleNotificationTap);
  }

  Future<void> _requestPermissions() async {
    // Request notification permission
    final status = await Permission.notification.request();
    if (status.isGranted) {
      debugPrint('✅ Notification permission granted');
    } else {
      debugPrint('❌ Notification permission denied');
    }

    // Request FCM permission
    final settings = await _messaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
      provisional: false,
    );

    if (settings.authorizationStatus == AuthorizationStatus.authorized) {
      debugPrint('✅ FCM permission granted');
    } else {
      debugPrint('❌ FCM permission denied');
    }
  }

  // Handle background messages
  static Future<void> _firebaseMessagingBackgroundHandler(
    RemoteMessage message,
  ) async {
    debugPrint('📱 Background message: ${message.messageId}');
    await AnalyticsService.instance.logNotificationReceived(
      notificationType: message.data['type'] ?? 'unknown',
      title: message.notification?.title ?? 'Notification',
    );
  }

  // Handle foreground messages
  Future<void> _handleForegroundMessage(RemoteMessage message) async {
    debugPrint('📱 Foreground message: ${message.messageId}');

    await _analytics.logNotificationReceived(
      notificationType: message.data['type'] ?? 'unknown',
      title: message.notification?.title ?? 'Notification',
    );

    // Show local notification
    await _showLocalNotification(message);
  }

  // Handle notification taps
  Future<void> _handleNotificationTap(RemoteMessage message) async {
    debugPrint('📱 Notification tapped: ${message.messageId}');

    await _analytics.logNotificationOpened(
      notificationType: message.data['type'] ?? 'unknown',
      title: message.notification?.title ?? 'Notification',
    );

    // Handle navigation based on notification type
    _handleNotificationNavigation(message.data);
  }

  // Handle local notification taps
  void _onNotificationTapped(NotificationResponse response) {
    debugPrint('📱 Local notification tapped: ${response.id}');
    // Handle local notification tap
  }

  // Show local notification
  Future<void> _showLocalNotification(RemoteMessage message) async {
    final notification = message.notification;
    if (notification == null) return;

    const AndroidNotificationDetails androidDetails =
        AndroidNotificationDetails(
          _generalChannel,
          'Général',
          channelDescription: 'Notifications générales',
          importance: Importance.high,
          priority: Priority.high,
          icon: '@mipmap/ic_launcher',
        );

    const DarwinNotificationDetails iosDetails = DarwinNotificationDetails(
      presentAlert: true,
      presentBadge: true,
      presentSound: true,
    );

    const NotificationDetails details = NotificationDetails(
      android: androidDetails,
      iOS: iosDetails,
    );

    await _localNotifications.show(
      message.hashCode,
      notification.title,
      notification.body,
      details,
    );
  }

  // Handle notification navigation
  void _handleNotificationNavigation(Map<String, dynamic> data) {
    final type = data['type'];
    final id = data['id'];

    switch (type) {
      case 'appointment':
        // Navigate to appointment details
        break;
      case 'promotion':
        // Navigate to promotion details
        break;
      case 'review':
        // Navigate to review screen
        break;
      case 'loyalty':
        // Navigate to loyalty screen
        break;
      default:
        // Navigate to home
        break;
    }
  }

  // Send appointment reminder
  Future<void> sendAppointmentReminder({
    required String appointmentId,
    required String clientName,
    required String serviceName,
    required DateTime appointmentDate,
    required int hoursBefore,
  }) async {
    const AndroidNotificationDetails androidDetails =
        AndroidNotificationDetails(
          _remindersChannel,
          'Rappels',
          channelDescription: 'Rappels de rendez-vous',
          importance: Importance.high,
          priority: Priority.high,
        );

    const DarwinNotificationDetails iosDetails = DarwinNotificationDetails(
      presentAlert: true,
      presentBadge: true,
      presentSound: true,
    );

    const NotificationDetails details = NotificationDetails(
      android: androidDetails,
      iOS: iosDetails,
    );

    await _localNotifications.show(
      appointmentId.hashCode,
      'Rappel de rendez-vous',
      'Votre rendez-vous pour $serviceName est dans $hoursBefore heures',
      details,
    );
  }

  // Send promotion notification
  Future<void> sendPromotionNotification({
    required String promotionId,
    required String title,
    required String description,
    required String imageUrl,
  }) async {
    const AndroidNotificationDetails androidDetails =
        AndroidNotificationDetails(
          _promotionsChannel,
          'Promotions',
          channelDescription: 'Notifications de promotions',
          importance: Importance.defaultImportance,
          priority: Priority.defaultPriority,
        );

    const DarwinNotificationDetails iosDetails = DarwinNotificationDetails(
      presentAlert: true,
      presentBadge: true,
      presentSound: true,
    );

    const NotificationDetails details = NotificationDetails(
      android: androidDetails,
      iOS: iosDetails,
    );

    await _localNotifications.show(
      promotionId.hashCode,
      title,
      description,
      details,
    );
  }

  // Send loyalty notification
  Future<void> sendLoyaltyNotification({
    required String userId,
    required String title,
    required String description,
    required String level,
  }) async {
    const AndroidNotificationDetails androidDetails =
        AndroidNotificationDetails(
          _loyaltyChannel,
          'Fidélité',
          channelDescription: 'Notifications du programme de fidélité',
          importance: Importance.defaultImportance,
          priority: Priority.defaultPriority,
        );

    const DarwinNotificationDetails iosDetails = DarwinNotificationDetails(
      presentAlert: true,
      presentBadge: true,
      presentSound: true,
    );

    const NotificationDetails details = NotificationDetails(
      android: androidDetails,
      iOS: iosDetails,
    );

    await _localNotifications.show(
      userId.hashCode,
      title,
      description,
      details,
    );
  }

  // Get FCM token
  Future<String?> getFCMToken() async {
    return await _messaging.getToken();
  }

  // Subscribe to topic
  Future<void> subscribeToTopic(String topic) async {
    await _messaging.subscribeToTopic(topic);
    debugPrint('📱 Subscribed to topic: $topic');
  }

  // Unsubscribe from topic
  Future<void> unsubscribeFromTopic(String topic) async {
    await _messaging.unsubscribeFromTopic(topic);
    debugPrint('📱 Unsubscribed from topic: $topic');
  }

  // Clear all notifications
  Future<void> clearAllNotifications() async {
    await _localNotifications.cancelAll();
  }

  // Clear specific notification
  Future<void> clearNotification(int id) async {
    await _localNotifications.cancel(id);
  }
}
