import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';

class ReservationService {
  static const String _reservationsKey = 'bold_beauty_reservations';

  // Ajouter une nouvelle réservation
  static Future<void> addReservation(Map<String, dynamic> reservation) async {
    final prefs = await SharedPreferences.getInstance();
    final reservations = await getReservations();

    // Générer un ID unique
    final id = DateTime.now().millisecondsSinceEpoch.toString();
    reservation['id'] = id;
    reservation['createdAt'] = DateTime.now().toIso8601String();

    reservations.add(reservation);

    // Sauvegarder dans SharedPreferences
    final reservationsJson = jsonEncode(reservations);
    await prefs.setString(_reservationsKey, reservationsJson);
  }

  // Récupérer toutes les réservations
  static Future<List<Map<String, dynamic>>> getReservations() async {
    final prefs = await SharedPreferences.getInstance();
    final reservationsJson = prefs.getString(_reservationsKey);

    if (reservationsJson == null) {
      return [];
    }

    try {
      final List<dynamic> decoded = jsonDecode(reservationsJson);
      return decoded.cast<Map<String, dynamic>>();
    } catch (e) {
      return [];
    }
  }

  // Récupérer les réservations par statut
  static Future<List<Map<String, dynamic>>> getReservationsByStatus(
    String status,
  ) async {
    final reservations = await getReservations();
    return reservations.where((r) => r['status'] == status).toList();
  }

  // Mettre à jour le statut d'une réservation
  static Future<void> updateReservationStatus(
    String id,
    String newStatus,
  ) async {
    final reservations = await getReservations();
    final index = reservations.indexWhere((r) => r['id'] == id);

    if (index != -1) {
      reservations[index]['status'] = newStatus;

      final prefs = await SharedPreferences.getInstance();
      final reservationsJson = jsonEncode(reservations);
      await prefs.setString(_reservationsKey, reservationsJson);
    }
  }

  // Supprimer une réservation
  static Future<void> deleteReservation(String id) async {
    final reservations = await getReservations();
    reservations.removeWhere((r) => r['id'] == id);

    final prefs = await SharedPreferences.getInstance();
    final reservationsJson = jsonEncode(reservations);
    await prefs.setString(_reservationsKey, reservationsJson);
  }

  // Formater une réservation pour l'affichage
  static Map<String, dynamic> formatReservationForDisplay(
    Map<String, dynamic> reservation,
  ) {
    final date = DateTime.parse(reservation['date']);
    final time = reservation['time'];
    final services = reservation['services'] as List<Map<String, dynamic>>;

    return {
      'id': reservation['id'],
      'service': services.isNotEmpty ? services.first['name'] : 'Service',
      'time': time,
      'date': '${date.day}/${date.month}/${date.year}',
      'specialist': reservation['specialist'] ?? 'Spécialiste',
      'price': reservation['totalPrice'] ?? 0,
      'status': reservation['status'] ?? 'Confirmé',
      'services': services,
      'duration': reservation['totalDuration'] ?? 0,
    };
  }
}
