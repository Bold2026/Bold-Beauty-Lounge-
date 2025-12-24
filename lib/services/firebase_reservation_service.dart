import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:firebase_auth/firebase_auth.dart';

class FirebaseReservationService {
  static final FirebaseFirestore _firestore = FirebaseFirestore.instance;
  static final FirebaseAuth _auth = FirebaseAuth.instance;

  // Ajouter une nouvelle réservation
  static Future<Map<String, dynamic>> addReservation(
    Map<String, dynamic> reservation,
  ) async {
    try {
      final User? user = _auth.currentUser;
      if (user == null) {
        return {'success': false, 'message': 'Utilisateur non connecté'};
      }

      // Ajouter l'ID utilisateur et la date de création
      reservation['userId'] = user.uid;
      reservation['createdAt'] = FieldValue.serverTimestamp();
      reservation['updatedAt'] = FieldValue.serverTimestamp();

      // Ajouter la réservation à Firestore
      final docRef = await _firestore
          .collection('reservations')
          .add(reservation);

      // Mettre à jour les statistiques utilisateur
      await _updateUserStats(reservation);

      return {
        'success': true,
        'message': 'Réservation créée avec succès',
        'reservationId': docRef.id,
      };
    } catch (e) {
      return {
        'success': false,
        'message': 'Erreur lors de la création de la réservation: $e',
      };
    }
  }

  // Récupérer toutes les réservations de l'utilisateur
  static Future<List<Map<String, dynamic>>> getReservations() async {
    try {
      final User? user = _auth.currentUser;
      if (user == null) return [];

      final QuerySnapshot snapshot = await _firestore
          .collection('reservations')
          .where('userId', isEqualTo: user.uid)
          .orderBy('createdAt', descending: true)
          .get();

      return snapshot.docs.map((doc) {
        final data = doc.data() as Map<String, dynamic>;
        data['id'] = doc.id;
        return data;
      }).toList();
    } catch (e) {
      print('Erreur lors de la récupération des réservations: $e');
      return [];
    }
  }

  // Récupérer les réservations par statut
  static Future<List<Map<String, dynamic>>> getReservationsByStatus(
    String status,
  ) async {
    try {
      final User? user = _auth.currentUser;
      if (user == null) return [];

      final QuerySnapshot snapshot = await _firestore
          .collection('reservations')
          .where('userId', isEqualTo: user.uid)
          .where('status', isEqualTo: status)
          .orderBy('createdAt', descending: true)
          .get();

      return snapshot.docs.map((doc) {
        final data = doc.data() as Map<String, dynamic>;
        data['id'] = doc.id;
        return data;
      }).toList();
    } catch (e) {
      print('Erreur lors de la récupération des réservations: $e');
      return [];
    }
  }

  // Mettre à jour le statut d'une réservation
  static Future<Map<String, dynamic>> updateReservationStatus(
    String id,
    String newStatus,
  ) async {
    try {
      await _firestore.collection('reservations').doc(id).update({
        'status': newStatus,
        'updatedAt': FieldValue.serverTimestamp(),
      });

      return {'success': true, 'message': 'Statut mis à jour avec succès'};
    } catch (e) {
      return {'success': false, 'message': 'Erreur lors de la mise à jour: $e'};
    }
  }

  // Supprimer une réservation
  static Future<Map<String, dynamic>> deleteReservation(String id) async {
    try {
      await _firestore.collection('reservations').doc(id).delete();

      return {'success': true, 'message': 'Réservation supprimée avec succès'};
    } catch (e) {
      return {'success': false, 'message': 'Erreur lors de la suppression: $e'};
    }
  }

  // Formater une réservation pour l'affichage
  static Map<String, dynamic> formatReservationForDisplay(
    Map<String, dynamic> reservation,
  ) {
    final date = (reservation['date'] as Timestamp).toDate();
    final time = reservation['time'];
    final services = reservation['services'] as List<Map<String, dynamic>>;

    return {
      'id': reservation['id'],
      'serviceName': services.map((s) => s['name']).join(', '),
      'totalPrice': reservation['totalPrice'],
      'totalDuration': reservation['totalDuration'],
      'date': '${date.day}/${date.month}/${date.year}',
      'time': time,
      'specialist': reservation['specialist'],
      'customerName': reservation['customerName'],
      'customerEmail': reservation['customerEmail'],
      'customerPhone': reservation['customerPhone'],
      'status': reservation['status'],
      'createdAt': reservation['createdAt'],
    };
  }

  // Mettre à jour les statistiques utilisateur
  static Future<void> _updateUserStats(Map<String, dynamic> reservation) async {
    try {
      final User? user = _auth.currentUser;
      if (user == null) return;

      final int totalPrice = reservation['totalPrice'] ?? 0;
      final int points = totalPrice; // 1 DH = 1 point

      await _firestore.collection('users').doc(user.uid).update({
        'reservationsCount': FieldValue.increment(1),
        'totalSpent': FieldValue.increment(totalPrice),
        'points': FieldValue.increment(points),
        'updatedAt': FieldValue.serverTimestamp(),
      });
    } catch (e) {
      print('Erreur lors de la mise à jour des statistiques: $e');
    }
  }

  // Récupérer les réservations à venir
  static Future<List<Map<String, dynamic>>> getUpcomingReservations() async {
    try {
      final User? user = _auth.currentUser;
      if (user == null) return [];

      final now = Timestamp.now();
      final QuerySnapshot snapshot = await _firestore
          .collection('reservations')
          .where('userId', isEqualTo: user.uid)
          .where('status', whereIn: ['Confirmé', 'En attente'])
          .orderBy('date', descending: false)
          .get();

      return snapshot.docs.map((doc) {
        final data = doc.data() as Map<String, dynamic>;
        data['id'] = doc.id;
        return data;
      }).toList();
    } catch (e) {
      print('Erreur lors de la récupération des réservations à venir: $e');
      return [];
    }
  }

  // Récupérer les réservations passées
  static Future<List<Map<String, dynamic>>> getPastReservations() async {
    try {
      final User? user = _auth.currentUser;
      if (user == null) return [];

      final QuerySnapshot snapshot = await _firestore
          .collection('reservations')
          .where('userId', isEqualTo: user.uid)
          .where('status', isEqualTo: 'Terminé')
          .orderBy('date', descending: true)
          .get();

      return snapshot.docs.map((doc) {
        final data = doc.data() as Map<String, dynamic>;
        data['id'] = doc.id;
        return data;
      }).toList();
    } catch (e) {
      print('Erreur lors de la récupération des réservations passées: $e');
      return [];
    }
  }

  // Récupérer les réservations annulées
  static Future<List<Map<String, dynamic>>> getCancelledReservations() async {
    try {
      final User? user = _auth.currentUser;
      if (user == null) return [];

      final QuerySnapshot snapshot = await _firestore
          .collection('reservations')
          .where('userId', isEqualTo: user.uid)
          .where('status', isEqualTo: 'Annulé')
          .orderBy('date', descending: true)
          .get();

      return snapshot.docs.map((doc) {
        final data = doc.data() as Map<String, dynamic>;
        data['id'] = doc.id;
        return data;
      }).toList();
    } catch (e) {
      print('Erreur lors de la récupération des réservations annulées: $e');
      return [];
    }
  }
}
