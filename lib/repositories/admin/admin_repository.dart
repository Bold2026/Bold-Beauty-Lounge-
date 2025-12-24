import 'package:cloud_firestore/cloud_firestore.dart';
import '../../models/admin/admin_model.dart';

class AdminRepository {
  final FirebaseFirestore _firestore;

  AdminRepository({FirebaseFirestore? firestore})
      : _firestore = firestore ?? FirebaseFirestore.instance;

  static const String collectionPath = 'admins';

  // Get admin by email
  Future<AdminModel?> getAdminByEmail(String email) async {
    final querySnapshot = await _firestore
        .collection(collectionPath)
        .where('email', isEqualTo: email.toLowerCase())
        .limit(1)
        .get();

    if (querySnapshot.docs.isEmpty) return null;
    return AdminModel.fromFirestore(querySnapshot.docs.first);
  }

  // Get admin by ID
  Future<AdminModel?> getAdminById(String adminId) async {
    final doc = await _firestore.collection(collectionPath).doc(adminId).get();
    if (!doc.exists) return null;
    return AdminModel.fromFirestore(doc);
  }

  // Check if user is admin
  Future<bool> isAdmin(String userId) async {
    final doc = await _firestore.collection(collectionPath).doc(userId).get();
    if (!doc.exists) return false;
    final admin = AdminModel.fromFirestore(doc);
    return admin.isActive;
  }

  // Update last login
  Future<void> updateLastLogin(String adminId) async {
    await _firestore.collection(collectionPath).doc(adminId).update({
      'lastLoginAt': FieldValue.serverTimestamp(),
    });
  }
}








