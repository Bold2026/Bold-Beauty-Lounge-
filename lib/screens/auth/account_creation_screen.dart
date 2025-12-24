import 'package:flutter/material.dart';
import '../../services/reservation_service.dart';

class AccountCreationScreen extends StatefulWidget {
  final List<Map<String, dynamic>> selectedServices;
  final int totalPrice;
  final int totalDuration;
  final DateTime selectedDate;
  final String selectedTime;
  final String selectedEmployee;

  const AccountCreationScreen({
    super.key,
    required this.selectedServices,
    required this.totalPrice,
    required this.totalDuration,
    required this.selectedDate,
    required this.selectedTime,
    required this.selectedEmployee,
  });

  @override
  State<AccountCreationScreen> createState() => _AccountCreationScreenState();
}

class _AccountCreationScreenState extends State<AccountCreationScreen> {
  final _formKey = GlobalKey<FormState>();
  final _firstNameController = TextEditingController();
  final _lastNameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  bool _isLoading = false;
  bool _acceptTerms = false;
  bool _obscurePassword = true;
  bool _obscureConfirmPassword = true;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        backgroundColor: Colors.white,
        foregroundColor: Colors.black,
        elevation: 0,
        title: const Text(
          'Créer un compte',
          style: TextStyle(
            color: Colors.black,
            fontSize: 18,
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
              // Résumé de la réservation
              _buildReservationSummary(),

              const SizedBox(height: 30),

              // Formulaire de création de compte
              _buildAccountForm(),

              const SizedBox(height: 30),

              // Bouton de création de compte
              _buildCreateAccountButton(),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildReservationSummary() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFFE9D7C2).withOpacity(0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE9D7C2).withOpacity(0.3)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Résumé de votre réservation',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: Colors.black,
            ),
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              const Icon(
                Icons.calendar_today,
                color: Color(0xFFE9D7C2),
                size: 16,
              ),
              const SizedBox(width: 8),
              Text(
                '${widget.selectedDate.day}/${widget.selectedDate.month}/${widget.selectedDate.year} à ${widget.selectedTime}',
                style: const TextStyle(fontSize: 14, color: Colors.black),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              const Icon(Icons.person, color: Color(0xFFE9D7C2), size: 16),
              const SizedBox(width: 8),
              Text(
                'Avec ${widget.selectedEmployee}',
                style: const TextStyle(fontSize: 14, color: Colors.black),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              const Icon(Icons.access_time, color: Color(0xFFE9D7C2), size: 16),
              const SizedBox(width: 8),
              Text(
                '${widget.totalDuration} min - ${widget.totalPrice} DH',
                style: const TextStyle(fontSize: 14, color: Colors.black),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildAccountForm() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Informations personnelles',
          style: TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.bold,
            color: Colors.black,
          ),
        ),
        const SizedBox(height: 20),

        // Prénom et Nom
        Row(
          children: [
            Expanded(
              child: _buildTextField(
                controller: _firstNameController,
                label: 'Prénom',
                icon: Icons.person_outline,
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Veuillez entrer votre prénom';
                  }
                  return null;
                },
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildTextField(
                controller: _lastNameController,
                label: 'Nom',
                icon: Icons.person_outline,
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Veuillez entrer votre nom';
                  }
                  return null;
                },
              ),
            ),
          ],
        ),

        const SizedBox(height: 16),

        // Email
        _buildTextField(
          controller: _emailController,
          label: 'Email',
          icon: Icons.email_outlined,
          keyboardType: TextInputType.emailAddress,
          validator: (value) {
            if (value == null || value.isEmpty) {
              return 'Veuillez entrer votre email';
            }
            if (!RegExp(r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$').hasMatch(value)) {
              return 'Veuillez entrer un email valide';
            }
            return null;
          },
        ),

        const SizedBox(height: 16),

        // Téléphone
        _buildTextField(
          controller: _phoneController,
          label: 'Téléphone',
          icon: Icons.phone_outlined,
          keyboardType: TextInputType.phone,
          validator: (value) {
            if (value == null || value.isEmpty) {
              return 'Veuillez entrer votre numéro de téléphone';
            }
            if (value.length < 10) {
              return 'Numéro de téléphone invalide';
            }
            return null;
          },
        ),

        const SizedBox(height: 16),

        // Mot de passe
        _buildTextField(
          controller: _passwordController,
          label: 'Mot de passe',
          icon: Icons.lock_outline,
          obscureText: _obscurePassword,
          suffixIcon: IconButton(
            icon: Icon(
              _obscurePassword ? Icons.visibility : Icons.visibility_off,
            ),
            onPressed: () {
              setState(() {
                _obscurePassword = !_obscurePassword;
              });
            },
          ),
          validator: (value) {
            if (value == null || value.isEmpty) {
              return 'Veuillez entrer un mot de passe';
            }
            if (value.length < 6) {
              return 'Le mot de passe doit contenir au moins 6 caractères';
            }
            return null;
          },
        ),

        const SizedBox(height: 16),

        // Confirmation mot de passe
        _buildTextField(
          controller: _confirmPasswordController,
          label: 'Confirmer le mot de passe',
          icon: Icons.lock_outline,
          obscureText: _obscureConfirmPassword,
          suffixIcon: IconButton(
            icon: Icon(
              _obscureConfirmPassword ? Icons.visibility : Icons.visibility_off,
            ),
            onPressed: () {
              setState(() {
                _obscureConfirmPassword = !_obscureConfirmPassword;
              });
            },
          ),
          validator: (value) {
            if (value == null || value.isEmpty) {
              return 'Veuillez confirmer votre mot de passe';
            }
            if (value != _passwordController.text) {
              return 'Les mots de passe ne correspondent pas';
            }
            return null;
          },
        ),

        const SizedBox(height: 20),

        // Acceptation des conditions
        Row(
          children: [
            Checkbox(
              value: _acceptTerms,
              onChanged: (value) {
                setState(() {
                  _acceptTerms = value ?? false;
                });
              },
              activeColor: const Color(0xFFE9D7C2),
            ),
            Expanded(
              child: GestureDetector(
                onTap: () {
                  setState(() {
                    _acceptTerms = !_acceptTerms;
                  });
                },
                child: const Text(
                  'J\'accepte les conditions d\'utilisation et la politique de confidentialité',
                  style: TextStyle(fontSize: 14, color: Colors.black87),
                ),
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String label,
    required IconData icon,
    TextInputType? keyboardType,
    bool obscureText = false,
    Widget? suffixIcon,
    String? Function(String?)? validator,
  }) {
    return TextFormField(
      controller: controller,
      keyboardType: keyboardType,
      obscureText: obscureText,
      validator: validator,
      decoration: InputDecoration(
        labelText: label,
        prefixIcon: Icon(icon, color: const Color(0xFFE9D7C2)),
        suffixIcon: suffixIcon,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey[300]!),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey[300]!),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: Color(0xFFE9D7C2), width: 2),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: Colors.red, width: 2),
        ),
        filled: true,
        fillColor: Colors.grey[50],
      ),
    );
  }

  Widget _buildCreateAccountButton() {
    return SizedBox(
      width: double.infinity,
      child: ElevatedButton(
        onPressed: _isLoading ? null : _createAccount,
        style: ElevatedButton.styleFrom(
          backgroundColor: const Color(0xFFE9D7C2),
          foregroundColor: Colors.black,
          padding: const EdgeInsets.symmetric(vertical: 16),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
        child: _isLoading
            ? const SizedBox(
                height: 20,
                width: 20,
                child: CircularProgressIndicator(
                  strokeWidth: 2,
                  valueColor: AlwaysStoppedAnimation<Color>(Colors.black),
                ),
              )
            : const Text(
                'Créer mon compte et confirmer la réservation',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
      ),
    );
  }

  Future<void> _createAccount() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    if (!_acceptTerms) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Veuillez accepter les conditions d\'utilisation'),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }

    setState(() {
      _isLoading = true;
    });

    // Simuler la création de compte
    await Future.delayed(const Duration(seconds: 2));

    // Sauvegarder la réservation
    try {
      final reservation = {
        'services': widget.selectedServices,
        'totalPrice': widget.totalPrice,
        'totalDuration': widget.totalDuration,
        'date': widget.selectedDate.toIso8601String(),
        'time': widget.selectedTime,
        'specialist': widget.selectedEmployee,
        'customerName':
            '${_firstNameController.text} ${_lastNameController.text}',
        'customerEmail': _emailController.text,
        'customerPhone': _phoneController.text,
        'status': 'Confirmé',
      };

      await ReservationService.addReservation(reservation);
    } catch (e) {
      print('Erreur lors de la sauvegarde de la réservation: $e');
    }

    setState(() {
      _isLoading = false;
    });

    // Naviguer vers la page de confirmation
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(
        builder: (context) => BookingConfirmationScreen(
          selectedServices: widget.selectedServices,
          totalPrice: widget.totalPrice,
          totalDuration: widget.totalDuration,
          selectedDate: widget.selectedDate,
          selectedTime: widget.selectedTime,
          selectedEmployee: widget.selectedEmployee,
          customerName:
              '${_firstNameController.text} ${_lastNameController.text}',
          customerEmail: _emailController.text,
          customerPhone: _phoneController.text,
        ),
      ),
    );
  }
}

// Page de confirmation de réservation
class BookingConfirmationScreen extends StatelessWidget {
  final List<Map<String, dynamic>> selectedServices;
  final int totalPrice;
  final int totalDuration;
  final DateTime selectedDate;
  final String selectedTime;
  final String selectedEmployee;
  final String customerName;
  final String customerEmail;
  final String customerPhone;

  const BookingConfirmationScreen({
    super.key,
    required this.selectedServices,
    required this.totalPrice,
    required this.totalDuration,
    required this.selectedDate,
    required this.selectedTime,
    required this.selectedEmployee,
    required this.customerName,
    required this.customerEmail,
    required this.customerPhone,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        backgroundColor: Colors.white,
        foregroundColor: Colors.black,
        elevation: 0,
        title: const Text(
          'Réservation confirmée',
          style: TextStyle(
            color: Colors.black,
            fontSize: 18,
            fontWeight: FontWeight.bold,
          ),
        ),
        centerTitle: true,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            // Icône de succès
            Container(
              width: 100,
              height: 100,
              decoration: const BoxDecoration(
                color: Color(0xFFE9D7C2),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.check, color: Colors.black, size: 50),
            ),

            const SizedBox(height: 30),

            // Message de confirmation
            const Text(
              'Réservation confirmée !',
              style: TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
                color: Colors.black,
              ),
            ),

            const SizedBox(height: 12),

            Text(
              'Votre réservation a été créée avec succès. Vous recevrez un email de confirmation.',
              style: TextStyle(fontSize: 16, color: Colors.grey[600]),
              textAlign: TextAlign.center,
            ),

            const SizedBox(height: 30),

            // Détails de la réservation
            _buildReservationDetails(),

            const SizedBox(height: 30),

            // Bouton retour à l'accueil
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () {
                  Navigator.pushNamedAndRemoveUntil(
                    context,
                    '/',
                    (route) => false,
                  );
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFFE9D7C2),
                  foregroundColor: Colors.black,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                child: const Text(
                  'Retour à l\'accueil',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildReservationDetails() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.grey[50],
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey[200]!),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Détails de votre réservation',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: Colors.black,
            ),
          ),
          const SizedBox(height: 16),

          _buildDetailRow('Client', customerName),
          _buildDetailRow('Email', customerEmail),
          _buildDetailRow('Téléphone', customerPhone),
          _buildDetailRow(
            'Date',
            '${selectedDate.day}/${selectedDate.month}/${selectedDate.year}',
          ),
          _buildDetailRow('Heure', selectedTime),
          _buildDetailRow('Employé', selectedEmployee),
          _buildDetailRow('Durée', '${totalDuration} min'),
          _buildDetailRow('Total', '${totalPrice} DH'),

          const SizedBox(height: 16),
          const Divider(),
          const SizedBox(height: 16),

          const Text(
            'Services',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: Colors.black,
            ),
          ),
          const SizedBox(height: 8),

          ...selectedServices
              .map(
                (service) => Padding(
                  padding: const EdgeInsets.only(bottom: 4),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(
                        child: Text(
                          service['name'],
                          style: const TextStyle(
                            fontSize: 14,
                            color: Colors.black,
                          ),
                        ),
                      ),
                      Text(
                        '${service['price']} DH',
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                          color: Color(0xFFE9D7C2),
                        ),
                      ),
                    ],
                  ),
                ),
              )
              .toList(),
        ],
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 80,
            child: Text(
              '$label:',
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w500,
                color: Colors.grey[600],
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(fontSize: 14, color: Colors.black),
            ),
          ),
        ],
      ),
    );
  }
}
