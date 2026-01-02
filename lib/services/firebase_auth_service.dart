import 'package:firebase_auth/firebase_auth.dart';
import 'package:cloud_firestore/cloud_firestore.dart';

class FirebaseAuthService {
  static final FirebaseAuth _auth = FirebaseAuth.instance;
  static final FirebaseFirestore _firestore = FirebaseFirestore.instance;

  // Inscription d'un nouvel utilisateur
  static Future<Map<String, dynamic>> signUp({
    required String firstName,
    required String lastName,
    required String email,
    required String phone,
    required String password,
  }) async {
    try {
      // Créer l'utilisateur avec Firebase Auth
      final UserCredential userCredential = await _auth
          .createUserWithEmailAndPassword(email: email, password: password);

      final User? user = userCredential.user;
      if (user == null) {
        return {
          'success': false,
          'message': 'Erreur lors de la création du compte',
        };
      }

      // Créer le document utilisateur dans Firestore
      final userData = {
        'uid': user.uid,
        'firstName': firstName,
        'lastName': lastName,
        'email': email,
        'phone': phone,
        'createdAt': FieldValue.serverTimestamp(),
        'lastLoginAt': FieldValue.serverTimestamp(),
        'isVerified': false,
        'points': 0,
        'totalSpent': 0,
        'reservationsCount': 0,
        'favorites': [],
        'notifications': true,
        'language': 'fr',
      };

      await _firestore.collection('users').doc(user.uid).set(userData);

      return {
        'success': true,
        'message': 'Compte créé avec succès',
        'user': userData,
      };
    } on FirebaseAuthException catch (e) {
      String message = 'Erreur lors de la création du compte';
      switch (e.code) {
        case 'weak-password':
          message = 'Le mot de passe est trop faible';
          break;
        case 'email-already-in-use':
          message = 'Un compte avec cet email existe déjà';
          break;
        case 'invalid-email':
          message = 'Email invalide';
          break;
      }
      return {'success': false, 'message': message};
    } catch (e) {
      return {
        'success': false,
        'message': 'Erreur lors de la création du compte: $e',
      };
    }
  }

  // Connexion d'un utilisateur
  static Future<Map<String, dynamic>> signIn({
    required String email,
    required String password,
  }) async {
    try {
      final UserCredential userCredential = await _auth
          .signInWithEmailAndPassword(email: email, password: password);

      final User? user = userCredential.user;
      if (user == null) {
        return {'success': false, 'message': 'Erreur lors de la connexion'};
      }

      // Mettre à jour la dernière connexion
      await _firestore.collection('users').doc(user.uid).update({
        'lastLoginAt': FieldValue.serverTimestamp(),
      });

      // Récupérer les données utilisateur
      final userDoc = await _firestore.collection('users').doc(user.uid).get();
      final userData = userDoc.data();

      return {
        'success': true,
        'message': 'Connexion réussie',
        'user': userData,
      };
    } on FirebaseAuthException catch (e) {
      String message = 'Erreur lors de la connexion';
      switch (e.code) {
        case 'user-not-found':
          message = 'Aucun compte trouvé avec cet email';
          break;
        case 'wrong-password':
          message = 'Mot de passe incorrect';
          break;
        case 'invalid-email':
          message = 'Email invalide';
          break;
        case 'user-disabled':
          message = 'Ce compte a été désactivé';
          break;
      }
      return {'success': false, 'message': message};
    } catch (e) {
      return {'success': false, 'message': 'Erreur lors de la connexion: $e'};
    }
  }

  // Déconnexion
  static Future<void> signOut() async {
    await _auth.signOut();
  }

  // Vérifier si l'utilisateur est connecté
  static bool isLoggedIn() {
    return _auth.currentUser != null;
  }

  // Récupérer l'utilisateur actuel
  static Future<Map<String, dynamic>?> getCurrentUser() async {
    final User? user = _auth.currentUser;
    if (user == null) return null;

    try {
      final userDoc = await _firestore.collection('users').doc(user.uid).get();
      return userDoc.data();
    } catch (e) {
      return null;
    }
  }

  // Mettre à jour le profil utilisateur
  static Future<Map<String, dynamic>> updateProfile({
    String? firstName,
    String? lastName,
    String? phone,
  }) async {
    try {
      final User? user = _auth.currentUser;
      if (user == null) {
        return {'success': false, 'message': 'Utilisateur non connecté'};
      }

      final Map<String, dynamic> updateData = {
        'updatedAt': FieldValue.serverTimestamp(),
      };

      if (firstName != null) updateData['firstName'] = firstName;
      if (lastName != null) updateData['lastName'] = lastName;
      if (phone != null) updateData['phone'] = phone;

      await _firestore.collection('users').doc(user.uid).update(updateData);

      return {'success': true, 'message': 'Profil mis à jour avec succès'};
    } catch (e) {
      return {'success': false, 'message': 'Erreur lors de la mise à jour: $e'};
    }
  }

  // Ajouter des points
  static Future<void> addPoints(int points) async {
    final User? user = _auth.currentUser;
    if (user != null) {
      await _firestore.collection('users').doc(user.uid).update({
        'points': FieldValue.increment(points),
        'updatedAt': FieldValue.serverTimestamp(),
      });
    }
  }

  // Mettre à jour les statistiques
  static Future<void> updateStats({
    int? totalSpent,
    int? reservationsCount,
  }) async {
    final User? user = _auth.currentUser;
    if (user != null) {
      final Map<String, dynamic> updateData = {
        'updatedAt': FieldValue.serverTimestamp(),
      };

      if (totalSpent != null) {
        updateData['totalSpent'] = FieldValue.increment(totalSpent);
      }
      if (reservationsCount != null) {
        updateData['reservationsCount'] = FieldValue.increment(
          reservationsCount,
        );
      }

      await _firestore.collection('users').doc(user.uid).update(updateData);
    }
  }

  // Ajouter aux favoris
  static Future<void> addToFavorites(String serviceId) async {
    final User? user = _auth.currentUser;
    if (user != null) {
      await _firestore.collection('users').doc(user.uid).update({
        'favorites': FieldValue.arrayUnion([serviceId]),
        'updatedAt': FieldValue.serverTimestamp(),
      });
    }
  }

  // Retirer des favoris
  static Future<void> removeFromFavorites(String serviceId) async {
    final User? user = _auth.currentUser;
    if (user != null) {
      await _firestore.collection('users').doc(user.uid).update({
        'favorites': FieldValue.arrayRemove([serviceId]),
        'updatedAt': FieldValue.serverTimestamp(),
      });
    }
  }

  // Récupérer les favoris
  static Future<List<String>> getFavorites() async {
    final User? user = _auth.currentUser;
    if (user == null) return [];

    try {
      final userDoc = await _firestore.collection('users').doc(user.uid).get();
      final userData = userDoc.data();
      return List<String>.from(userData?['favorites'] ?? []);
    } catch (e) {
      return [];
    }
  }

  // Réinitialiser le mot de passe
  static Future<Map<String, dynamic>> resetPassword(String email) async {
    try {
      await _auth.sendPasswordResetEmail(email: email);
      return {'success': true, 'message': 'Email de réinitialisation envoyé'};
    } on FirebaseAuthException catch (e) {
      String message = 'Erreur lors de l\'envoi de l\'email';
      switch (e.code) {
        case 'user-not-found':
          message = 'Aucun compte trouvé avec cet email';
          break;
        case 'invalid-email':
          message = 'Email invalide';
          break;
      }
      return {'success': false, 'message': message};
    } catch (e) {
      return {
        'success': false,
        'message': 'Erreur lors de l\'envoi de l\'email: $e',
      };
    }
  }
}
