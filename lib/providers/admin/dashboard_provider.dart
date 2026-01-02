import 'package:flutter/foundation.dart';
import '../../repositories/admin/bookings_repository.dart';

class DashboardProvider with ChangeNotifier {
  final BookingsRepository _repository;

  int _todayBookings = 0;
  int _monthBookings = 0;
  String? _mostBookedServiceId;
  String? _mostBookedServiceName;
  bool _isLoading = false;
  String? _error;

  DashboardProvider({BookingsRepository? repository})
      : _repository = repository ?? BookingsRepository() {
    loadStats();
  }

  int get todayBookings => _todayBookings;
  int get monthBookings => _monthBookings;
  String? get mostBookedServiceId => _mostBookedServiceId;
  String? get mostBookedServiceName => _mostBookedServiceName;
  bool get isLoading => _isLoading;
  String? get error => _error;

  Future<void> loadStats() async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final stats = await _repository.getBookingsStats();
      _todayBookings = stats['today'] ?? 0;
      _monthBookings = stats['thisMonth'] ?? 0;
      _mostBookedServiceId = stats['mostBookedServiceId'];

      // Service name will be fetched in the UI from ServicesProvider

      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _error = 'Erreur lors du chargement des statistiques: $e';
      _isLoading = false;
      notifyListeners();
    }
  }

  void refresh() {
    loadStats();
  }
}

