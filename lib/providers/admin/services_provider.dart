import 'package:flutter/foundation.dart';
import '../../models/admin/service_model.dart';
import '../../repositories/admin/services_repository.dart';

class ServicesProvider with ChangeNotifier {
  final ServicesRepository _repository;

  List<ServiceModel> _services = [];
  bool _isLoading = false;
  String? _error;

  ServicesProvider({ServicesRepository? repository})
      : _repository = repository ?? ServicesRepository() {
    loadServices();
  }

  List<ServiceModel> get services => _services;
  bool get isLoading => _isLoading;
  String? get error => _error;

  void loadServices() {
    _isLoading = true;
    _error = null;
    notifyListeners();

    _repository.getAllServices().listen(
      (services) {
        _services = services;
        _isLoading = false;
        _error = null;
        notifyListeners();
      },
      onError: (error) {
        _error = 'Erreur lors du chargement des services: $error';
        _isLoading = false;
        notifyListeners();
      },
    );
  }

  Future<bool> addService(ServiceModel service) async {
    try {
      await _repository.addService(service);
      return true;
    } catch (e) {
      _error = 'Erreur lors de l\'ajout: $e';
      notifyListeners();
      return false;
    }
  }

  Future<bool> updateService(ServiceModel service) async {
    try {
      await _repository.updateService(service);
      return true;
    } catch (e) {
      _error = 'Erreur lors de la mise à jour: $e';
      notifyListeners();
      return false;
    }
  }

  Future<bool> deleteService(String serviceId) async {
    try {
      await _repository.deleteService(serviceId);
      return true;
    } catch (e) {
      _error = 'Erreur lors de la suppression: $e';
      notifyListeners();
      return false;
    }
  }

  Future<bool> toggleServiceStatus(String serviceId, bool isActive) async {
    try {
      await _repository.toggleServiceStatus(serviceId, isActive);
      return true;
    } catch (e) {
      _error = 'Erreur lors de la modification: $e';
      notifyListeners();
      return false;
    }
  }
}








