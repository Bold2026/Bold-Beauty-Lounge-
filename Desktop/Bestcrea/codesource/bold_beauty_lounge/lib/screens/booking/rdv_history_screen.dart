import 'package:flutter/material.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../pricing/detailed_pricing_screen.dart';
import 'service_selection_screen.dart';
// import '../../services/firebase_reservation_service.dart';
import '../../services/reservation_service.dart';

class RdvHistoryScreen extends StatefulWidget {
  const RdvHistoryScreen({super.key});

  @override
  State<RdvHistoryScreen> createState() => _RdvHistoryScreenState();
}

class _RdvHistoryScreenState extends State<RdvHistoryScreen> {
  String selectedTab = 'À venir';

  final List<String> _tabs = ['À venir', 'Passés', 'Annulés'];

  // Données des RDV
  List<Map<String, dynamic>> _upcomingRdvs = [];
  List<Map<String, dynamic>> _pastRdvs = [];
  List<Map<String, dynamic>> _cancelledRdvs = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadReservations();
  }

  Future<void> _loadReservations() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final allReservations = await ReservationService.getReservations();

      // Séparer les réservations par statut
      _upcomingRdvs = allReservations
          .where(
            (r) => r['status'] == 'Confirmé' || r['status'] == 'En attente',
          )
          .map((r) => ReservationService.formatReservationForDisplay(r))
          .toList();

      _pastRdvs = allReservations
          .where((r) => r['status'] == 'Terminé')
          .map((r) => ReservationService.formatReservationForDisplay(r))
          .toList();

      _cancelledRdvs = allReservations
          .where((r) => r['status'] == 'Annulé')
          .map((r) => ReservationService.formatReservationForDisplay(r))
          .toList();

      // Trier les RDV à venir par date
      _upcomingRdvs.sort((a, b) {
        final dateA = DateTime.parse(a['date'].split('/').reversed.join('-'));
        final dateB = DateTime.parse(b['date'].split('/').reversed.join('-'));
        return dateA.compareTo(dateB);
      });
    } catch (e) {
      print('Erreur lors du chargement des réservations: $e');
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        backgroundColor: Colors.white,
        foregroundColor: Colors.black,
        elevation: 0,
        title: const Text(
          'Mes rendez-vous',
          style: TextStyle(
            color: Colors.black,
            fontSize: 24,
            fontWeight: FontWeight.bold,
          ),
        ),
        centerTitle: true,
        actions: [
          IconButton(
            icon: Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                border: Border.all(color: Colors.grey.shade300),
              ),
              child: const Icon(
                LucideIcons.plus,
                color: Colors.black,
                size: 20,
              ),
            ),
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) => const DetailedPricingScreen(),
                ),
              );
            },
          ),
        ],
      ),
      body: Column(
        children: [
          // Tabs de navigation
          _buildTabsSection(),

          // Contenu principal
          Expanded(child: _buildContent()),

          // Bouton de réservation
          _buildReservationButton(),
        ],
      ),
    );
  }

  Widget _buildTabsSection() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
      child: Row(
        children: _tabs.map((tab) {
          final isSelected = selectedTab == tab;
          final count = _getRdvsCount(tab);

          return Expanded(
            child: GestureDetector(
              onTap: () {
                setState(() {
                  selectedTab = tab;
                });
              },
              child: Container(
                padding: const EdgeInsets.symmetric(vertical: 12),
                margin: const EdgeInsets.symmetric(horizontal: 4),
                decoration: BoxDecoration(
                  color: isSelected
                      ? const Color(0xFFE9D7C2)
                      : Colors.transparent,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      _getTabIcon(tab),
                      size: 18,
                      color: Colors.black,
                    ),
                    const SizedBox(width: 6),
                    Text(
                      '$tab ($count)',
                      style: TextStyle(
                        color: Colors.black,
                        fontWeight: isSelected
                            ? FontWeight.bold
                            : FontWeight.normal,
                        fontSize: 14,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildContent() {
    if (_isLoading) {
      return const Center(
        child: CircularProgressIndicator(
          valueColor: AlwaysStoppedAnimation<Color>(Color(0xFFE9D7C2)),
        ),
      );
    }

    final rdvs = _getCurrentRdvs();

    if (rdvs.isEmpty) {
      return _buildEmptyState();
    }

    return RefreshIndicator(
      onRefresh: _loadReservations,
      color: const Color(0xFFE9D7C2),
      child: ListView.builder(
        padding: const EdgeInsets.symmetric(horizontal: 20),
        itemCount: rdvs.length,
        itemBuilder: (context, index) {
          final rdv = rdvs[index];
          return _buildRdvCard(rdv);
        },
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            width: 100,
            height: 100,
            decoration: BoxDecoration(
              color: Colors.grey[200],
              shape: BoxShape.circle,
            ),
            child: Icon(
              LucideIcons.calendarClock,
              size: 48,
              color: Colors.grey[400],
            ),
          ),
          const SizedBox(height: 24),
          Text(
            _getEmptyStateText(),
            style: const TextStyle(
              fontSize: 18,
              color: Colors.black,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Réservez votre prochain service',
            style: TextStyle(
              fontSize: 14,
              color: Colors.grey[600],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRdvCard(Map<String, dynamic> rdv) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey[200]!),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                rdv['service'],
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Colors.black,
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 12,
                  vertical: 6,
                ),
                decoration: BoxDecoration(
                  color: _getStatusColor(rdv['status']),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  rdv['status'],
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Icon(LucideIcons.clock3, size: 18, color: Colors.grey[600]),
              const SizedBox(width: 8),
              Text(
                rdv['time'],
                style: TextStyle(fontSize: 14, color: Colors.grey[600]),
              ),
              const SizedBox(width: 24),
              Icon(LucideIcons.calendarDays, size: 18, color: Colors.grey[600]),
              const SizedBox(width: 8),
              Text(
                rdv['date'],
                style: TextStyle(fontSize: 14, color: Colors.grey[600]),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              Icon(LucideIcons.userCircle2, size: 18, color: Colors.grey[600]),
              const SizedBox(width: 8),
              Text(
                rdv['specialist'],
                style: TextStyle(fontSize: 14, color: Colors.grey[600]),
              ),
              const Spacer(),
              Text(
                '${rdv['price']} DH',
                style: const TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFFE9D7C2),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              _buildRdvAction(
                icon: LucideIcons.repeat,
                label: 'Reprogrammer',
                onTap: () {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text(
                        'Fonctionnalité de reprogrammation à venir',
                      ),
                      duration: Duration(seconds: 1),
                    ),
                  );
                },
              ),
              const SizedBox(width: 12),
              _buildRdvAction(
                icon: LucideIcons.trash2,
                label: 'Annuler',
                onTap: () {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('Fonctionnalité d\'annulation à venir'),
                      duration: Duration(seconds: 1),
                    ),
                  );
                },
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildReservationButton() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 20),
      child: ElevatedButton(
        onPressed: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => const ServiceSelectionScreen(),
            ),
          );
        },
        style: ElevatedButton.styleFrom(
          backgroundColor: const Color(0xFFE9D7C2),
          foregroundColor: Colors.black,
          padding: const EdgeInsets.symmetric(vertical: 16),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(26),
          ),
          elevation: 0,
        ),
        child: const Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(LucideIcons.calendarCheck, size: 20, color: Colors.black),
            SizedBox(width: 10),
            Text(
              'Réserver un service',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
          ],
        ),
      ),
    );
  }

  int _getRdvsCount(String tab) {
    switch (tab) {
      case 'À venir':
        return _upcomingRdvs.length;
      case 'Passés':
        return _pastRdvs.length;
      case 'Annulés':
        return _cancelledRdvs.length;
      default:
        return 0;
    }
  }

  List<Map<String, dynamic>> _getCurrentRdvs() {
    switch (selectedTab) {
      case 'À venir':
        return _upcomingRdvs;
      case 'Passés':
        return _pastRdvs;
      case 'Annulés':
        return _cancelledRdvs;
      default:
        return [];
    }
  }

  String _getEmptyStateText() {
    switch (selectedTab) {
      case 'À venir':
        return 'Aucun RDV à venir';
      case 'Passés':
        return 'Aucun RDV passé';
      case 'Annulés':
        return 'Aucun RDV annulé';
      default:
        return 'Aucun RDV';
    }
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'Confirmé':
        return Colors.green;
      case 'En attente':
        return Colors.orange;
      case 'Annulé':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }

  IconData _getTabIcon(String tab) {
    switch (tab) {
      case 'À venir':
        return LucideIcons.calendarClock;
      case 'Passés':
        return LucideIcons.checkCircle;
      case 'Annulés':
        return LucideIcons.xCircle;
      default:
        return LucideIcons.calendar;
    }
  }

  Widget _buildRdvAction({
    required IconData icon,
    required String label,
    required VoidCallback onTap,
  }) {
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [Color(0xFFf9e7d9), Color(0xFFf6d7c1)],
            ),
            borderRadius: BorderRadius.circular(14),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.08),
                blurRadius: 12,
                offset: const Offset(0, 4),
              ),
            ],
            border: Border.all(color: const Color(0xFFE9D7C2).withOpacity(0.4)),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, size: 18, color: Colors.black87),
              const SizedBox(width: 8),
              Text(
                label,
                style: const TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                  color: Colors.black87,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
