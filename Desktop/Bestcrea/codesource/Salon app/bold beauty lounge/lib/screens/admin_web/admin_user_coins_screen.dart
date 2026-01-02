import 'package:flutter/material.dart';
import 'package:firebase_auth/firebase_auth.dart';
import 'package:intl/intl.dart';
import '../../services/coins_service.dart';
import '../../models/coin_transaction_model.dart';
import '../../models/contact_model.dart';

/// Admin screen to view and manage user's Bold Coins
class AdminUserCoinsScreen extends StatefulWidget {
  final String userId;
  final String userName;
  final String userEmail;

  const AdminUserCoinsScreen({
    super.key,
    required this.userId,
    required this.userName,
    required this.userEmail,
  });

  @override
  State<AdminUserCoinsScreen> createState() => _AdminUserCoinsScreenState();
}

class _AdminUserCoinsScreenState extends State<AdminUserCoinsScreen> {
  final CoinsService _coinsService = CoinsService();
  final TextEditingController _adjustAmountController = TextEditingController();
  final TextEditingController _adjustReasonController = TextEditingController();
  bool _isAdjusting = false;
  String? _selectedType;
  String? _selectedDirection;

  @override
  void dispose() {
    _adjustAmountController.dispose();
    _adjustReasonController.dispose();
    super.dispose();
  }

  Future<void> _adjustBalance(bool isCredit) async {
    final amountText = _adjustAmountController.text.trim();
    if (amountText.isEmpty) {
      _showError('Please enter an amount');
      return;
    }

    final amount = int.tryParse(amountText);
    if (amount == null || amount <= 0) {
      _showError('Please enter a valid positive amount');
      return;
    }

    final reason = _adjustReasonController.text.trim();
    if (reason.isEmpty) {
      _showError('Please enter a reason');
      return;
    }

    final adminId = FirebaseAuth.instance.currentUser?.uid;
    if (adminId == null) {
      _showError('Admin not authenticated');
      return;
    }

    setState(() => _isAdjusting = true);

    try {
      final adjustmentAmount = isCredit ? amount : -amount;
      await _coinsService.adminAdjustBalance(
        widget.userId,
        adjustmentAmount,
        reason,
        adminId,
      );

      if (mounted) {
        _adjustAmountController.clear();
        _adjustReasonController.clear();
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'Successfully ${isCredit ? 'credited' : 'debited'} $amount coins',
            ),
            backgroundColor: Colors.green,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        _showError('Error: $e');
      }
    } finally {
      if (mounted) {
        setState(() => _isAdjusting = false);
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
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text('${widget.userName} - Bold Coins'),
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // User Info Card
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    CircleAvatar(
                      radius: 30,
                      child: Text(widget.userName[0].toUpperCase()),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            widget.userName,
                            style: const TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          Text(
                            widget.userEmail,
                            style: TextStyle(
                              fontSize: 14,
                              color: Colors.grey[600],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),

            // Balance Card
            StreamBuilder<int>(
              stream: _coinsService.streamBalance(widget.userId),
              builder: (context, snapshot) {
                final balance = snapshot.data ?? 0;
                return Card(
                  color: Colors.black,
                  child: Padding(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      children: [
                        const Text(
                          'Current Balance',
                          style: TextStyle(
                            color: Colors.white70,
                            fontSize: 16,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          '$balance',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 48,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 4),
                        const Text(
                          'Bold Coins',
                          style: TextStyle(
                            color: Colors.white70,
                            fontSize: 16,
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
            const SizedBox(height: 24),

            // Admin Adjustment
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Adjust Balance',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 16),
                    TextField(
                      controller: _adjustAmountController,
                      keyboardType: TextInputType.number,
                      decoration: InputDecoration(
                        labelText: 'Amount',
                        hintText: 'Enter amount',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    TextField(
                      controller: _adjustReasonController,
                      decoration: InputDecoration(
                        labelText: 'Reason',
                        hintText: 'Enter reason for adjustment',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                      maxLines: 2,
                    ),
                    const SizedBox(height: 16),
                    Row(
                      children: [
                        Expanded(
                          child: ElevatedButton.icon(
                            onPressed: _isAdjusting
                                ? null
                                : () => _adjustBalance(true),
                            icon: const Icon(Icons.add),
                            label: const Text('Credit'),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: Colors.green,
                              foregroundColor: Colors.white,
                              padding: const EdgeInsets.symmetric(vertical: 12),
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: ElevatedButton.icon(
                            onPressed: _isAdjusting
                                ? null
                                : () => _adjustBalance(false),
                            icon: const Icon(Icons.remove),
                            label: const Text('Debit'),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: Colors.red,
                              foregroundColor: Colors.white,
                              padding: const EdgeInsets.symmetric(vertical: 12),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),

            // Contacts
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Contacts',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 16),
                    StreamBuilder<List<Contact>>(
                      stream: _coinsService.getContacts(widget.userId),
                      builder: (context, snapshot) {
                        if (snapshot.connectionState == ConnectionState.waiting) {
                          return const Center(child: CircularProgressIndicator());
                        }
                        final contacts = snapshot.data ?? [];
                        if (contacts.isEmpty) {
                          return const Text('No contacts');
                        }
                        return ListView.builder(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          itemCount: contacts.length,
                          itemBuilder: (context, index) {
                            final contact = contacts[index];
                            return ListTile(
                              leading: CircleAvatar(
                                child: Text(contact.displayName[0].toUpperCase()),
                              ),
                              title: Text(contact.displayName),
                              subtitle: contact.email != null
                                  ? Text(contact.email!)
                                  : null,
                            );
                          },
                        );
                      },
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),

            // Transaction History
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'Transaction History',
                          style: TextStyle(
                            fontSize: 20,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        // Filters
                        Wrap(
                          spacing: 8,
                          children: [
                            FilterChip(
                              label: const Text('Type'),
                              selected: _selectedType != null,
                              onSelected: (selected) {
                                // TODO: Show type filter dialog
                              },
                            ),
                            FilterChip(
                              label: const Text('Direction'),
                              selected: _selectedDirection != null,
                              onSelected: (selected) {
                                // TODO: Show direction filter dialog
                              },
                            ),
                          ],
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    StreamBuilder<List<CoinTransaction>>(
                      stream: _coinsService.getHistory(
                        widget.userId,
                        type: _selectedType,
                        direction: _selectedDirection,
                        limit: 50,
                      ),
                      builder: (context, snapshot) {
                        if (snapshot.connectionState == ConnectionState.waiting) {
                          return const Center(child: CircularProgressIndicator());
                        }
                        final transactions = snapshot.data ?? [];
                        if (transactions.isEmpty) {
                          return const Text('No transactions');
                        }
                        return ListView.builder(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          itemCount: transactions.length,
                          itemBuilder: (context, index) {
                            final tx = transactions[index];
                            return _buildTransactionRow(tx);
                          },
                        );
                      },
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

  Widget _buildTransactionRow(CoinTransaction tx) {
    final isIncoming = tx.direction == 'in';
    final color = isIncoming ? Colors.green : Colors.red;

    return ListTile(
      leading: CircleAvatar(
        backgroundColor: color.withOpacity(0.2),
        child: Icon(
          _getTransactionIcon(tx.type),
          color: color,
          size: 20,
        ),
      ),
      title: Text(_getTransactionTitle(tx)),
      subtitle: Text(
        DateFormat('MMM dd, yyyy • HH:mm').format(tx.createdAt),
        style: TextStyle(fontSize: 12, color: Colors.grey[600]),
      ),
      trailing: Text(
        '${isIncoming ? '+' : '-'}${tx.amount}',
        style: TextStyle(
          fontSize: 16,
          fontWeight: FontWeight.bold,
          color: color,
        ),
      ),
    );
  }

  IconData _getTransactionIcon(String type) {
    switch (type) {
      case 'earn':
        return Icons.stars;
      case 'pay':
        return Icons.payment;
      case 'transfer_in':
        return Icons.arrow_downward;
      case 'transfer_out':
        return Icons.arrow_upward;
      case 'admin_adjustment':
        return Icons.admin_panel_settings;
      default:
        return Icons.info;
    }
  }

  String _getTransactionTitle(CoinTransaction tx) {
    switch (tx.type) {
      case 'earn':
        return 'Earned Coins';
      case 'pay':
        return 'Redeemed Coins';
      case 'transfer_in':
        return 'Received Coins';
      case 'transfer_out':
        return 'Sent Coins';
      case 'admin_adjustment':
        return 'Admin Adjustment';
      default:
        return 'Transaction';
    }
  }
}

