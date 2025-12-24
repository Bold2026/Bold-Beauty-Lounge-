import 'package:flutter/material.dart';
import '../../models/admin/customer_model.dart';
import '../../repositories/admin/customers_repository.dart';

class CustomersProvider extends ChangeNotifier {
  final CustomersRepository _repository;

  CustomersProvider({CustomersRepository? repository})
      : _repository = repository ?? CustomersRepository();

  List<CustomerModel> _customers = [];
  bool _isLoading = false;
  String? _error;
  String _searchQuery = '';

  List<CustomerModel> get customers => _customers;
  bool get isLoading => _isLoading;
  String? get error => _error;
  String get searchQuery => _searchQuery;

  // Filtered customers based on search query
  List<CustomerModel> get filteredCustomers {
    if (_searchQuery.isEmpty) return _customers;

    final query = _searchQuery.toLowerCase();
    return _customers.where((customer) {
      return customer.name.toLowerCase().contains(query) ||
          customer.email.toLowerCase().contains(query) ||
          customer.phone.contains(query) ||
          customer.customerId.toLowerCase().contains(query);
    }).toList();
  }

  // Load all customers
  void loadCustomers() {
    _isLoading = true;
    _error = null;
    notifyListeners();

    _repository.getAllCustomers().listen(
      (customers) {
        _customers = customers;
        _isLoading = false;
        _error = null;
        notifyListeners();
      },
      onError: (error) {
        _isLoading = false;
        _error = error.toString();
        notifyListeners();
      },
    );
  }

  // Set search query
  void setSearchQuery(String query) {
    _searchQuery = query;
    notifyListeners();
  }

  // Add new customer
  Future<bool> addCustomer(CustomerModel customer) async {
    try {
      _isLoading = true;
      _error = null;
      notifyListeners();

      await _repository.addCustomer(customer);
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _isLoading = false;
      _error = e.toString();
      notifyListeners();
      return false;
    }
  }

  // Update customer
  Future<bool> updateCustomer(CustomerModel customer) async {
    try {
      _isLoading = true;
      _error = null;
      notifyListeners();

      await _repository.updateCustomer(customer);
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _isLoading = false;
      _error = e.toString();
      notifyListeners();
      return false;
    }
  }

  // Delete customer
  Future<bool> deleteCustomer(String customerId) async {
    try {
      _isLoading = true;
      _error = null;
      notifyListeners();

      await _repository.deleteCustomer(customerId);
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _isLoading = false;
      _error = e.toString();
      notifyListeners();
      return false;
    }
  }

  // Update last visit
  Future<bool> updateLastVisit(String customerId, DateTime visitDate) async {
    try {
      await _repository.updateLastVisit(customerId, visitDate);
      notifyListeners();
      return true;
    } catch (e) {
      _error = e.toString();
      notifyListeners();
      return false;
    }
  }
}




