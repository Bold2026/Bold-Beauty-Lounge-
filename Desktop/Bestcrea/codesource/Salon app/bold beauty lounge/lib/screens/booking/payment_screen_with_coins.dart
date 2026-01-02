import 'package:flutter/material.dart';
import 'package:firebase_auth/firebase_auth.dart';
import 'package:intl/intl.dart';
import '../../models/booking_data.dart';
import '../../services/coins_service.dart';
import 'confirmation_screen.dart';

/// Payment screen with Bold Coins redemption support
class PaymentScreenWithCoins extends StatefulWidget {
  final BookingData bookingData;

  const PaymentScreenWithCoins({
    super.key,
    required this.bookingData,
  });

  @override
  State<PaymentScreenWithCoins> createState() => _PaymentScreenWithCoinsState();
}

class _PaymentScreenWithCoinsState extends State<PaymentScreenWithCoins> {
  String _selectedPaymentMethod = 'cash';
  bool _isProcessing = false;
  int _coinsToRedeem = 0;
  int _currentBalance = 0;
  bool _isLoadingBalance = true;
  final CoinsService _coinsService = CoinsService();
  final TextEditingController _coinsController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadBalance();
  }

  @override
  void dispose() {
    _coinsController.dispose();
    super.dispose();
  }

  Future<void> _loadBalance() async {
    final user = FirebaseAuth.instance.currentUser;
    if (user == null) {
      setState(() => _isLoadingBalance = false);
      return;
    }

    _coinsService.streamBalance(user.uid).listen((balance) {
      if (mounted) {
        setState(() {
          _currentBalance = balance;
          _isLoadingBalance = false;
        });
      }
    });
  }

  void _updateCoinsToRedeem(String value) {
    final amount = int.tryParse(value) ?? 0;
    final maxRedeemable = _currentBalance;
    final maxByPrice = widget.bookingData.servicePrice != null
        ? widget.bookingData.servicePrice!.floor()
        : maxRedeemable;

    final coinsToRedeem = amount.clamp(0, maxRedeemable < maxByPrice ? maxRedeemable : maxByPrice);
    setState(() => _coinsToRedeem = coinsToRedeem);
    _coinsController.text = coinsToRedeem.toString();
  }

  double? get _finalPrice {
    if (widget.bookingData.servicePrice == null) return null;
    final discount = _coinsToRedeem.toDouble();
    final finalPrice = widget.bookingData.servicePrice! - discount;
    return finalPrice < 0 ? 0 : finalPrice;
  }

  @override
  Widget build(BuildContext context) {
    final dateFormat = DateFormat('EEEE d MMMM yyyy', 'fr_FR');

    return Scaffold(
      appBar: AppBar(
        title: const Text('Payment'),
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Summary
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: Colors.grey[100],
                borderRadius: BorderRadius.circular(12),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Booking Summary',
                    style: TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 16),
                  _buildSummaryRow('Service', widget.bookingData.serviceName),
                  if (widget.bookingData.servicePrice != null)
                    _buildSummaryRow(
                      'Price',
                      '${widget.bookingData.servicePrice!.toStringAsFixed(2)} MAD',
                    ),
                  _buildSummaryRow(
                    'Date',
                    dateFormat.format(widget.bookingData.selectedDate),
                  ),
                  _buildSummaryRow(
                    'Time',
                    widget.bookingData.selectedTime,
                  ),
                  if (widget.bookingData.employeeName != null)
                    _buildSummaryRow(
                      'Employee',
                      widget.bookingData.employeeName!,
                    ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            // Bold Coins Redemption
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        const Icon(Icons.coins, color: Colors.amber),
                        const SizedBox(width: 8),
                        const Text(
                          'Redeem Bold Coins',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    if (_isLoadingBalance)
                      const LinearProgressIndicator()
                    else
                      Text(
                        'Available: $_currentBalance coins',
                        style: TextStyle(
                          fontSize: 14,
                          color: Colors.grey[600],
                        ),
                      ),
                    const SizedBox(height: 16),
                    TextField(
                      controller: _coinsController,
                      keyboardType: TextInputType.number,
                      decoration: InputDecoration(
                        labelText: 'Coins to Redeem',
                        hintText: 'Enter amount',
                        prefixIcon: const Icon(Icons.coins),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        suffixText: 'coins',
                      ),
                      onChanged: _updateCoinsToRedeem,
                    ),
                    if (_coinsToRedeem > 0) ...[
                      const SizedBox(height: 12),
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: Colors.green.shade50,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Row(
                          children: [
                            Icon(Icons.discount, color: Colors.green.shade700),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                'Discount: ${_coinsToRedeem} MAD',
                                style: TextStyle(
                                  color: Colors.green.shade900,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),

            // Final Price
            if (_finalPrice != null)
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.black,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'Total to Pay',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                    Text(
                      '${_finalPrice!.toStringAsFixed(2)} MAD',
                      style: const TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                  ],
                ),
              ),
            const SizedBox(height: 24),

            // Payment Method
            const Text(
              'Payment Method',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),
            _buildPaymentMethodOption(
              'cash',
              'Cash',
              Icons.money,
              'Pay on site',
            ),
            _buildPaymentMethodOption(
              'card',
              'Card',
              Icons.credit_card,
              'Card payment',
            ),
            _buildPaymentMethodOption(
              'mobile',
              'Mobile Money',
              Icons.phone_android,
              'Mobile payment',
            ),
            const SizedBox(height: 32),

            // Confirm Button
            SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton(
                onPressed: _isProcessing ? null : _processPayment,
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
                        'Confirm & Pay',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSummaryRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: const TextStyle(
              fontSize: 16,
              color: Colors.grey,
            ),
          ),
          Text(
            value,
            style: const TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPaymentMethodOption(
    String value,
    String title,
    IconData icon,
    String subtitle,
  ) {
    final isSelected = _selectedPaymentMethod == value;
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        border: Border.all(
          color: isSelected ? Colors.black : Colors.grey[300]!,
          width: isSelected ? 2 : 1,
        ),
        borderRadius: BorderRadius.circular(12),
        color: isSelected ? Colors.black.withOpacity(0.05) : Colors.white,
      ),
      child: RadioListTile<String>(
        value: value,
        groupValue: _selectedPaymentMethod,
        onChanged: (newValue) {
          setState(() {
            _selectedPaymentMethod = newValue!;
          });
        },
        title: Row(
          children: [
            Icon(icon, color: isSelected ? Colors.black : Colors.grey),
            const SizedBox(width: 12),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: TextStyle(
                    fontWeight: FontWeight.bold,
                    color: isSelected ? Colors.black : Colors.grey[800],
                  ),
                ),
                Text(
                  subtitle,
                  style: TextStyle(
                    fontSize: 12,
                    color: Colors.grey[600],
                  ),
                ),
              ],
            ),
          ],
        ),
        activeColor: Colors.black,
      ),
    );
  }

  Future<void> _processPayment() async {
    setState(() => _isProcessing = true);

    try {
      final user = FirebaseAuth.instance.currentUser;
      if (user == null) {
        throw Exception('User not authenticated');
      }

      // Redeem coins if any
      if (_coinsToRedeem > 0) {
        await _coinsService.redeemCoins(
          user.uid,
          _coinsToRedeem,
          'Redeemed for booking discount',
        );
      }

      // Create booking data with coins redeemed
      final bookingDataWithCoins = BookingData(
        serviceId: widget.bookingData.serviceId,
        serviceName: widget.bookingData.serviceName,
        serviceImage: widget.bookingData.serviceImage,
        servicePrice: widget.bookingData.servicePrice,
        selectedDate: widget.bookingData.selectedDate,
        selectedTime: widget.bookingData.selectedTime,
        employeeId: widget.bookingData.employeeId,
        employeeName: widget.bookingData.employeeName,
        notes: widget.bookingData.notes,
        coinsRedeemed: _coinsToRedeem,
      );

      if (!mounted) return;

      // Navigate to confirmation
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(
          builder: (context) => ConfirmationScreen(
            bookingData: bookingDataWithCoins,
            paymentMethod: _selectedPaymentMethod,
          ),
        ),
      );
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Error: $e'),
            backgroundColor: Colors.red,
          ),
        );
        setState(() => _isProcessing = false);
      }
    }
  }
}

