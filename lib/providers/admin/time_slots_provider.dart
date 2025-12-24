import 'package:flutter/foundation.dart';
import '../../models/admin/time_slot_model.dart';
import '../../repositories/admin/time_slots_repository.dart';

class TimeSlotsProvider with ChangeNotifier {
  final TimeSlotsRepository _repository;

  TimeSlotModel? _timeSlot;
  bool _isLoading = false;
  String? _error;

  TimeSlotsProvider({TimeSlotsRepository? repository})
      : _repository = repository ?? TimeSlotsRepository() {
    loadTimeSlot();
  }

  TimeSlotModel? get timeSlot => _timeSlot;
  bool get isLoading => _isLoading;
  String? get error => _error;

  Future<void> loadTimeSlot() async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _timeSlot = await _repository.getTimeSlot();
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _error = 'Erreur lors du chargement: $e';
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> updateTimeSlot(TimeSlotModel timeSlot) async {
    try {
      await _repository.updateTimeSlot(timeSlot);
      await loadTimeSlot();
      return true;
    } catch (e) {
      _error = 'Erreur lors de la mise à jour: $e';
      notifyListeners();
      return false;
    }
  }

  Future<bool> addDisabledDate(DateTime date) async {
    try {
      await _repository.addDisabledDate(date);
      await loadTimeSlot();
      return true;
    } catch (e) {
      _error = 'Erreur lors de l\'ajout: $e';
      notifyListeners();
      return false;
    }
  }

  Future<bool> removeDisabledDate(DateTime date) async {
    try {
      await _repository.removeDisabledDate(date);
      await loadTimeSlot();
      return true;
    } catch (e) {
      _error = 'Erreur lors de la suppression: $e';
      notifyListeners();
      return false;
    }
  }
}








