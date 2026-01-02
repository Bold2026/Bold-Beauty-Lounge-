import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:bold_beauty_lounge/services/firestore_service.dart';

class BookingRepository {
  final FirestoreService _firestoreService;

  BookingRepository({FirestoreService? firestoreService})
      : _firestoreService = firestoreService ?? FirestoreService();

  static const String bookingsPath = 'bookings';

  Future<void> createBooking(
      {required String bookingId,
      required Map<String, dynamic> bookingData}) async {
    await _firestoreService.setDocument(
        path: bookingsPath, id: bookingId, data: bookingData);
  }

  Future<DocumentSnapshot<Map<String, dynamic>>> getBooking(String bookingId) {
    return _firestoreService.getDocument(path: bookingsPath, id: bookingId);
  }
}
