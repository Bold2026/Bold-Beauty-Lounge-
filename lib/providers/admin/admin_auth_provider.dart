import 'package:flutter/foundation.dart';
import 'package:firebase_auth/firebase_auth.dart';
import '../../models/admin/admin_model.dart';
import '../../repositories/admin/admin_repository.dart';

class AdminAuthProvider with ChangeNotifier {
  final AdminRepository _adminRepository;
  final FirebaseAuth _auth;

  AdminModel? _currentAdmin;
  bool _isLoading = false;
  String? _error;

  AdminAuthProvider({
    AdminRepository? adminRepository,
    FirebaseAuth? auth,
  })  : _adminRepository = adminRepository ?? AdminRepository(),
        _auth = auth ?? FirebaseAuth.instance {
    // Initialize auth state listener
    _init();
  }

  AdminModel? get currentAdmin => _currentAdmin;
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get isAuthenticated => _currentAdmin != null;

  void _init() {
    try {
      _auth.authStateChanges().listen((user) async {
        if (user != null) {
          await _loadAdminData(user.uid);
        } else {
          _currentAdmin = null;
          notifyListeners();
        }
      }).onError((error) {
        debugPrint('⚠️ Auth state listener error: $error');
        // Continue without auth state listening
      });
    } catch (e) {
      debugPrint('⚠️ Firebase not initialized, skipping auth state listener: $e');
      // Continue without auth state listening
    }
  }

  Future<void> _loadAdminData(String userId) async {
    try {
      final admin = await _adminRepository.getAdminById(userId);
      if (admin != null && admin.isActive) {
        _currentAdmin = admin;
        await _adminRepository.updateLastLogin(userId);
      } else {
        _currentAdmin = null;
        await _auth.signOut();
      }
      notifyListeners();
    } catch (e) {
      _error = 'Erreur lors du chargement des données admin: $e';
      notifyListeners();
    }
  }

  Future<bool> login(String email, String password) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final credential = await _auth.signInWithEmailAndPassword(
        email: email.trim().toLowerCase(),
        password: password,
      );

      if (credential.user != null) {
        final admin = await _adminRepository.getAdminByEmail(
          email.trim().toLowerCase(),
        );

        if (admin == null || !admin.isActive) {
          await _auth.signOut();
          _error = 'Accès non autorisé. Vous n\'êtes pas administrateur.';
          _isLoading = false;
          notifyListeners();
          return false;
        }

        _currentAdmin = admin;
        await _adminRepository.updateLastLogin(admin.id);
        _isLoading = false;
        notifyListeners();
        return true;
      }

      _isLoading = false;
      notifyListeners();
      return false;
    } on FirebaseAuthException catch (e) {
      _error = _getAuthErrorMessage(e.code);
      _isLoading = false;
      notifyListeners();
      return false;
    } catch (e) {
      _error = 'Erreur de connexion: $e';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    await _auth.signOut();
    _currentAdmin = null;
    _error = null;
    notifyListeners();
  }

  String _getAuthErrorMessage(String code) {
    switch (code) {
      case 'user-not-found':
        return 'Aucun compte trouvé avec cet email.';
      case 'wrong-password':
        return 'Mot de passe incorrect.';
      case 'invalid-email':
        return 'Email invalide.';
      case 'user-disabled':
        return 'Ce compte a été désactivé.';
      case 'too-many-requests':
        return 'Trop de tentatives. Veuillez réessayer plus tard.';
      default:
        return 'Erreur de connexion. Veuillez réessayer.';
    }
  }
}
