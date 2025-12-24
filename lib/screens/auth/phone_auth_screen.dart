import 'package:flutter/material.dart';
import 'package:firebase_auth/firebase_auth.dart';
import '../../services/auth_service.dart';

class PhoneAuthScreen extends StatefulWidget {
  const PhoneAuthScreen({super.key});

  @override
  State<PhoneAuthScreen> createState() => _PhoneAuthScreenState();
}

class _PhoneAuthScreenState extends State<PhoneAuthScreen> {
  final _formKey = GlobalKey<FormState>();
  final _phoneController = TextEditingController();
  final _codeController = TextEditingController();
  final AuthService _authService = AuthService();

  bool _isLoading = false;
  bool _codeSent = false;
  String? _verificationId;
  String? _phoneNumber;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        backgroundColor: Colors.white,
        foregroundColor: Colors.black,
        elevation: 0,
        title: Text(
          _codeSent ? 'Vérification' : 'Connexion par téléphone',
          style: const TextStyle(
            color: Colors.black,
            fontSize: 24,
            fontWeight: FontWeight.bold,
          ),
        ),
        centerTitle: true,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: 40),

              // Logo
              Center(
                child: Container(
                  width: 100,
                  height: 100,
                  decoration: BoxDecoration(
                    color: const Color(0xFFE9D7C2).withOpacity(0.1),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.phone_android,
                    color: Color(0xFFE9D7C2),
                    size: 50,
                  ),
                ),
              ),

              const SizedBox(height: 40),

              // Titre
              Text(
                _codeSent ? 'Entrez le code reçu' : 'Entrez votre numéro',
                style: const TextStyle(
                  fontSize: 28,
                  fontWeight: FontWeight.bold,
                  color: Colors.black,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                _codeSent
                    ? 'Nous avons envoyé un code à $_phoneNumber'
                    : 'Nous vous enverrons un code de vérification',
                style: TextStyle(fontSize: 16, color: Colors.grey[600]),
              ),

              const SizedBox(height: 40),

              if (!_codeSent) ...[
                // Champ téléphone
                TextFormField(
                  controller: _phoneController,
                  keyboardType: TextInputType.phone,
                  decoration: InputDecoration(
                    labelText: 'Numéro de téléphone',
                    hintText: '+212 6XX XXX XXX',
                    prefixIcon: const Icon(Icons.phone_outlined),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: const BorderSide(
                        color: Color(0xFFE9D7C2),
                        width: 2,
                      ),
                    ),
                  ),
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Veuillez entrer votre numéro';
                    }
                    if (value.length < 10) {
                      return 'Numéro de téléphone invalide';
                    }
                    return null;
                  },
                ),

                const SizedBox(height: 30),

                // Bouton envoyer le code
                SizedBox(
                  width: double.infinity,
                  height: 50,
                  child: ElevatedButton(
                    onPressed: _isLoading ? null : _sendVerificationCode,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFFE9D7C2),
                      foregroundColor: Colors.black,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    child: _isLoading
                        ? const CircularProgressIndicator(color: Colors.black)
                        : const Text(
                            'Envoyer le code',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                  ),
                ),
              ] else ...[
                // Champ code de vérification
                TextFormField(
                  controller: _codeController,
                  keyboardType: TextInputType.number,
                  maxLength: 6,
                  decoration: InputDecoration(
                    labelText: 'Code de vérification',
                    hintText: '000000',
                    prefixIcon: const Icon(Icons.lock_outlined),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: const BorderSide(
                        color: Color(0xFFE9D7C2),
                        width: 2,
                      ),
                    ),
                  ),
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Veuillez entrer le code';
                    }
                    if (value.length != 6) {
                      return 'Le code doit contenir 6 chiffres';
                    }
                    return null;
                  },
                ),

                const SizedBox(height: 20),

                // Bouton renvoyer le code
                Center(
                  child: TextButton(
                    onPressed: _isLoading ? null : _resendCode,
                    child: Text(
                      'Renvoyer le code',
                      style: TextStyle(color: Colors.grey[600], fontSize: 14),
                    ),
                  ),
                ),

                const SizedBox(height: 20),

                // Bouton vérifier
                SizedBox(
                  width: double.infinity,
                  height: 50,
                  child: ElevatedButton(
                    onPressed: _isLoading ? null : _verifyCode,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFFE9D7C2),
                      foregroundColor: Colors.black,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    child: _isLoading
                        ? const CircularProgressIndicator(color: Colors.black)
                        : const Text(
                            'Vérifier',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                  ),
                ),
              ],

              const SizedBox(height: 20),

              // Retour
              if (_codeSent)
                Center(
                  child: TextButton(
                    onPressed: () {
                      setState(() {
                        _codeSent = false;
                        _codeController.clear();
                        _verificationId = null;
                      });
                    },
                    child: const Text(
                      'Changer le numéro',
                      style: TextStyle(
                        color: Color(0xFFE9D7C2),
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _sendVerificationCode() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isLoading = true;
    });

    try {
      final phoneNumber = _phoneController.text.trim();
      // Ajouter le préfixe + si nécessaire
      final formattedPhone = phoneNumber.startsWith('+')
          ? phoneNumber
          : '+212${phoneNumber.replaceAll(RegExp(r'[^0-9]'), '')}';

      await FirebaseAuth.instance.verifyPhoneNumber(
        phoneNumber: formattedPhone,
        verificationCompleted: (PhoneAuthCredential credential) async {
          // Auto-verification (Android uniquement)
          try {
            final userCredential = await FirebaseAuth.instance
                .signInWithCredential(credential);
            if (userCredential.user != null && mounted) {
              Navigator.pop(context, true);
            }
          } catch (e) {
            // Ignorer les erreurs d'auto-vérification
          }
        },
        verificationFailed: (FirebaseAuthException e) {
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text('Erreur: ${e.message}'),
                backgroundColor: Colors.red,
              ),
            );
          }
        },
        codeSent: (String verificationId, int? resendToken) {
          setState(() {
            _codeSent = true;
            _verificationId = verificationId;
            _phoneNumber = formattedPhone;
            _isLoading = false;
          });
        },
        codeAutoRetrievalTimeout: (String verificationId) {
          _verificationId = verificationId;
        },
        timeout: const Duration(seconds: 60),
      );
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Erreur: $e'), backgroundColor: Colors.red),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  Future<void> _verifyCode() async {
    if (!_formKey.currentState!.validate()) return;
    if (_verificationId == null || _phoneNumber == null) return;

    setState(() {
      _isLoading = true;
    });

    try {
      final result = await _authService.signInWithPhone(
        phoneNumber: _phoneNumber!,
        verificationId: _verificationId!,
        smsCode: _codeController.text.trim(),
      );

      if (result['success'] && mounted) {
        Navigator.pop(context, true);
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(result['message'] ?? 'Erreur de vérification'),
              backgroundColor: Colors.red,
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Erreur: $e'), backgroundColor: Colors.red),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  Future<void> _resendCode() async {
    await _sendVerificationCode();
  }

  @override
  void dispose() {
    _phoneController.dispose();
    _codeController.dispose();
    super.dispose();
  }
}
