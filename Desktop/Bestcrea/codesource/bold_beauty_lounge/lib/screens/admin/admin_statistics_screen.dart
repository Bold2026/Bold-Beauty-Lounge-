import 'package:flutter/material.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:intl/intl.dart';

class AdminStatisticsScreen extends StatefulWidget {
  const AdminStatisticsScreen({super.key});

  @override
  State<AdminStatisticsScreen> createState() => _AdminStatisticsScreenState();
}

class _AdminStatisticsScreenState extends State<AdminStatisticsScreen> {
  final FirebaseFirestore _firestore = FirebaseFirestore.instance;
  DateTime _selectedPeriod = DateTime.now();
  bool _isLoading = true;

  Map<String, dynamic> _stats = {};

  @override
  void initState() {
    super.initState();
    _loadStatistics();
  }

  Future<void> _loadStatistics() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final startOfMonth = DateTime(
        _selectedPeriod.year,
        _selectedPeriod.month,
        1,
      );
      final endOfMonth = DateTime(
        _selectedPeriod.year,
        _selectedPeriod.month + 1,
        0,
        23,
        59,
        59,
      );

      final bookingsSnapshot = await _firestore
          .collection('bookings')
          .where(
            'selectedDate',
            isGreaterThanOrEqualTo: Timestamp.fromDate(startOfMonth),
          )
          .where(
            'selectedDate',
            isLessThanOrEqualTo: Timestamp.fromDate(endOfMonth),
          )
          .get();

      int total = 0;
      int pending = 0;
      int confirmed = 0;
      int completed = 0;
      int cancelled = 0;
      double revenue = 0.0;
      Map<String, int> serviceCount = {};
      Map<String, double> serviceRevenue = {};

      for (var doc in bookingsSnapshot.docs) {
        final data = doc.data();
        final status = data['status'] as String? ?? '';
        final price = (data['servicePrice'] as num?)?.toDouble() ?? 0.0;
        final serviceName = data['serviceName'] as String? ?? 'Autre';

        total++;
        if (status == 'pending') pending++;
        if (status == 'confirmed') confirmed++;
        if (status == 'completed') {
          completed++;
          revenue += price;
        }
        if (status == 'cancelled') cancelled++;

        serviceCount[serviceName] = (serviceCount[serviceName] ?? 0) + 1;
        if (status == 'completed' || status == 'confirmed') {
          serviceRevenue[serviceName] =
              (serviceRevenue[serviceName] ?? 0.0) + price;
        }
      }

      setState(() {
        _stats = {
          'total': total,
          'pending': pending,
          'confirmed': confirmed,
          'completed': completed,
          'cancelled': cancelled,
          'revenue': revenue,
          'serviceCount': serviceCount,
          'serviceRevenue': serviceRevenue,
        };
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F5F5),
      appBar: AppBar(
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
        elevation: 0,
        title: const Text(
          'Statistiques détaillées',
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Sélecteur de période
                  _buildPeriodSelector(),

                  const SizedBox(height: 24),

                  // Résumé
                  _buildSummaryCards(),

                  const SizedBox(height: 24),

                  // Services les plus demandés
                  _buildTopServices(),

                  const SizedBox(height: 24),

                  // Revenus par service
                  _buildServiceRevenue(),
                ],
              ),
            ),
    );
  }

  Widget _buildPeriodSelector() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          const Text(
            'Période',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
          ),
          TextButton.icon(
            onPressed: () async {
              final DateTime? picked = await showDatePicker(
                context: context,
                initialDate: _selectedPeriod,
                firstDate: DateTime(2020),
                lastDate: DateTime.now(),
                locale: const Locale('fr', 'FR'),
              );
              if (picked != null) {
                setState(() {
                  _selectedPeriod = picked;
                });
                _loadStatistics();
              }
            },
            icon: const Icon(Icons.calendar_today),
            label: Text(
              DateFormat('MMMM yyyy', 'fr_FR').format(_selectedPeriod),
              style: const TextStyle(fontSize: 16),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSummaryCards() {
    return GridView.count(
      crossAxisCount: 2,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      crossAxisSpacing: 16,
      mainAxisSpacing: 16,
      childAspectRatio: 1.3,
      children: [
        _buildStatCard(
          'Total',
          _stats['total']?.toString() ?? '0',
          Icons.calendar_today,
          Colors.blue,
        ),
        _buildStatCard(
          'Terminées',
          _stats['completed']?.toString() ?? '0',
          Icons.check_circle,
          Colors.green,
        ),
        _buildStatCard(
          'En attente',
          _stats['pending']?.toString() ?? '0',
          Icons.pending,
          Colors.orange,
        ),
        _buildStatCard(
          'Revenus',
          '${(_stats['revenue'] as double? ?? 0.0).toStringAsFixed(0)} DH',
          Icons.attach_money,
          const Color(0xFFE9D7C2),
        ),
      ],
    );
  }

  Widget _buildStatCard(
    String title,
    String value,
    IconData icon,
    Color color,
  ) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Icon(icon, color: color, size: 28),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                value,
                style: TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                  color: color,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                title,
                style: TextStyle(fontSize: 12, color: Colors.grey[600]),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildTopServices() {
    final serviceCount = _stats['serviceCount'] as Map<String, int>? ?? {};
    final sortedServices = serviceCount.entries.toList()
      ..sort((a, b) => b.value.compareTo(a.value));

    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Services les plus demandés',
            style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 20),
          if (sortedServices.isEmpty)
            const Center(
              child: Padding(
                padding: EdgeInsets.all(20),
                child: Text('Aucune donnée disponible'),
              ),
            )
          else
            ...sortedServices.take(5).map((entry) {
              final percentage = (_stats['total'] as int? ?? 1) > 0
                  ? (entry.value / (_stats['total'] as int? ?? 1) * 100)
                  : 0.0;
              return Padding(
                padding: const EdgeInsets.only(bottom: 16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Text(
                            entry.key,
                            style: const TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 14,
                            ),
                          ),
                        ),
                        Text(
                          '${entry.value} réservations',
                          style: TextStyle(
                            color: Colors.grey[600],
                            fontSize: 12,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    ClipRRect(
                      borderRadius: BorderRadius.circular(4),
                      child: LinearProgressIndicator(
                        value: percentage / 100,
                        minHeight: 8,
                        backgroundColor: Colors.grey[200],
                        valueColor: AlwaysStoppedAnimation<Color>(
                          const Color(0xFFE9D7C2),
                        ),
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '${percentage.toStringAsFixed(1)}%',
                      style: TextStyle(fontSize: 11, color: Colors.grey[600]),
                    ),
                  ],
                ),
              );
            }),
        ],
      ),
    );
  }

  Widget _buildServiceRevenue() {
    final serviceRevenue =
        _stats['serviceRevenue'] as Map<String, double>? ?? {};
    final sortedRevenue = serviceRevenue.entries.toList()
      ..sort((a, b) => b.value.compareTo(a.value));

    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Revenus par service',
            style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 20),
          if (sortedRevenue.isEmpty)
            const Center(
              child: Padding(
                padding: EdgeInsets.all(20),
                child: Text('Aucune donnée disponible'),
              ),
            )
          else
            ...sortedRevenue.map((entry) {
              return Padding(
                padding: const EdgeInsets.only(bottom: 12),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: Text(
                        entry.key,
                        style: const TextStyle(
                          fontWeight: FontWeight.w500,
                          fontSize: 14,
                        ),
                      ),
                    ),
                    Text(
                      '${entry.value.toStringAsFixed(0)} DH',
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                        color: Color(0xFFE9D7C2),
                      ),
                    ),
                  ],
                ),
              );
            }),
        ],
      ),
    );
  }
}
