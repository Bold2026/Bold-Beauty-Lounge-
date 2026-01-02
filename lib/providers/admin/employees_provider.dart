import 'package:flutter/foundation.dart';
import '../../models/admin/employee_model.dart';
import '../../repositories/admin/employees_repository.dart';

class EmployeesProvider with ChangeNotifier {
  final EmployeesRepository _repository;

  List<EmployeeModel> _employees = [];
  List<EmployeeModel> _filteredEmployees = [];
  bool _isLoading = false;
  String? _error;
  String _searchQuery = '';

  EmployeesProvider({EmployeesRepository? repository})
      : _repository = repository ?? EmployeesRepository() {
    loadEmployees();
  }

  List<EmployeeModel> get employees =>
      _searchQuery.isEmpty ? _employees : _filteredEmployees;
  bool get isLoading => _isLoading;
  String? get error => _error;

  void loadEmployees() {
    _isLoading = true;
    _error = null;
    notifyListeners();

    _repository.getAllEmployees().listen(
      (employees) {
        _employees = employees;
        _applySearch();
        _isLoading = false;
        _error = null;
        notifyListeners();
      },
      onError: (error) {
        _error = 'Erreur lors du chargement des employés: $error';
        _isLoading = false;
        notifyListeners();
      },
    );
  }

  void searchEmployees(String query) {
    _searchQuery = query.toLowerCase();
    _applySearch();
    notifyListeners();
  }

  void _applySearch() {
    if (_searchQuery.isEmpty) {
      _filteredEmployees = _employees;
      return;
    }

    _filteredEmployees = _employees.where((employee) {
      return employee.firstName.toLowerCase().contains(_searchQuery) ||
          employee.email.toLowerCase().contains(_searchQuery) ||
          employee.phone.contains(_searchQuery) ||
          employee.role.toLowerCase().contains(_searchQuery) ||
          employee.employeeId.toLowerCase().contains(_searchQuery);
    }).toList();
  }

  Future<bool> addEmployee(EmployeeModel employee) async {
    try {
      await _repository.addEmployee(employee);
      return true;
    } catch (e) {
      _error = 'Erreur lors de l\'ajout de l\'employé: $e';
      notifyListeners();
      return false;
    }
  }

  Future<bool> updateEmployee(EmployeeModel employee) async {
    try {
      await _repository.updateEmployee(employee);
      return true;
    } catch (e) {
      _error = 'Erreur lors de la mise à jour de l\'employé: $e';
      notifyListeners();
      return false;
    }
  }

  Future<bool> deleteEmployee(String employeeId) async {
    try {
      await _repository.deleteEmployee(employeeId);
      return true;
    } catch (e) {
      _error = 'Erreur lors de la suppression de l\'employé: $e';
      notifyListeners();
      return false;
    }
  }

  Future<bool> toggleEmployeeStatus(String employeeId, String status) async {
    try {
      await _repository.toggleEmployeeStatus(employeeId, status);
      return true;
    } catch (e) {
      _error = 'Erreur lors du changement de statut: $e';
      notifyListeners();
      return false;
    }
  }
}






