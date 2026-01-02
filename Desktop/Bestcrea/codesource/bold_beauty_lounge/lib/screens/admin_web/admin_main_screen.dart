import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/admin/admin_auth_provider.dart';
import '../../widgets/admin/collapsible_sidebar.dart';
import 'admin_login_screen.dart';
import 'admin_dashboard_screen.dart';
import 'admin_bookings_screen.dart';
import 'admin_services_screen.dart';
import 'admin_time_slots_screen.dart';
import 'admin_employees_screen.dart';
import 'admin_customers_screen.dart';
import 'admin_reviews_screen.dart';

class AdminMainScreen extends StatefulWidget {
  const AdminMainScreen({super.key});

  @override
  State<AdminMainScreen> createState() => _AdminMainScreenState();
}

class _AdminMainScreenState extends State<AdminMainScreen> {
  int _selectedIndex = 0;
  bool _isSidebarCollapsed = false; // Expanded by default

  final List<SidebarItem> _navItems = [
    SidebarItem(
      icon: Icons.dashboard_outlined,
      label: 'Tableau de bord',
      screen: const AdminDashboardScreen(),
    ),
    SidebarItem(
      icon: Icons.calendar_today_outlined,
      label: 'Réservations',
      screen: const AdminBookingsScreen(),
    ),
    SidebarItem(
      icon: Icons.people_outlined,
      label: 'Employés',
      screen: const AdminEmployeesScreen(),
    ),
    SidebarItem(
      icon: Icons.person_outline,
      label: 'Clients',
      screen: const AdminCustomersScreen(),
    ),
    SidebarItem(
      icon: Icons.spa_outlined,
      label: 'Services',
      screen: const AdminServicesScreen(),
    ),
    SidebarItem(
      icon: Icons.access_time_outlined,
      label: 'Créneaux horaires',
      screen: const AdminTimeSlotsScreen(),
    ),
    SidebarItem(
      icon: Icons.star_outline,
      label: 'Avis',
      screen: const AdminReviewsScreen(),
    ),
  ];

  @override
  Widget build(BuildContext context) {
    return Consumer<AdminAuthProvider>(
      builder: (context, authProvider, _) {
        if (!authProvider.isAuthenticated) {
          return const AdminLoginScreen();
        }

        return Scaffold(
          backgroundColor: Colors.grey[50],
          body: Row(
            children: [
              // Collapsible Sidebar
              CollapsibleSidebar(
                isCollapsed: _isSidebarCollapsed,
                selectedIndex: _selectedIndex,
                items: _navItems,
                onItemSelected: (index) {
                  setState(() => _selectedIndex = index);
                },
                header: _buildSidebarHeader(),
                footer: _buildSidebarFooter(context, authProvider),
                onLogout: () async {
                  await authProvider.logout();
                  if (mounted) {
                    Navigator.pushReplacement(
                      context,
                      MaterialPageRoute(
                        builder: (_) => const AdminLoginScreen(),
                      ),
                    );
                  }
                },
              ),
              // Main content with AppBar
              Expanded(
                child: Column(
                  children: [
                    // Top AppBar with hamburger menu
                    _buildAppBar(context),
                    // Main content area
                    Expanded(
                      child: _navItems[_selectedIndex].screen,
                    ),
                  ],
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  /// Builds the AppBar with hamburger menu
  Widget _buildAppBar(BuildContext context) {
    return Container(
      height: 64,
      color: Colors.white,
      padding: const EdgeInsets.symmetric(horizontal: 24),
      child: Row(
        children: [
          // Hamburger menu button
          IconButton(
            icon: const Icon(Icons.menu),
            onPressed: () {
              setState(() {
                _isSidebarCollapsed = !_isSidebarCollapsed;
              });
            },
            tooltip: _isSidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar',
          ),
          const SizedBox(width: 16),
          // Page title
          Text(
            _navItems[_selectedIndex].label,
            style: const TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.bold,
              color: Colors.black,
            ),
          ),
          const Spacer(),
          // User info (optional - can be moved to sidebar footer)
          Row(
            children: [
              IconButton(
                icon: const Icon(Icons.notifications_outlined),
                color: Colors.orange,
                onPressed: () {
                  // Handle notifications
                },
              ),
              const SizedBox(width: 8),
              const CircleAvatar(
                radius: 16,
                backgroundColor: Colors.grey,
                child: Icon(Icons.person, size: 20, color: Colors.white),
              ),
              const SizedBox(width: 8),
              const Column(
                mainAxisAlignment: MainAxisAlignment.center,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Test User',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                      color: Colors.black,
                    ),
                  ),
                  Text(
                    'Super Admin (Shire)',
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.black54,
                    ),
                  ),
                ],
              ),
              const SizedBox(width: 8),
              const Icon(Icons.arrow_drop_down),
            ],
          ),
        ],
      ),
    );
  }

  /// Builds the sidebar header (logo)
  Widget _buildSidebarHeader() {
    return Row(
      children: [
        Container(
          width: 40,
          height: 40,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(8),
          ),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(8),
            child: Image.asset(
              'assets/logo/logo1.png',
              width: 40,
              height: 40,
              fit: BoxFit.cover,
              errorBuilder: (context, error, stackTrace) {
                return Container(
                  color: const Color(0xFFDDD1BC),
                  child: const Icon(
                    Icons.spa,
                    color: Colors.black,
                    size: 24,
                  ),
                );
              },
            ),
          ),
        ),
        const SizedBox(width: 12),
        const Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Bold Beauty',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: Colors.black,
                ),
              ),
              Text(
                'Administration',
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.black54,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  /// Builds the sidebar footer (user info)
  Widget _buildSidebarFooter(BuildContext context, AdminAuthProvider authProvider) {
    return Column(
      children: [
        Row(
          children: [
            CircleAvatar(
              radius: 20,
              backgroundColor: const Color(0xFFDDD1BC),
              child: Text(
                authProvider.currentAdmin?.name[0].toUpperCase() ?? 'A',
                style: const TextStyle(
                  color: Colors.black,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    authProvider.currentAdmin?.name ?? 'Admin',
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                      color: Colors.black,
                    ),
                    overflow: TextOverflow.ellipsis,
                  ),
                  Text(
                    authProvider.currentAdmin?.email ?? '',
                    style: const TextStyle(
                      fontSize: 12,
                      color: Colors.black54,
                    ),
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        SizedBox(
          width: double.infinity,
          child: OutlinedButton.icon(
            onPressed: () async {
              await authProvider.logout();
              if (context.mounted) {
                Navigator.pushReplacement(
                  context,
                  MaterialPageRoute(
                    builder: (_) => const AdminLoginScreen(),
                  ),
                );
              }
            },
            icon: const Icon(Icons.logout, size: 18),
            label: const Text('Déconnexion'),
            style: OutlinedButton.styleFrom(
              foregroundColor: Colors.black87,
              side: BorderSide(color: Colors.grey[300]!),
              padding: const EdgeInsets.symmetric(vertical: 12),
            ),
          ),
        ),
      ],
    );
  }
}




