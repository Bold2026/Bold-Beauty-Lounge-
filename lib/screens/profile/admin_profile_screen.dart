import 'package:flutter/material.dart';
import 'package:firebase_auth/firebase_auth.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import '../admin/admin_access_screen.dart';
import '../admin/admin_dashboard_screen.dart';
import '../../services/admin_service.dart';
import '../../services/auth_service.dart';
import '../auth/login_screen.dart';
import '../booking/service_booking_screen.dart';
import 'user_bookings_screen.dart';

class AdminProfileScreen extends StatefulWidget {
  const AdminProfileScreen({super.key});

  @override
  State<AdminProfileScreen> createState() => _AdminProfileScreenState();
}

class _AdminProfileScreenState extends State<AdminProfileScreen> {
  final AdminService _adminService = AdminService();
  final AuthService _authService = AuthService();
  bool _isAdmin = false;
  bool _isLoading = true;
  Map<String, dynamic>? _userData;

  @override
  void initState() {
    super.initState();
    _loadUserData();
  }

  Future<void> _loadUserData() async {
    setState(() {
      _isLoading = true;
    });

    final isAdmin = await _adminService.isAdmin();
    final user = _authService.getCurrentUser();

    if (user != null) {
      final userData = await _authService.getUserData(user.uid);
      setState(() {
        _isAdmin = isAdmin;
        _userData = userData;
        _isLoading = false;
      });
    } else {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = FirebaseAuth.instance.currentUser;

    if (_isLoading) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    if (user == null) {
      return Scaffold(
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.person_off, size: 64, color: Colors.grey),
              const SizedBox(height: 16),
              const Text(
                'Vous n\'êtes pas connecté',
                style: TextStyle(fontSize: 18, color: Colors.grey),
              ),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => const LoginScreen(),
                    ),
                  );
                },
                child: const Text('Se connecter'),
              ),
            ],
          ),
        ),
      );
    }

    return Scaffold(
      backgroundColor: const Color(0xFFF5F5F5),
      body: SingleChildScrollView(
        child: Column(
          children: [
            // En-tête avec photo de profil
            Container(
              width: double.infinity,
              padding: const EdgeInsets.fromLTRB(20, 60, 20, 30),
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  colors: [Color(0xFF1F1A17), Color(0xFF2E2318)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
              ),
              child: Column(
                children: [
                  CircleAvatar(
                    radius: 50,
                    backgroundColor: const Color(0xFFE9D7C2).withOpacity(0.2),
                    backgroundImage: user.photoURL != null
                        ? NetworkImage(user.photoURL!)
                        : null,
                    child: user.photoURL == null
                        ? const Icon(
                            Icons.person,
                            size: 50,
                            color: Color(0xFFE9D7C2),
                          )
                        : null,
                  ),
                  const SizedBox(height: 16),
                  Text(
                    user.displayName ?? _userData?['firstName'] != null
                        ? '${_userData!['firstName']} ${_userData!['lastName']}'
                        : 'Utilisateur',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 24,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    user.email ?? '',
                    style: TextStyle(
                      color: Colors.white.withOpacity(0.8),
                      fontSize: 14,
                    ),
                  ),
                  if (_userData?['phone'] != null) ...[
                    const SizedBox(height: 4),
                    Text(
                      _userData!['phone'] as String,
                      style: TextStyle(
                        color: Colors.white.withOpacity(0.8),
                        fontSize: 14,
                      ),
                    ),
                  ],
                ],
              ),
            ),

            // Options du menu
            Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                children: [
                  // Mes réservations
                  _buildMenuTile(
                    icon: Icons.calendar_today,
                    title: 'Mes réservations',
                    subtitle: 'Voir toutes mes réservations',
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => const UserBookingsScreen(),
                        ),
                      );
                    },
                  ),

                  const SizedBox(height: 12),

                  // Nouvelle réservation
                  _buildMenuTile(
                    icon: Icons.add_circle_outline,
                    title: 'Nouvelle réservation',
                    subtitle: 'Réserver un service',
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => const ServiceBookingScreen(),
                        ),
                      );
                    },
                  ),

                  const SizedBox(height: 12),

                  // Panneau d'administration (si admin)
                  if (_isAdmin) ...[
                    _buildMenuTile(
                      icon: Icons.admin_panel_settings,
                      title: 'Panneau d\'administration',
                      subtitle: 'Gérer les réservations et statistiques',
                      color: Colors.black,
                      onTap: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (context) => const AdminDashboardScreen(),
                          ),
                        );
                      },
                    ),
                    const SizedBox(height: 12),
                  ] else ...[
                    _buildMenuTile(
                      icon: Icons.admin_panel_settings,
                      title: 'Accès administrateur',
                      subtitle: 'Accéder au panneau d\'administration',
                      color: Colors.grey,
                      onTap: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (context) => const AdminAccessScreen(),
                          ),
                        );
                      },
                    ),
                    const SizedBox(height: 12),
                  ],

                  // Paramètres
                  _buildMenuTile(
                    icon: Icons.settings,
                    title: 'Paramètres',
                    subtitle: 'Notifications, préférences',
                    onTap: () {
                      // TODO: Implémenter l'écran de paramètres
                    },
                  ),

                  const SizedBox(height: 12),

                  // Déconnexion
                  _buildMenuTile(
                    icon: Icons.logout,
                    title: 'Déconnexion',
                    subtitle: 'Se déconnecter de votre compte',
                    color: Colors.red,
                    onTap: () async {
                      final confirm = await showDialog<bool>(
                        context: context,
                        builder: (context) => AlertDialog(
                          title: const Text('Déconnexion'),
                          content: const Text(
                            'Êtes-vous sûr de vouloir vous déconnecter ?',
                          ),
                          actions: [
                            TextButton(
                              onPressed: () => Navigator.pop(context, false),
                              child: const Text('Annuler'),
                            ),
                            TextButton(
                              onPressed: () => Navigator.pop(context, true),
                              child: const Text('Déconnexion'),
                            ),
                          ],
                        ),
                      );

                      if (confirm == true) {
                        await _authService.signOut();
                        if (mounted) {
                          Navigator.pushAndRemoveUntil(
                            context,
                            MaterialPageRoute(
                              builder: (context) => const LoginScreen(),
                            ),
                            (route) => false,
                          );
                        }
                      }
                    },
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMenuTile({
    required IconData icon,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
    Color? color,
  }) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(16),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: (color ?? const Color(0xFFE9D7C2)).withOpacity(0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(
                  icon,
                  color: color ?? const Color(0xFFE9D7C2),
                  size: 24,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: color ?? Colors.black,
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
              Icon(Icons.chevron_right, color: Colors.grey[400]),
            ],
          ),
        ),
      ),
    );
  }
}
