import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:firebase_auth/firebase_auth.dart';

class BookingService {
  final FirebaseFirestore _firestore = FirebaseFirestore.instance;
  final FirebaseAuth _auth = FirebaseAuth.instance;

  // Créer une réservation
  Future<Map<String, dynamic>> createBooking({
    required String serviceId,
    required String serviceName,
    required double servicePrice,
    required DateTime selectedDate,
    required String selectedTime,
    required String? specialistId,
    String? notes,
  }) async {
    try {
      final user = _auth.currentUser;
      if (user == null) {
        return {
          'success': false,
          'message': 'Vous devez être connecté pour réserver',
        };
      }

      // Vérifier si le créneau est disponible
      final isAvailable = await _checkTimeSlotAvailability(
        selectedDate,
        selectedTime,
        specialistId,
      );

      if (!isAvailable) {
        return {
          'success': false,
          'message': 'Ce créneau n\'est plus disponible',
        };
      }

      // Créer la réservation
      final bookingRef = _firestore.collection('bookings').doc();
      final bookingData = {
        'id': bookingRef.id,
        'userId': user.uid,
        'serviceId': serviceId,
        'serviceName': serviceName,
        'servicePrice': servicePrice,
        'selectedDate': Timestamp.fromDate(selectedDate),
        'selectedTime': selectedTime,
        'specialistId': specialistId,
        'notes': notes ?? '',
        'status': 'pending', // pending, confirmed, completed, cancelled
        'createdAt': FieldValue.serverTimestamp(),
        'updatedAt': FieldValue.serverTimestamp(),
      };

      await bookingRef.set(bookingData);

      // Mettre à jour les statistiques utilisateur
      await _updateUserStats(user.uid, servicePrice);

      return {
        'success': true,
        'message': 'Réservation créée avec succès',
        'bookingId': bookingRef.id,
        'booking': bookingData,
      };
    } catch (e) {
      return {
        'success': false,
        'message': 'Erreur lors de la création de la réservation: $e',
      };
    }
  }

  // Vérifier la disponibilité d'un créneau
  Future<bool> _checkTimeSlotAvailability(
    DateTime date,
    String time,
    String? specialistId,
  ) async {
    try {
      final dateStr =
          '${date.year}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}';

      final query = _firestore
          .collection('bookings')
          .where('selectedDate', isEqualTo: Timestamp.fromDate(date))
          .where('selectedTime', isEqualTo: time)
          .where('status', whereIn: ['pending', 'confirmed']);

      if (specialistId != null) {
        query.where('specialistId', isEqualTo: specialistId);
      }

      final snapshot = await query.get();

      // Limiter à 1 réservation par créneau (peut être ajusté)
      return snapshot.docs.isEmpty;
    } catch (e) {
      return false;
    }
  }

  // Récupérer les réservations d'un utilisateur
  Future<List<Map<String, dynamic>>> getUserBookings() async {
    try {
      final user = _auth.currentUser;
      if (user == null) return [];

      final snapshot = await _firestore
          .collection('bookings')
          .where('userId', isEqualTo: user.uid)
          .orderBy('selectedDate', descending: false)
          .get();

      return snapshot.docs.map((doc) {
        final data = doc.data();
        return {
          'id': doc.id,
          ...data,
          'selectedDate': (data['selectedDate'] as Timestamp).toDate(),
        };
      }).toList();
    } catch (e) {
      return [];
    }
  }

  // Récupérer les créneaux disponibles pour une date
  Future<List<String>> getAvailableTimeSlots(DateTime date) async {
    // Créneaux par défaut (peut être récupéré depuis Firestore)
    final defaultSlots = [
      '09:00',
      '10:00',
      '11:00',
      '12:00',
      '14:00',
      '15:00',
      '16:00',
      '17:00',
      '18:00',
      '19:00',
    ];

    try {
      // Récupérer les réservations pour cette date
      final snapshot = await _firestore
          .collection('bookings')
          .where('selectedDate', isEqualTo: Timestamp.fromDate(date))
          .where('status', whereIn: ['pending', 'confirmed'])
          .get();

      final bookedSlots = snapshot.docs
          .map((doc) => doc.data()['selectedTime'] as String)
          .toSet();

      // Retourner les créneaux disponibles
      return defaultSlots.where((slot) => !bookedSlots.contains(slot)).toList();
    } catch (e) {
      return defaultSlots;
    }
  }

  // Annuler une réservation
  Future<Map<String, dynamic>> cancelBooking(String bookingId) async {
    try {
      final user = _auth.currentUser;
      if (user == null) {
        return {'success': false, 'message': 'Vous devez être connecté'};
      }

      final bookingRef = _firestore.collection('bookings').doc(bookingId);
      final bookingDoc = await bookingRef.get();

      if (!bookingDoc.exists) {
        return {'success': false, 'message': 'Réservation introuvable'};
      }

      final bookingData = bookingDoc.data()!;
      if (bookingData['userId'] != user.uid) {
        return {
          'success': false,
          'message': 'Vous n\'êtes pas autorisé à annuler cette réservation',
        };
      }

      await bookingRef.update({
        'status': 'cancelled',
        'updatedAt': FieldValue.serverTimestamp(),
      });

      return {'success': true, 'message': 'Réservation annulée avec succès'};
    } catch (e) {
      return {'success': false, 'message': 'Erreur lors de l\'annulation: $e'};
    }
  }

  // Mettre à jour les statistiques utilisateur
  Future<void> _updateUserStats(String userId, double amount) async {
    try {
      final userRef = _firestore.collection('users').doc(userId);
      await userRef.update({
        'reservationsCount': FieldValue.increment(1),
        'totalSpent': FieldValue.increment(amount),
        'updatedAt': FieldValue.serverTimestamp(),
      });
    } catch (e) {
      // Ignorer les erreurs de mise à jour des stats
    }
  }

  // Récupérer les services disponibles
  Future<List<Map<String, dynamic>>> getAvailableServices() async {
    try {
      final snapshot = await _firestore.collection('services').get();
      return snapshot.docs.map((doc) {
        final data = doc.data();
        return {'id': doc.id, ...data};
      }).toList();
    } catch (e) {
      return [];
    }
  }
}
