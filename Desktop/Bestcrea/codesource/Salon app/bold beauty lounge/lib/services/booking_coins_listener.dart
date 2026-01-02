import 'dart:async';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:firebase_auth/firebase_auth.dart';
import 'coins_service.dart';
import '../models/booking_model.dart';

/// Service to listen for booking status changes and earn coins when completed
class BookingCoinsListener {
  final FirebaseFirestore _firestore;
  final CoinsService _coinsService;
  StreamSubscription<QuerySnapshot>? _subscription;

  BookingCoinsListener({
    FirebaseFirestore? firestore,
    CoinsService? coinsService,
  })  : _firestore = firestore ?? FirebaseFirestore.instance,
        _coinsService = coinsService ?? CoinsService();

  /// Start listening for booking status changes for the current user
  void startListening() {
    final user = FirebaseAuth.instance.currentUser;
    if (user == null) return;

    _subscription = _firestore
        .collection('bookings')
        .where('userId', isEqualTo: user.uid)
        .where('status', isEqualTo: 'completed')
        .snapshots()
        .listen((snapshot) {
      for (var change in snapshot.docChanges) {
        if (change.type == DocumentChangeType.added ||
            change.type == DocumentChangeType.modified) {
          _processCompletedBooking(change.doc);
        }
      }
    });
  }

  /// Process a completed booking to earn coins
  Future<void> _processCompletedBooking(DocumentSnapshot doc) async {
    try {
      final booking = BookingModel.fromFirestore(doc);
      final user = FirebaseAuth.instance.currentUser;
      if (user == null || booking.userId != user.uid) return;

      if (booking.status == 'completed' && booking.price != null && booking.price! > 0) {
        // Earn coins (idempotent - service checks for existing transaction)
        await _coinsService.earnFromBooking(
          booking.id,
          booking.userId,
          booking.price!,
        );
      }
    } catch (e) {
      print('Error processing completed booking for coins: $e');
    }
  }

  /// Stop listening
  void stopListening() {
    _subscription?.cancel();
    _subscription = null;
  }
}

