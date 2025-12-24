import 'package:firebase_auth/firebase_auth.dart';
import 'package:cloud_firestore/cloud_firestore.dart';

class AuthService {
  final FirebaseAuth _firebaseAuth = FirebaseAuth.instance;
  final FirebaseFirestore _firestore = FirebaseFirestore.instance;

  // Inscription avec email et mot de passe
  Future<Map<String, dynamic>> signUpWithEmail({
    required String firstName,
    required String lastName,
    required String email,
    required String phone,
    required String password,
  }) async {
    try {
      // Créer l'utilisateur dans Firebase Auth
      final UserCredential userCredential = await _firebaseAuth
          .createUserWithEmailAndPassword(email: email, password: password);

      final User? user = userCredential.user;

      if (user != null) {
        // Mettre à jour le profil avec le nom
        await user.updateDisplayName('$firstName $lastName');

        // Créer le document utilisateur dans Firestore
        await _firestore.collection('users').doc(user.uid).set({
          'uid': user.uid,
          'firstName': firstName,
          'lastName': lastName,
          'email': email,
          'phone': phone,
          'createdAt': FieldValue.serverTimestamp(),
          'isVerified': false,
          'points': 0,
          'totalSpent': 0,
          'reservationsCount': 0,
          'photoURL': null,
        });

        return {
          'success': true,
          'message': 'Compte créé avec succès',
          'user': user,
        };
      }

      return {
        'success': false,
        'message': 'Erreur lors de la création du compte',
      };
    } on FirebaseAuthException catch (e) {
      String message = 'Erreur lors de l\'inscription';
      if (e.code == 'weak-password') {
        message = 'Le mot de passe est trop faible';
      } else if (e.code == 'email-already-in-use') {
        message = 'Un compte existe déjà avec cet email';
      } else if (e.code == 'invalid-email') {
        message = 'Email invalide';
      }
      return {'success': false, 'message': message};
    } catch (e) {
      return {'success': false, 'message': 'Erreur: $e'};
    }
  }

  // Connexion avec email et mot de passe
  Future<Map<String, dynamic>> signInWithEmail({
    required String email,
    required String password,
  }) async {
    try {
      final UserCredential userCredential = await _firebaseAuth
          .signInWithEmailAndPassword(email: email, password: password);

      final User? user = userCredential.user;

      if (user != null) {
        // Mettre à jour la dernière connexion
        await _firestore.collection('users').doc(user.uid).update({
          'lastLoginAt': FieldValue.serverTimestamp(),
        });

        return {'success': true, 'message': 'Connexion réussie', 'user': user};
      }

      return {'success': false, 'message': 'Erreur lors de la connexion'};
    } on FirebaseAuthException catch (e) {
      String message = 'Erreur lors de la connexion';
      if (e.code == 'user-not-found') {
        message = 'Aucun compte trouvé avec cet email';
      } else if (e.code == 'wrong-password') {
        message = 'Mot de passe incorrect';
      } else if (e.code == 'invalid-email') {
        message = 'Email invalide';
      } else if (e.code == 'user-disabled') {
        message = 'Ce compte a été désactivé';
      }
      return {'success': false, 'message': message};
    } catch (e) {
      return {'success': false, 'message': 'Erreur: $e'};
    }
  }

  // Inscription/Connexion avec téléphone
  Future<Map<String, dynamic>> signInWithPhone({
    required String phoneNumber,
    required String verificationId,
    required String smsCode,
  }) async {
    try {
      // Créer les credentials
      final PhoneAuthCredential credential = PhoneAuthProvider.credential(
        verificationId: verificationId,
        smsCode: smsCode,
      );

      // Se connecter avec les credentials
      final UserCredential userCredential = await _firebaseAuth
          .signInWithCredential(credential);

      final User? user = userCredential.user;

      if (user != null) {
        // Vérifier si l'utilisateur existe déjà dans Firestore
        final userDoc = await _firestore
            .collection('users')
            .doc(user.uid)
            .get();

        if (!userDoc.exists) {
          // Créer le document utilisateur si c'est un nouvel utilisateur
          await _firestore.collection('users').doc(user.uid).set({
            'uid': user.uid,
            'phone': phoneNumber,
            'createdAt': FieldValue.serverTimestamp(),
            'isVerified': true, // Le téléphone est vérifié
            'points': 0,
            'totalSpent': 0,
            'reservationsCount': 0,
            'photoURL': null,
          });
        } else {
          // Mettre à jour la dernière connexion
          await _firestore.collection('users').doc(user.uid).update({
            'lastLoginAt': FieldValue.serverTimestamp(),
          });
        }

        return {'success': true, 'message': 'Connexion réussie', 'user': user};
      }

      return {'success': false, 'message': 'Erreur lors de la connexion'};
    } on FirebaseAuthException catch (e) {
      String message = 'Erreur lors de la connexion';
      if (e.code == 'invalid-verification-code') {
        message = 'Code de vérification invalide';
      } else if (e.code == 'session-expired') {
        message = 'La session a expiré. Veuillez réessayer';
      }
      return {'success': false, 'message': message};
    } catch (e) {
      return {'success': false, 'message': 'Erreur: $e'};
    }
  }

  // Méthode statique pour compatibilité
  static Future<Map<String, dynamic>> signInWithPhoneStatic({
    required String phoneNumber,
    required String verificationId,
    required String smsCode,
  }) async {
    final service = AuthService();
    return await service.signInWithPhone(
      phoneNumber: phoneNumber,
      verificationId: verificationId,
      smsCode: smsCode,
    );
  }

  // Envoyer le code de vérification par SMS
  Future<Map<String, dynamic>> sendPhoneVerificationCode(
    String phoneNumber,
  ) async {
    try {
      await _firebaseAuth.verifyPhoneNumber(
        phoneNumber: phoneNumber,
        verificationCompleted: (PhoneAuthCredential credential) {
          // Auto-verification (Android uniquement)
        },
        verificationFailed: (FirebaseAuthException e) {
          // Géré dans le catch
        },
        codeSent: (String verificationId, int? resendToken) {
          // Le code sera géré par l'écran de vérification
        },
        codeAutoRetrievalTimeout: (String verificationId) {
          // Timeout
        },
        timeout: const Duration(seconds: 60),
      );

      return {'success': true, 'message': 'Code de vérification envoyé'};
    } catch (e) {
      return {
        'success': false,
        'message': 'Erreur lors de l\'envoi du code: $e',
      };
    }
  }

  // Déconnexion
  Future<void> signOut() async {
    await _firebaseAuth.signOut();
  }

  // Récupérer l'utilisateur actuel
  User? getCurrentUser() {
    return _firebaseAuth.currentUser;
  }

  // Stream de l'état d'authentification
  Stream<User?> authStateChanges() {
    return _firebaseAuth.authStateChanges();
  }

  // Récupérer les données utilisateur depuis Firestore
  Future<Map<String, dynamic>?> getUserData(String uid) async {
    try {
      final doc = await _firestore.collection('users').doc(uid).get();
      if (doc.exists) {
        return doc.data();
      }
      return null;
    } catch (e) {
      return null;
    }
  }

  // Mettre à jour le profil utilisateur
  Future<Map<String, dynamic>> updateProfile({
    String? firstName,
    String? lastName,
    String? phone,
    String? photoURL,
  }) async {
    try {
      final user = _firebaseAuth.currentUser;
      if (user == null) {
        return {'success': false, 'message': 'Utilisateur non connecté'};
      }

      final Map<String, dynamic> updates = {};
      if (firstName != null) updates['firstName'] = firstName;
      if (lastName != null) updates['lastName'] = lastName;
      if (phone != null) updates['phone'] = phone;
      if (photoURL != null) updates['photoURL'] = photoURL;

      updates['updatedAt'] = FieldValue.serverTimestamp();

      await _firestore.collection('users').doc(user.uid).update(updates);

      // Mettre à jour le displayName dans Firebase Auth
      if (firstName != null && lastName != null) {
        await user.updateDisplayName('$firstName $lastName');
      }

      return {'success': true, 'message': 'Profil mis à jour avec succès'};
    } catch (e) {
      return {'success': false, 'message': 'Erreur lors de la mise à jour: $e'};
    }
  }

  // Vérifier si l'utilisateur est connecté
  Future<bool> isLoggedIn() async {
    return _firebaseAuth.currentUser != null;
  }

  // Méthodes de compatibilité avec l'ancien code
  static Future<Map<String, dynamic>> signUp({
    required String firstName,
    required String lastName,
    required String email,
    required String phone,
    required String password,
  }) async {
    final service = AuthService();
    return await service.signUpWithEmail(
      firstName: firstName,
      lastName: lastName,
      email: email,
      phone: phone,
      password: password,
    );
  }

  static Future<Map<String, dynamic>> signIn({
    required String email,
    required String password,
  }) async {
    final service = AuthService();
    return await service.signInWithEmail(email: email, password: password);
  }

  static Future<void> signOutStatic() async {
    final service = AuthService();
    await service.signOut();
  }

  static Future<bool> isLoggedInStatic() async {
    final service = AuthService();
    return await service.isLoggedIn();
  }

  static Future<Map<String, dynamic>?> getCurrentUserStatic() async {
    final service = AuthService();
    final user = service.getCurrentUser();
    if (user != null) {
      return await service.getUserData(user.uid);
    }
    return null;
  }
}
