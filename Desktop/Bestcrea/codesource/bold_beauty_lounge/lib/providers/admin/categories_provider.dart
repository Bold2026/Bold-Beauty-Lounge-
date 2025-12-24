import 'package:flutter/foundation.dart';
import '../../models/admin/category_model.dart';
import '../../repositories/admin/categories_repository.dart';

class CategoriesProvider with ChangeNotifier {
  final CategoriesRepository _repository;

  List<CategoryModel> _categories = [];
  bool _isLoading = false;
  String? _error;

  CategoriesProvider({CategoriesRepository? repository})
      : _repository = repository ?? CategoriesRepository() {
    loadCategories();
  }

  List<CategoryModel> get categories => _categories;
  List<CategoryModel> get activeCategories =>
      _categories.where((c) => c.isActive).toList();
  bool get isLoading => _isLoading;
  String? get error => _error;

  void loadCategories() {
    _isLoading = true;
    _error = null;
    notifyListeners();

    _repository.getAllCategories().listen(
      (categories) {
        _categories = categories;
        _isLoading = false;
        _error = null;
        notifyListeners();
      },
      onError: (error) {
        _error = 'Erreur lors du chargement des catégories: $error';
        _isLoading = false;
        notifyListeners();
      },
    );
  }

  Future<bool> addCategory(CategoryModel category) async {
    try {
      await _repository.addCategory(category);
      return true;
    } catch (e) {
      _error = 'Erreur lors de l\'ajout: $e';
      notifyListeners();
      return false;
    }
  }

  Future<bool> updateCategory(CategoryModel category) async {
    try {
      await _repository.updateCategory(category);
      return true;
    } catch (e) {
      _error = 'Erreur lors de la mise à jour: $e';
      notifyListeners();
      return false;
    }
  }

  Future<bool> deleteCategory(String categoryId) async {
    try {
      await _repository.deleteCategory(categoryId);
      return true;
    } catch (e) {
      _error = 'Erreur lors de la suppression: $e';
      notifyListeners();
      return false;
    }
  }

  Future<bool> toggleCategoryStatus(String categoryId, bool isActive) async {
    try {
      await _repository.toggleCategoryStatus(categoryId, isActive);
      return true;
    } catch (e) {
      _error = 'Erreur lors de la modification: $e';
      notifyListeners();
      return false;
    }
  }
}




