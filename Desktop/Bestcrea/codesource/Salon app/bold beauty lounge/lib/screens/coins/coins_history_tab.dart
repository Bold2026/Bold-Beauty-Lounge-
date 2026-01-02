import 'package:flutter/material.dart';
import 'package:firebase_auth/firebase_auth.dart';
import 'package:intl/intl.dart';
import '../../services/coins_service.dart';
import '../../models/coin_transaction_model.dart';

/// History tab: Show transaction history with filters
class CoinsHistoryTab extends StatefulWidget {
  final CoinsService coinsService;

  const CoinsHistoryTab({
    super.key,
    required this.coinsService,
  });

  @override
  State<CoinsHistoryTab> createState() => _CoinsHistoryTabState();
}

class _CoinsHistoryTabState extends State<CoinsHistoryTab> {
  String? _selectedType;
  String? _selectedDirection;
  DateTime? _startDate;
  DateTime? _endDate;
  final int _pageSize = 20;

  @override
  Widget build(BuildContext context) {
    final userId = FirebaseAuth.instance.currentUser?.uid;
    if (userId == null) {
      return const Center(child: Text('Please sign in'));
    }

    return Column(
      children: [
        // Filters
        Container(
          padding: const EdgeInsets.all(16),
          color: Colors.grey[100],
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Filters',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 12),
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  // Type Filter
                  FilterChip(
                    label: const Text('Type'),
                    selected: _selectedType != null,
                    onSelected: (selected) {
                      if (selected) {
                        _showTypeFilter();
                      } else {
                        setState(() => _selectedType = null);
                      }
                    },
                    selectedColor: Colors.black,
                    labelStyle: TextStyle(
                      color: _selectedType != null ? Colors.white : Colors.black,
                    ),
                  ),
                  // Direction Filter
                  FilterChip(
                    label: const Text('Direction'),
                    selected: _selectedDirection != null,
                    onSelected: (selected) {
                      if (selected) {
                        _showDirectionFilter();
                      } else {
                        setState(() => _selectedDirection = null);
                      }
                    },
                    selectedColor: Colors.black,
                    labelStyle: TextStyle(
                      color: _selectedDirection != null ? Colors.white : Colors.black,
                    ),
                  ),
                  // Date Range Filter
                  FilterChip(
                    label: const Text('Date Range'),
                    selected: _startDate != null || _endDate != null,
                    onSelected: (selected) {
                      if (selected) {
                        _showDateRangeFilter();
                      } else {
                        setState(() {
                          _startDate = null;
                          _endDate = null;
                        });
                      }
                    },
                    selectedColor: Colors.black,
                    labelStyle: TextStyle(
                      color: (_startDate != null || _endDate != null)
                          ? Colors.white
                          : Colors.black,
                    ),
                  ),
                  // Clear Filters
                  if (_selectedType != null ||
                      _selectedDirection != null ||
                      _startDate != null ||
                      _endDate != null)
                    ActionChip(
                      label: const Text('Clear All'),
                      onPressed: () {
                        setState(() {
                          _selectedType = null;
                          _selectedDirection = null;
                          _startDate = null;
                          _endDate = null;
                        });
                      },
                      backgroundColor: Colors.red.shade100,
                    ),
                ],
              ),
            ],
          ),
        ),
        // History List
        Expanded(
          child: StreamBuilder<List<CoinTransaction>>(
            stream: widget.coinsService.getHistory(
              userId,
              type: _selectedType,
              direction: _selectedDirection,
              startDate: _startDate,
              endDate: _endDate,
              limit: _pageSize,
            ),
            builder: (context, snapshot) {
              if (snapshot.connectionState == ConnectionState.waiting) {
                return const Center(child: CircularProgressIndicator());
              }
              if (snapshot.hasError) {
                return Center(child: Text('Error: ${snapshot.error}'));
              }
              final transactions = snapshot.data ?? [];
              if (transactions.isEmpty) {
                return const Center(
                  child: Text('No transactions yet'),
                );
              }
              return ListView.builder(
                itemCount: transactions.length,
                itemBuilder: (context, index) {
                  final tx = transactions[index];
                  return _buildTransactionCard(tx);
                },
              );
            },
          ),
        ),
      ],
    );
  }

  Widget _buildTransactionCard(CoinTransaction tx) {
    final isIncoming = tx.direction == 'in';
    final icon = _getTransactionIcon(tx.type);
    final color = isIncoming ? Colors.green : Colors.red;

    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: color.withOpacity(0.2),
          child: Icon(icon, color: color),
        ),
        title: Text(
          _getTransactionTitle(tx),
          style: const TextStyle(fontWeight: FontWeight.bold),
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              DateFormat('MMM dd, yyyy • HH:mm').format(tx.createdAt),
              style: TextStyle(fontSize: 12, color: Colors.grey[600]),
            ),
            if (tx.reason != null)
              Text(
                tx.reason!,
                style: TextStyle(fontSize: 12, color: Colors.grey[600]),
              ),
          ],
        ),
        trailing: Text(
          '${isIncoming ? '+' : '-'}${tx.amount}',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: color,
          ),
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

  void _showTypeFilter() {
    showModalBottomSheet(
      context: context,
      builder: (context) => Container(
        padding: const EdgeInsets.all(16),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text(
              'Select Type',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            _buildTypeOption('earn', 'Earn'),
            _buildTypeOption('pay', 'Pay'),
            _buildTypeOption('transfer_in', 'Transfer In'),
            _buildTypeOption('transfer_out', 'Transfer Out'),
            _buildTypeOption('admin_adjustment', 'Admin Adjustment'),
          ],
        ),
      ),
    );
  }

  Widget _buildTypeOption(String value, String label) {
    return ListTile(
      title: Text(label),
      trailing: _selectedType == value
          ? const Icon(Icons.check, color: Colors.black)
          : null,
      onTap: () {
        setState(() => _selectedType = value);
        Navigator.pop(context);
      },
    );
  }

  void _showDirectionFilter() {
    showModalBottomSheet(
      context: context,
      builder: (context) => Container(
        padding: const EdgeInsets.all(16),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text(
              'Select Direction',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            _buildDirectionOption('in', 'Incoming'),
            _buildDirectionOption('out', 'Outgoing'),
          ],
        ),
      ),
    );
  }

  Widget _buildDirectionOption(String value, String label) {
    return ListTile(
      title: Text(label),
      trailing: _selectedDirection == value
          ? const Icon(Icons.check, color: Colors.black)
          : null,
      onTap: () {
        setState(() => _selectedDirection = value);
        Navigator.pop(context);
      },
    );
  }

  Future<void> _showDateRangeFilter() async {
    final DateTimeRange? picked = await showDateRangePicker(
      context: context,
      firstDate: DateTime(2020),
      lastDate: DateTime.now(),
      initialDateRange: _startDate != null && _endDate != null
          ? DateTimeRange(start: _startDate!, end: _endDate!)
          : null,
    );
    if (picked != null) {
      setState(() {
        _startDate = picked.start;
        _endDate = picked.end;
      });
    }
  }
}

