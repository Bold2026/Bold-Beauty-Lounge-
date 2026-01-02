import 'package:flutter/material.dart';
import 'package:firebase_auth/firebase_auth.dart';
import '../../services/coins_service.dart';
import 'coins_pay_tab.dart';
import 'coins_earn_tab.dart';
import 'coins_transfer_tab.dart';
import 'coins_history_tab.dart';

/// Main Bold Coins screen with 4 tabs: Pay, Earn, Transfer, History
class BoldCoinsScreen extends StatefulWidget {
  const BoldCoinsScreen({super.key});

  @override
  State<BoldCoinsScreen> createState() => _BoldCoinsScreenState();
}

class _BoldCoinsScreenState extends State<BoldCoinsScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final CoinsService _coinsService = CoinsService();
  final FirebaseAuth _auth = FirebaseAuth.instance;

  int _currentBalance = 0;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    _initializeWallet();
  }

  Future<void> _initializeWallet() async {
    final user = _auth.currentUser;
    if (user == null) {
      setState(() => _isLoading = false);
      return;
    }

    try {
      await _coinsService.ensureWalletInitialized(user.uid);
      _coinsService.streamBalance(user.uid).listen((balance) {
        if (mounted) {
          setState(() {
            _currentBalance = balance;
            _isLoading = false;
          });
        }
      });
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final user = _auth.currentUser;
    if (user == null) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('Bold Coins'),
          backgroundColor: Colors.black,
          foregroundColor: Colors.white,
        ),
        body: const Center(
          child: Text('Please sign in to access Bold Coins'),
        ),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Bold Coins'),
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.qr_code),
            onPressed: () {
              Navigator.pushNamed(context, '/coins/qr');
            },
            tooltip: 'Show QR Code',
          ),
        ],
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: Colors.white,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          tabs: const [
            Tab(icon: Icon(Icons.payment), text: 'Pay'),
            Tab(icon: Icon(Icons.stars), text: 'Earn'),
            Tab(icon: Icon(Icons.swap_horiz), text: 'Transfer'),
            Tab(icon: Icon(Icons.history), text: 'History'),
          ],
        ),
      ),
      body: Column(
        children: [
          // Balance Card
          Container(
            width: double.infinity,
            margin: const EdgeInsets.all(16),
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF1A1A1A), Color(0xFF2D2D2D)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(16),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.3),
                  blurRadius: 10,
                  offset: const Offset(0, 5),
                ),
              ],
            ),
            child: Column(
              children: [
                const Text(
                  'Your Balance',
                  style: TextStyle(
                    color: Colors.white70,
                    fontSize: 14,
                    fontWeight: FontWeight.w500,
                  ),
                ),
                const SizedBox(height: 8),
                if (_isLoading)
                  const CircularProgressIndicator(color: Colors.white)
                else
                  Text(
                    '$_currentBalance',
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
                const SizedBox(height: 12),
                const Text(
                  '1 Coin = 1 MAD Discount',
                  style: TextStyle(
                    color: Colors.white60,
                    fontSize: 12,
                  ),
                ),
              ],
            ),
          ),
          // Tab Views
          Expanded(
            child: TabBarView(
              controller: _tabController,
              children: [
                CoinsPayTab(
                  coinsService: _coinsService,
                  currentBalance: _currentBalance,
                  onBalanceUpdated: () => _initializeWallet(),
                ),
                CoinsEarnTab(coinsService: _coinsService),
                CoinsTransferTab(
                  coinsService: _coinsService,
                  currentBalance: _currentBalance,
                  onBalanceUpdated: () => _initializeWallet(),
                ),
                CoinsHistoryTab(coinsService: _coinsService),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

