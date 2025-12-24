import 'package:flutter/material.dart';
import 'admin_dashboard_screen.dart';
import 'admin_access_screen.dart';
import 'booking_management_screen.dart';
import 'admin_statistics_screen.dart';

/// Écran de test pour accéder directement au panneau d'administration
/// Utilisez cet écran pour tester rapidement le panneau admin
class TestAdminScreen extends StatelessWidget {
  const TestAdminScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F5F5),
      appBar: AppBar(
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
        title: const Text(
          'Test - Panneau Admin',
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Text(
                'Accès direct au panneau d\'administration',
                style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 40),

              // Bouton Tableau de bord
              _buildAccessButton(
                context,
                'Tableau de bord',
                'Vue d\'ensemble des statistiques',
                Icons.dashboard,
                Colors.blue,
                () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => const AdminDashboardScreen(),
                    ),
                  );
                },
              ),

              const SizedBox(height: 16),

              // Bouton Gestion des réservations
              _buildAccessButton(
                context,
                'Gestion des réservations',
                'Voir et gérer toutes les réservations',
                Icons.event_note,
                Colors.green,
                () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => const BookingManagementScreen(),
                    ),
                  );
                },
              ),

              const SizedBox(height: 16),

              // Bouton Statistiques
              _buildAccessButton(
                context,
                'Statistiques détaillées',
                'Analyses et rapports',
                Icons.analytics,
                const Color(0xFFE9D7C2),
                () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => const AdminStatisticsScreen(),
                    ),
                  );
                },
              ),

              const SizedBox(height: 16),

              // Bouton Accès admin (avec mot de passe)
              _buildAccessButton(
                context,
                'Accès administrateur',
                'Écran d\'authentification admin',
                Icons.admin_panel_settings,
                Colors.orange,
                () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => const AdminAccessScreen(),
                    ),
                  );
                },
              ),

              const SizedBox(height: 40),

              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.blue[50],
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.blue[200]!),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Icon(Icons.info_outline, color: Colors.blue[700]),
                        const SizedBox(width: 8),
                        Text(
                          'Note',
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            color: Colors.blue[900],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Ces écrans sont accessibles directement pour les tests. '
                      'En production, l\'accès nécessite une authentification admin.',
                      style: TextStyle(fontSize: 12, color: Colors.blue[900]),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildAccessButton(
    BuildContext context,
    String title,
    String subtitle,
    IconData icon,
    Color color,
    VoidCallback onTap,
  ) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(16),
      elevation: 2,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: color.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(icon, color: color, size: 28),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: Colors.black,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      subtitle,
                      style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                    ),
                  ],
                ),
              ),
              Icon(Icons.arrow_forward_ios, color: Colors.grey[400], size: 16),
            ],
          ),
        ),
      ),
    );
  }
}
