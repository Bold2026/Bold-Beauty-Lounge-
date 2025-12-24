==import 'package:flutter/material.dart';

class AdminLayout extends StatelessWidget {
  final Widget child;
  final String? currentRoute;

  const AdminLayout({super.key, required this.child, this.currentRoute});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Row(
        children: [
          // Left Sidebar
          _buildSidebar(context),

          // Main Content Area
          Expanded(
            child: Column(
              children: [
                // Top Header
                _buildHeader(context),

                // Main Content
                Expanded(child: child),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSidebar(BuildContext context) {
    return Container(
      width: 250,
      color: Colors.white,
      child: Column(
        children: [
          // Logo
          Container(
            padding: const EdgeInsets.all(20),
            child: Row(
              children: [
                Container(
                  width: 40,
                  height: 40,
                  decoration: BoxDecoration(
                    color: const Color(0xFF9C27B0),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Icon(Icons.spa, color: Colors.white, size: 24),
                ),
                const SizedBox(width: 12),
                const Text(
                  'Bold Beauty',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: Colors.black,
                  ),
                ),
              ],
            ),
          ),
          const Divider(height: 1),

          // Navigation Items
          Expanded(
            child: ListView(
              padding: const EdgeInsets.symmetric(vertical: 8),
              children: [
                _buildNavItem(
                  context,
                  icon: Icons.dashboard,
                  label: 'Dashboard',
                  route: '/dashboard',
                  isActive: currentRoute == '/dashboard',
                ),
                _buildNavItem(
                  context,
                  icon: Icons.calendar_today,
                  label: 'Appointments',
                  route: '/appointments',
                  isActive: currentRoute == '/appointments',
                ),
                _buildNavItem(
                  context,
                  icon: Icons.people,
                  label: 'Employees',
                  route: '/employees',
                  isActive: currentRoute == '/employees',
                ),
                _buildNavItem(
                  context,
                  icon: Icons.person,
                  label: 'Customers',
                  route: '/customers',
                  isActive: currentRoute == '/customers',
                ),
                _buildNavItem(
                  context,
                  icon: Icons.local_offer,
                  label: 'Offers',
                  route: '/offers',
                  isActive: currentRoute == '/offers',
                ),
                _buildNavItem(
                  context,
                  icon: Icons.content_cut,
                  label: 'Services',
                  route: '/services',
                  isActive: currentRoute == '/services',
                ),
                _buildNavItem(
                  context,
                  icon: Icons.swap_horiz,
                  label: 'Transactions',
                  route: '/transactions',
                  isActive: currentRoute == '/transactions',
                ),
                _buildNavItem(
                  context,
                  icon: Icons.star,
                  label: 'Reviews',
                  route: '/reviews',
                  isActive: currentRoute == '/reviews',
                ),
                _buildNavItem(
                  context,
                  icon: Icons.inventory,
                  label: 'Products',
                  route: '/products',
                  isActive: currentRoute == '/products',
                ),
                _buildNavItem(
                  context,
                  icon: Icons.settings,
                  label: 'Settings',
                  route: '/settings',
                  isActive: currentRoute == '/settings',
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildNavItem(
    BuildContext context, {
    required IconData icon,
    required String label,
    required String route,
    required bool isActive,
  }) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: isActive ? const Color(0xFF9C27B0) : Colors.transparent,
        borderRadius: BorderRadius.circular(8),
      ),
      child: ListTile(
        leading: Icon(
          icon,
          color: isActive ? Colors.white : Colors.grey[700],
          size: 20,
        ),
        title: Text(
          label,
          style: TextStyle(
            color: isActive ? Colors.white : Colors.black87,
            fontWeight: isActive ? FontWeight.bold : FontWeight.normal,
            fontSize: 14,
          ),
        ),
        onTap: () {
          // Navigate to route
          Navigator.pushNamed(context, route);
        },
      ),
    );
  }

  Widget _buildHeader(BuildContext context) {
    return Container(
      height: 70,
      padding: const EdgeInsets.symmetric(horizontal: 24),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        children: [
          // Hamburger Menu
          IconButton(icon: const Icon(Icons.menu), onPressed: () {}),
          const SizedBox(width: 16),

          // Page Title (will be set by child)
          const Expanded(
            child: Text(
              'Dashboard',
              style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
            ),
          ),

          // Right side - Notifications and User
          IconButton(
            icon: const Icon(Icons.notifications_outlined),
            color: Colors.orange,
            onPressed: () {},
          ),
          const SizedBox(width: 12),

          // User Profile
          Row(
            children: [
              const CircleAvatar(
                radius: 18,
                backgroundColor: Colors.grey,
                child: Icon(Icons.person, color: Colors.white),
              ),
              const SizedBox(width: 12),
              Column(
                mainAxisAlignment: MainAxisAlignment.center,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: const [
                  Text(
                    'Admin User',
                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                  ),
                  Text(
                    'Super Admin',
                    style: TextStyle(fontSize: 12, color: Colors.grey),
                  ),
                ],
              ),
              const SizedBox(width: 8),
              const Icon(Icons.arrow_drop_down, color: Colors.grey),
            ],
          ),
        ],
      ),
    );
  }
}
