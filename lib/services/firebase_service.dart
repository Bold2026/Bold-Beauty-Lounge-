import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_auth/firebase_auth.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:firebase_storage/firebase_storage.dart';
import 'package:firebase_analytics/firebase_analytics.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';

class FirebaseService {
  static FirebaseService? _instance;
  static FirebaseService get instance => _instance ??= FirebaseService._();
  FirebaseService._();

  // Firebase instances
  FirebaseAuth get auth => FirebaseAuth.instance;
  FirebaseFirestore get firestore => FirebaseFirestore.instance;
  FirebaseStorage get storage => FirebaseStorage.instance;
  FirebaseAnalytics get analytics => FirebaseAnalytics.instance;
  FirebaseMessaging get messaging => FirebaseMessaging.instance;

  // Initialization
  bool _isInitialized = false;
  bool get isInitialized => _isInitialized;

  /// Initialize Firebase services configuration
  /// 
  /// IMPORTANT: This method does NOT initialize Firebase Core.
  /// Firebase.initializeApp() must be called in main() BEFORE calling this method.
  /// 
  /// This method only configures:
  /// - Firestore settings
  /// - Analytics settings
  /// - Messaging settings
  /// - Auth settings
  Future<void> initialize() async {
    if (_isInitialized) return;

    try {
      // Verify Firebase is initialized - DO NOT initialize it here
      // Firebase must be initialized in main() before this is called
      try {
        Firebase.app(); // This will throw if Firebase is not initialized
        debugPrint('✅ Firebase Core verified - configuring services');
      } catch (e) {
        debugPrint('❌ Firebase Core not initialized. Call Firebase.initializeApp() in main() first.');
        throw StateError(
          'Firebase Core not initialized. Call Firebase.initializeApp() in main() before FirebaseService.initialize()',
        );
      }
      
      // Configure Firebase services (Firebase Core is already initialized)
      await _configureFirestore();
      await _configureAnalytics();
      await _configureMessaging();
      await _configureAuth();

      _isInitialized = true;
      debugPrint('🔥 Firebase services configured successfully');
    } catch (e) {
      debugPrint('❌ Firebase service configuration failed: $e');
      // Don't rethrow - allow app to continue
    }
  }

  Future<void> _configureFirestore() async {
    // Configure settings for better performance
    // Note: enablePersistence() is not available in web, persistence is enabled by default
    firestore.settings = const Settings(
      persistenceEnabled: true,
      cacheSizeBytes: Settings.CACHE_SIZE_UNLIMITED,
    );
  }

  Future<void> _configureAnalytics() async {
    // Set analytics collection enabled
    await analytics.setAnalyticsCollectionEnabled(true);

    // Set user properties
    await analytics.setUserProperty(name: 'app_version', value: '1.0.0');
  }

  Future<void> _configureMessaging() async {
    // Request permission for notifications
    await messaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
      provisional: false,
    );

    // Get FCM token
    final token = await messaging.getToken();
    debugPrint('📱 FCM Token: $token');
  }

  Future<void> _configureAuth() async {
    // Configure auth settings
    await auth.setSettings(appVerificationDisabledForTesting: kDebugMode);
  }

  // Analytics helpers
  Future<void> logEvent(String name, Map<String, dynamic> parameters) async {
    try {
      // Convert dynamic to Object for Firebase Analytics
      final Map<String, Object> convertedParams = parameters.map(
        (key, value) => MapEntry(key, value as Object),
      );
      await analytics.logEvent(name: name, parameters: convertedParams);
    } catch (e) {
      debugPrint('❌ Analytics event failed: $e');
    }
  }

  Future<void> setUserId(String userId) async {
    try {
      await analytics.setUserId(id: userId);
    } catch (e) {
      debugPrint('❌ Set user ID failed: $e');
    }
  }

  Future<void> setUserProperty(String name, String value) async {
    try {
      await analytics.setUserProperty(name: name, value: value);
    } catch (e) {
      debugPrint('❌ Set user property failed: $e');
    }
  }

  // Firestore helpers
  CollectionReference get users => firestore.collection('users');
  CollectionReference get services => firestore.collection('services');
  CollectionReference get appointments => firestore.collection('appointments');
  CollectionReference get reviews => firestore.collection('reviews');
  CollectionReference get loyalty => firestore.collection('loyalty');
  CollectionReference get notifications =>
      firestore.collection('notifications');
  CollectionReference get promotions => firestore.collection('promotions');
  CollectionReference get gallery => firestore.collection('gallery');

  // Storage helpers
  Reference get imagesRef => storage.ref().child('images');
  Reference get avatarsRef => storage.ref().child('avatars');
  Reference get galleryRef => storage.ref().child('gallery');
  Reference get documentsRef => storage.ref().child('documents');

  // Auth helpers
  User? get currentUser => auth.currentUser;
  Stream<User?> get authStateChanges => auth.authStateChanges();

  Future<void> signOut() async {
    await auth.signOut();
  }

  // Error handling
  String getFirebaseErrorMessage(String code) {
    switch (code) {
      case 'user-not-found':
        return 'Aucun utilisateur trouvé avec cet email.';
      case 'wrong-password':
        return 'Mot de passe incorrect.';
      case 'email-already-in-use':
        return 'Cet email est déjà utilisé.';
      case 'weak-password':
        return 'Le mot de passe est trop faible.';
      case 'invalid-email':
        return 'Email invalide.';
      case 'user-disabled':
        return 'Ce compte a été désactivé.';
      case 'too-many-requests':
        return 'Trop de tentatives. Réessayez plus tard.';
      case 'network-request-failed':
        return 'Erreur de connexion. Vérifiez votre internet.';
      default:
        return 'Une erreur est survenue. Réessayez.';
    }
  }
}







