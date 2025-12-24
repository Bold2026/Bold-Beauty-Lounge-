import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/admin/dashboard_provider.dart';
import '../../providers/admin/services_provider.dart';
import '../../repositories/admin/bookings_repository.dart';
import '../../repositories/admin/services_repository.dart';

class AdminDashboardScreen extends StatelessWidget {
  const AdminDashboardScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(
          create: (_) => DashboardProvider(
            repository: BookingsRepository(),
          ),
        ),
        ChangeNotifierProvider(
          create: (_) => ServicesProvider(
            repository: ServicesRepository(),
          ),
        ),
      ],
      child: const _DashboardContent(),
    );
  }
}

class _DashboardContent extends StatelessWidget {
  const _DashboardContent();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Tableau de bord',
                      style: TextStyle(
                        fontSize: 28,
                        fontWeight: FontWeight.bold,
                        color: Colors.black,
                      ),
                    ),
                    SizedBox(height: 4),
                    Text(
                      'Vue d\'ensemble de votre salon',
                      style: TextStyle(
                        fontSize: 14,
                        color: Colors.black54,
                      ),
                    ),
                  ],
                ),
                IconButton(
                  icon: const Icon(Icons.refresh),
                  onPressed: () {
                    context.read<DashboardProvider>().refresh();
                  },
                ),
              ],
            ),

            const SizedBox(height: 32),

            // Statistics cards
            Consumer<DashboardProvider>(
              builder: (context, dashboardProvider, _) {
                if (dashboardProvider.isLoading) {
                  return const Center(
                    child: Padding(
                      padding: EdgeInsets.all(48.0),
                      child: CircularProgressIndicator(),
                    ),
                  );
                }

                if (dashboardProvider.error != null) {
                  return Center(
                    child: Padding(
                      padding: const EdgeInsets.all(48.0),
                      child: Text(
                        dashboardProvider.error!,
                        style: const TextStyle(color: Colors.red),
                      ),
                    ),
                  );
                }

                return Row(
                  children: [
                    Expanded(
                      child: _StatCard(
                        title: 'Réservations aujourd\'hui',
                        value: dashboardProvider.todayBookings.toString(),
                        icon: Icons.calendar_today,
                        color: Colors.blue,
                      ),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: _StatCard(
                        title: 'Réservations ce mois',
                        value: dashboardProvider.monthBookings.toString(),
                        icon: Icons.date_range,
                        color: Colors.green,
                      ),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: _MostBookedServiceCard(
                        serviceId: dashboardProvider.mostBookedServiceId,
                      ),
                    ),
                  ],
                );
              },
            ),

            const SizedBox(height: 32),

            // Recent bookings section
            const Text(
              'Réservations récentes',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: Colors.black,
              ),
            ),
            const SizedBox(height: 16),
            Container(
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.05),
                    blurRadius: 10,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: const _RecentBookingsList(),
            ),
          ],
        ),
      ),
    );
  }
}

class _StatCard extends StatelessWidget {
  final String title;
  final String value;
  final IconData icon;
  final Color color;

  const _StatCard({
    required this.title,
    required this.value,
    required this.icon,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
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
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: color.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Icon(icon, color: color, size: 24),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Text(
            value,
            style: const TextStyle(
              fontSize: 32,
              fontWeight: FontWeight.bold,
              color: Colors.black,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            title,
            style: const TextStyle(
              fontSize: 14,
              color: Colors.black54,
            ),
          ),
        ],
      ),
    );
  }
}

class _MostBookedServiceCard extends StatelessWidget {
  final String? serviceId;

  const _MostBookedServiceCard({this.serviceId});

  @override
  Widget build(BuildContext context) {
    return Consumer<ServicesProvider>(
      builder: (context, servicesProvider, _) {
        String serviceName = 'Aucun';
        if (serviceId != null && servicesProvider.services.isNotEmpty) {
          try {
            final service = servicesProvider.services
                .firstWhere((s) => s.id == serviceId);
            serviceName = service.name;
          } catch (e) {
            serviceName = 'Service introuvable';
          }
        }

        return Container(
          padding: const EdgeInsets.all(24),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
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
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: const Color(0xFFDDD1BC).withOpacity(0.3),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Icon(
                      Icons.spa,
                      color: Color(0xFFDDD1BC),
                      size: 24,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              Text(
                serviceName,
                style: const TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                  color: Colors.black,
                ),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: 4),
              const Text(
                'Service le plus réservé',
                style: TextStyle(
                  fontSize: 14,
                  color: Colors.black54,
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}

class _RecentBookingsList extends StatelessWidget {
  const _RecentBookingsList();

  @override
  Widget build(BuildContext context) {
    // This would be replaced with actual recent bookings stream
    return const Padding(
      padding: EdgeInsets.all(24.0),
      child: Center(
        child: Text(
          'Les réservations récentes apparaîtront ici',
          style: TextStyle(color: Colors.black54),
        ),
      ),
    );
  }
}

