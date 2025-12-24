import 'package:cloud_firestore/cloud_firestore.dart';
import '../../models/admin/category_model.dart';

class CategoriesRepository {
  final FirebaseFirestore _firestore;

  CategoriesRepository({FirebaseFirestore? firestore})
      : _firestore = firestore ?? FirebaseFirestore.instance;

  static const String collectionPath = 'categories';

  // Get all categories
  Stream<List<CategoryModel>> getAllCategories() {
    return _firestore
        .collection(collectionPath)
        .orderBy('name')
        .snapshots()
        .map((snapshot) => snapshot.docs
            .map((doc) => CategoryModel.fromFirestore(doc))
            .toList());
  }

  // Get active categories only
  Stream<List<CategoryModel>> getActiveCategories() {
    return _firestore
        .collection(collectionPath)
        .where('isActive', isEqualTo: true)
        .orderBy('name')
        .snapshots()
        .map((snapshot) => snapshot.docs
            .map((doc) => CategoryModel.fromFirestore(doc))
            .toList());
  }

  // Get category by ID
  Future<CategoryModel?> getCategoryById(String categoryId) async {
    final doc = await _firestore
        .collection(collectionPath)
        .doc(categoryId)
        .get();
    if (!doc.exists) return null;
    return CategoryModel.fromFirestore(doc);
  }

  // Add new category
  Future<String> addCategory(CategoryModel category) async {
    final docRef = _firestore.collection(collectionPath).doc();
    await docRef.set(category.copyWith(id: docRef.id).toFirestore());
    return docRef.id;
  }

  // Update category
  Future<void> updateCategory(CategoryModel category) async {
    await _firestore
        .collection(collectionPath)
        .doc(category.id)
        .update(category.toFirestore());
  }

  // Delete category
  Future<void> deleteCategory(String categoryId) async {
    await _firestore.collection(collectionPath).doc(categoryId).delete();
  }

  // Toggle category active status
  Future<void> toggleCategoryStatus(String categoryId, bool isActive) async {
    await _firestore.collection(collectionPath).doc(categoryId).update({
      'isActive': isActive,
      'updatedAt': FieldValue.serverTimestamp(),
    });
  }
}




