import 'package:cloud_firestore/cloud_firestore.dart';
import '../../models/admin/booking_model.dart';

class BookingsRepository {
  final FirebaseFirestore _firestore;

  BookingsRepository({FirebaseFirestore? firestore})
      : _firestore = firestore ?? FirebaseFirestore.instance;

  static const String collectionPath = 'bookings';

  // Get all bookings
  Stream<List<BookingModel>> getAllBookings() {
    return _firestore
        .collection(collectionPath)
        .orderBy('date', descending: true)
        .orderBy('time', descending: false)
        .snapshots()
        .map((snapshot) => snapshot.docs
            .map((doc) => BookingModel.fromFirestore(doc))
            .toList());
  }

  // Get bookings filtered by date
  Stream<List<BookingModel>> getBookingsByDate(DateTime date) {
    final startOfDay = DateTime(date.year, date.month, date.day);
    final endOfDay = DateTime(date.year, date.month, date.day, 23, 59, 59);

    return _firestore
        .collection(collectionPath)
        .where('date',
            isGreaterThanOrEqualTo: Timestamp.fromDate(startOfDay))
        .where('date', isLessThanOrEqualTo: Timestamp.fromDate(endOfDay))
        .orderBy('date')
        .orderBy('time')
        .snapshots()
        .map((snapshot) => snapshot.docs
            .map((doc) => BookingModel.fromFirestore(doc))
            .toList());
  }

  // Get bookings filtered by service
  Stream<List<BookingModel>> getBookingsByService(String serviceId) {
    return _firestore
        .collection(collectionPath)
        .where('serviceId', isEqualTo: serviceId)
        .orderBy('date', descending: true)
        .snapshots()
        .map((snapshot) => snapshot.docs
            .map((doc) => BookingModel.fromFirestore(doc))
            .toList());
  }

  // Get bookings filtered by status
  Stream<List<BookingModel>> getBookingsByStatus(String status) {
    return _firestore
        .collection(collectionPath)
        .where('status', isEqualTo: status)
        .orderBy('date', descending: true)
        .snapshots()
        .map((snapshot) => snapshot.docs
            .map((doc) => BookingModel.fromFirestore(doc))
            .toList());
  }

  // Get booking by ID
  Future<BookingModel?> getBookingById(String bookingId) async {
    final doc = await _firestore.collection(collectionPath).doc(bookingId).get();
    if (!doc.exists) return null;
    return BookingModel.fromFirestore(doc);
  }

  // Check if a time slot is available (no double booking)
  Future<bool> isTimeSlotAvailable(DateTime date, String time) async {
    final startOfDay = DateTime(date.year, date.month, date.day);
    final endOfDay = DateTime(date.year, date.month, date.day, 23, 59, 59);

    final querySnapshot = await _firestore
        .collection(collectionPath)
        .where('date',
            isGreaterThanOrEqualTo: Timestamp.fromDate(startOfDay))
        .where('date', isLessThanOrEqualTo: Timestamp.fromDate(endOfDay))
        .where('time', isEqualTo: time)
        .where('status', whereIn: ['pending', 'confirmed'])
        .get();

    return querySnapshot.docs.isEmpty;
  }

  // Update booking status
  Future<void> updateBookingStatus(
    String bookingId,
    String status,
  ) async {
    await _firestore.collection(collectionPath).doc(bookingId).update({
      'status': status,
      'updatedAt': FieldValue.serverTimestamp(),
    });
  }

  // Delete booking
  Future<void> deleteBooking(String bookingId) async {
    await _firestore.collection(collectionPath).doc(bookingId).delete();
  }

  // Get bookings statistics
  Future<Map<String, dynamic>> getBookingsStats({
    DateTime? startDate,
    DateTime? endDate,
  }) async {
    Query query = _firestore.collection(collectionPath);

    if (startDate != null) {
      query = query.where('date',
          isGreaterThanOrEqualTo: Timestamp.fromDate(startDate));
    }
    if (endDate != null) {
      query = query.where('date', isLessThanOrEqualTo: Timestamp.fromDate(endDate));
    }

    final snapshot = await query.get();
    final bookings = snapshot.docs
        .map((doc) => BookingModel.fromFirestore(doc))
        .toList();

    final today = DateTime.now();
    final startOfToday = DateTime(today.year, today.month, today.day);

    final todayBookings = bookings.where((booking) {
      final bookingDate = DateTime(
        booking.date.year,
        booking.date.month,
        booking.date.day,
      );
      return bookingDate.isAtSameMomentAs(startOfToday);
    }).length;

    final thisMonth = DateTime(today.year, today.month, 1);
    final nextMonth = DateTime(today.year, today.month + 1, 1);

    final monthBookings = bookings.where((booking) {
      return booking.date.isAfter(thisMonth.subtract(const Duration(days: 1))) &&
          booking.date.isBefore(nextMonth);
    }).length;

    // Most booked service
    final serviceCounts = <String, int>{};
    for (var booking in bookings) {
      serviceCounts[booking.serviceId] =
          (serviceCounts[booking.serviceId] ?? 0) + 1;
    }

    final mostBookedService = serviceCounts.isEmpty
        ? null
        : serviceCounts.entries
            .reduce((a, b) => a.value > b.value ? a : b)
            .key;

    return {
      'total': bookings.length,
      'today': todayBookings,
      'thisMonth': monthBookings,
      'mostBookedServiceId': mostBookedService,
    };
  }
}

