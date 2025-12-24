import 'package:flutter/foundation.dart';
import '../../models/admin/booking_model.dart';
import '../../repositories/admin/bookings_repository.dart';

class BookingsProvider with ChangeNotifier {
  final BookingsRepository _repository;

  List<BookingModel> _bookings = [];
  List<BookingModel> _filteredBookings = [];
  bool _isLoading = false;
  String? _error;
  String? _filterStatus;
  String? _filterServiceId;
  DateTime? _filterDate;
  String _searchQuery = '';

  BookingsProvider({BookingsRepository? repository})
      : _repository = repository ?? BookingsRepository() {
    loadBookings();
  }

  List<BookingModel> get bookings => _searchQuery.isEmpty ? _bookings : _filteredBookings;
  bool get isLoading => _isLoading;
  String? get error => _error;
  String? get filterStatus => _filterStatus;
  String? get filterServiceId => _filterServiceId;
  DateTime? get filterDate => _filterDate;

  void loadBookings() {
    _isLoading = true;
    _error = null;
    notifyListeners();

    Stream<List<BookingModel>> stream;

    if (_filterDate != null) {
      stream = _repository.getBookingsByDate(_filterDate!);
    } else if (_filterServiceId != null) {
      stream = _repository.getBookingsByService(_filterServiceId!);
    } else if (_filterStatus != null) {
      stream = _repository.getBookingsByStatus(_filterStatus!);
    } else {
      stream = _repository.getAllBookings();
    }

    stream.listen(
      (bookings) {
        _bookings = bookings;
        _applySearch();
        _isLoading = false;
        _error = null;
        notifyListeners();
      },
      onError: (error) {
        _error = 'Erreur lors du chargement des réservations: $error';
        _isLoading = false;
        notifyListeners();
      },
    );
  }

  void setFilterStatus(String? status) {
    _filterStatus = status;
    loadBookings();
  }

  void setFilterService(String? serviceId) {
    _filterServiceId = serviceId;
    loadBookings();
  }

  void setFilterDate(DateTime? date) {
    _filterDate = date;
    loadBookings();
  }

  void clearFilters() {
    _filterStatus = null;
    _filterServiceId = null;
    _filterDate = null;
    loadBookings();
  }

  void searchBookings(String query) {
    _searchQuery = query.toLowerCase();
    _applySearch();
    notifyListeners();
  }

  void _applySearch() {
    if (_searchQuery.isEmpty) {
      _filteredBookings = _bookings;
      return;
    }

    _filteredBookings = _bookings.where((booking) {
      return booking.userName.toLowerCase().contains(_searchQuery) ||
          booking.userEmail.toLowerCase().contains(_searchQuery) ||
          booking.userPhone.contains(_searchQuery) ||
          booking.serviceName.toLowerCase().contains(_searchQuery) ||
          (booking.employeeName?.toLowerCase().contains(_searchQuery) ?? false) ||
          booking.id.toLowerCase().contains(_searchQuery);
    }).toList();
  }

  Future<bool> confirmBooking(String bookingId) async {
    try {
      await _repository.updateBookingStatus(bookingId, 'confirmed');
      return true;
    } catch (e) {
      _error = 'Erreur lors de la confirmation: $e';
      notifyListeners();
      return false;
    }
  }

  Future<bool> cancelBooking(String bookingId) async {
    try {
      await _repository.updateBookingStatus(bookingId, 'cancelled');
      return true;
    } catch (e) {
      _error = 'Erreur lors de l\'annulation: $e';
      notifyListeners();
      return false;
    }
  }

  Future<bool> deleteBooking(String bookingId) async {
    try {
      await _repository.deleteBooking(bookingId);
      return true;
    } catch (e) {
      _error = 'Erreur lors de la suppression: $e';
      notifyListeners();
      return false;
    }
  }
}



