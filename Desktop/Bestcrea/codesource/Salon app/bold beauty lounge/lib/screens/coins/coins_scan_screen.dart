import 'package:flutter/material.dart';
import 'package:firebase_auth/firebase_auth.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import '../../services/coins_service.dart';

/// Screen to scan QR code and add contact
class CoinsScanScreen extends StatefulWidget {
  const CoinsScanScreen({super.key});

  @override
  State<CoinsScanScreen> createState() => _CoinsScanScreenState();
}

class _CoinsScanScreenState extends State<CoinsScanScreen> {
  final CoinsService _coinsService = CoinsService();
  final TextEditingController _qrController = TextEditingController();
  bool _isProcessing = false;

  @override
  void dispose() {
    _qrController.dispose();
    super.dispose();
  }

  Future<void> _processQrCode(String qrValue) async {
    final userId = FirebaseAuth.instance.currentUser?.uid;
    if (userId == null) {
      _showError('User not authenticated');
      return;
    }

    // Parse QR code
    final contactUid = CoinsService.parseQrCode(qrValue);
    if (contactUid == null) {
      _showError('Invalid QR code format');
      return;
    }

    if (contactUid == userId) {
      _showError('Cannot add yourself as contact');
      return;
    }

    setState(() => _isProcessing = true);

    try {
      // Get contact user info
      final contactDoc = await FirebaseFirestore.instance
          .collection('users')
          .doc(contactUid)
          .get();

      if (!contactDoc.exists) {
        _showError('User not found');
        return;
      }

      final contactData = contactDoc.data();
      final displayName = contactData?['userName'] ??
          contactData?['displayName'] ??
          contactData?['email'] ??
          'User';
      final email = contactData?['email'];

      // Add to contacts
      await _coinsService.addContact(
        userId,
        contactUid,
        displayName,
        email: email,
      );

      if (mounted) {
        Navigator.pop(context, {
          'uid': contactUid,
          'displayName': displayName,
        });
      }
    } catch (e) {
      if (mounted) {
        _showError('Error: $e');
      }
    } finally {
      if (mounted) {
        setState(() => _isProcessing = false);
      }
    }
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.red,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Scan QR Code'),
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const SizedBox(height: 40),
            const Icon(
              Icons.qr_code_scanner,
              size: 100,
              color: Colors.black,
            ),
            const SizedBox(height: 24),
            const Text(
              'Scan QR Code',
              style: TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              'Scan a QR code to add a contact and transfer coins',
              style: TextStyle(
                fontSize: 14,
                color: Colors.grey[600],
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 32),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Or Enter QR Code Manually',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 16),
                    TextField(
                      controller: _qrController,
                      decoration: InputDecoration(
                        labelText: 'QR Code Value',
                        hintText: 'BOLDCOINS:...',
                        prefixIcon: const Icon(Icons.qr_code),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    SizedBox(
                      width: double.infinity,
                      height: 50,
                      child: ElevatedButton(
                        onPressed: _isProcessing
                            ? null
                            : () {
                                final qrValue = _qrController.text.trim();
                                if (qrValue.isEmpty) {
                                  _showError('Please enter QR code value');
                                  return;
                                }
                                _processQrCode(qrValue);
                              },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.black,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                        child: _isProcessing
                            ? const CircularProgressIndicator(color: Colors.white)
                            : const Text(
                                'Add Contact',
                                style: TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),
            Card(
              color: Colors.blue.shade50,
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    Icon(Icons.info_outline, color: Colors.blue.shade700),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        'QR codes start with "BOLDCOINS:" followed by the user ID',
                        style: TextStyle(
                          fontSize: 14,
                          color: Colors.blue.shade900,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

