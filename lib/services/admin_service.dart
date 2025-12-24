import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:firebase_auth/firebase_auth.dart';

class AdminService {
  final FirebaseFirestore _firestore = FirebaseFirestore.instance;
  final FirebaseAuth _auth = FirebaseAuth.instance;

  // Vérifier si l'utilisateur est un administrateur
  Future<bool> isAdmin() async {
    try {
      final user = _auth.currentUser;
      if (user == null) return false;

      final userDoc = await _firestore.collection('users').doc(user.uid).get();
      if (!userDoc.exists) return false;

      final userData = userDoc.data();
      return userData?['isAdmin'] == true || userData?['role'] == 'admin';
    } catch (e) {
      return false;
    }
  }

  // Définir un utilisateur comme administrateur
  Future<void> setAdmin(String userId, bool isAdmin) async {
    await _firestore.collection('users').doc(userId).update({
      'isAdmin': isAdmin,
      'role': isAdmin ? 'admin' : 'user',
      'updatedAt': FieldValue.serverTimestamp(),
    });
  }

  // Récupérer tous les administrateurs
  Future<List<Map<String, dynamic>>> getAdmins() async {
    try {
      final snapshot = await _firestore
          .collection('users')
          .where('isAdmin', isEqualTo: true)
          .get();

      return snapshot.docs.map((doc) => {'id': doc.id, ...doc.data()}).toList();
    } catch (e) {
      return [];
    }
  }
}
