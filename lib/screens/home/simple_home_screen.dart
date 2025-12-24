import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../services/translation_service.dart';
import '../detail/bold_beauty_detail_page.dart';
import '../pricing/detailed_pricing_screen.dart';
import '../auth/login_screen.dart';

class SimpleHomeScreen extends StatelessWidget {
  const SimpleHomeScreen({super.key});

  // Services data
  final List<Map<String, dynamic>> services = const [
    {'name': 'Coiffure', 'image': 'assets/services/coiffure.jpg'},
    {'name': 'Onglerie', 'image': 'assets/services/onglerie.jpg'},
    {'name': 'Hammam', 'image': 'assets/services/hammam.jpg'},
    {'name': 'Massages', 'image': 'assets/services/massages.jpg'},
    {'name': 'Head Spa', 'image': 'assets/services/head spa.jpg'},
    {'name': 'Soins', 'image': 'assets/services/soins.jpg'},
  ];

  // Specialists data
  final List<Map<String, dynamic>> specialists = const [
    {
      'name': 'Laila Bazzi',
      'title': 'Directeur général',
      'rating': 4.9,
      'image': 'assets/specialists/leila bazi.jpg',
    },
    {
      'name': 'Nasira Mounir',
      'title': 'Esthéticienne Senior',
      'rating': 4.8,
      'image': 'assets/specialists/nasira mounir.jpg',
    },
    {
      'name': 'Laarach Fadoua',
      'title': 'Esthéticienne Senior',
      'rating': 4.9,
      'image': 'assets/specialists/laarach fadoua.jpg',
    },
  ];

  Future<void> _openGoogleMaps() async {
    const String googleMapsUrl = 'https://maps.app.goo.gl/BW1d99JkP1QK29W17';
    try {
      final Uri url = Uri.parse(googleMapsUrl);
      if (await canLaunchUrl(url)) {
        await launchUrl(url, mode: LaunchMode.externalApplication);
      }
    } catch (e) {
      debugPrint('Error opening maps: $e');
    }
  }

  Future<void> _openWhatsApp() async {
    const String whatsappUrl = 'https://wa.me/212619249249';
    try {
      final Uri url = Uri.parse(whatsappUrl);
      if (await canLaunchUrl(url)) {
        await launchUrl(url, mode: LaunchMode.externalApplication);
      }
    } catch (e) {
      debugPrint('Error opening WhatsApp: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    final translationService = TranslationService.instance;

    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header
              _buildHeader(context, translationService),
              const SizedBox(height: 20),

              // Services
              _buildServicesSection(context, translationService),
              const SizedBox(height: 30),

              // Main Image Banner
              _buildMainBanner(context),
              const SizedBox(height: 30),

              // Salon Info
              _buildSalonInfo(context),
              const SizedBox(height: 30),

              // Actions
              _buildActions(context),
              const SizedBox(height: 30),

              // Team
              _buildTeamSection(context),
              const SizedBox(height: 50),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeader(
    BuildContext context,
    TranslationService translationService,
  ) {
    return Container(
      color: Colors.black,
      padding: const EdgeInsets.all(20),
      child: Row(
        children: [
          // Logo
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: const Color(0xFFE9D7C2),
              borderRadius: BorderRadius.circular(8),
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(8),
              child: Image.asset(
                'assets/logo/logo1.png',
                fit: BoxFit.contain,
                errorBuilder: (context, error, stackTrace) {
                  return const Center(
                    child: Text(
                      'B',
                      style: TextStyle(
                        color: Colors.black,
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  );
                },
              ),
            ),
          ),
          const SizedBox(width: 12),
          // Text
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Bonjour !',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 22,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                GestureDetector(
                  onTap: _openGoogleMaps,
                  child: Row(
                    children: [
                      const Icon(
                        Icons.location_on,
                        color: Colors.white,
                        size: 16,
                      ),
                      const SizedBox(width: 4),
                      const Text(
                        'Casablanca, Maroc',
                        style: TextStyle(color: Colors.white70, fontSize: 14),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          // Menu button
          IconButton(
            icon: const Icon(Icons.menu, color: Colors.white),
            onPressed: () {
              Navigator.pushNamed(context, '/home').then((_) {
                // After profile navigation, go back to current index
              });
            },
          ),
        ],
      ),
    );
  }

  Widget _buildServicesSection(
    BuildContext context,
    TranslationService translationService,
  ) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Nos Services',
            style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 16),
          SizedBox(
            height: 300,
            child: GridView.builder(
              scrollDirection: Axis.horizontal,
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 2,
                mainAxisSpacing: 12,
                crossAxisSpacing: 12,
                childAspectRatio: 0.8,
              ),
              itemCount: services.length,
              itemBuilder: (context, index) {
                final service = services[index];
                return GestureDetector(
                  onTap: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (context) => const DetailedPricingScreen(),
                      ),
                    );
                  },
                  child: Container(
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.grey.withOpacity(0.2),
                          blurRadius: 10,
                          offset: const Offset(0, 5),
                        ),
                      ],
                    ),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Expanded(
                          child: ClipRRect(
                            borderRadius: BorderRadius.circular(12),
                            child: Image.asset(
                              service['image'],
                              fit: BoxFit.cover,
                              errorBuilder: (context, error, stackTrace) {
                                return Container(
                                  color: Colors.grey[300],
                                  child: const Icon(Icons.spa, size: 50),
                                );
                              },
                            ),
                          ),
                        ),
                        const SizedBox(height: 8),
                        Padding(
                          padding: const EdgeInsets.all(8.0),
                          child: Text(
                            service['name'],
                            style: const TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.bold,
                            ),
                            textAlign: TextAlign.center,
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMainBanner(BuildContext context) {
    return GestureDetector(
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(builder: (context) => const BoldBeautyDetailPage()),
        );
      },
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 20),
        height: 200,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(20),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.2),
              blurRadius: 20,
              offset: const Offset(0, 10),
            ),
          ],
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(20),
          child: Stack(
            children: [
              Image.asset(
                'assets/boldbeautylounge.jpg',
                fit: BoxFit.cover,
                width: double.infinity,
                height: double.infinity,
                errorBuilder: (context, error, stackTrace) {
                  return Container(
                    color: Colors.grey[300],
                    child: const Center(
                      child: Icon(Icons.spa, size: 60, color: Colors.grey),
                    ),
                  );
                },
              ),
              Container(
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                    colors: [Colors.transparent, Colors.black.withOpacity(0.7)],
                  ),
                ),
              ),
              Positioned(
                bottom: 20,
                left: 20,
                right: 20,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 12,
                            vertical: 6,
                          ),
                          decoration: BoxDecoration(
                            color: const Color(0xFFE9D7C2),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: const Text(
                            'TOP',
                            style: TextStyle(
                              color: Colors.black,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Row(
                          children: [
                            const Icon(
                              Icons.star,
                              color: Color(0xFFE9D7C2),
                              size: 20,
                            ),
                            const SizedBox(width: 4),
                            const Text(
                              '4.9',
                              style: TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    const Text(
                      'Bold Beauty Lounge',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                      ),
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

  Widget _buildSalonInfo(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Notre Salon',
            style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.grey[50],
              borderRadius: BorderRadius.circular(12),
            ),
            child: Column(
              children: [
                Row(
                  children: [
                    const Icon(Icons.access_time, color: Color(0xFFE9D7C2)),
                    const SizedBox(width: 8),
                    const Text(
                      'Horaires',
                      style: TextStyle(fontWeight: FontWeight.bold),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                const Text('Lundi-Vendredi: 9h-20h'),
                const Text('Samedi: 9h-19h'),
                const Text('Dimanche: 10h-18h'),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildActions(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20),
      child: Row(
        children: [
          Expanded(
            child: ElevatedButton.icon(
              onPressed: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (context) => const DetailedPricingScreen(),
                  ),
                );
              },
              icon: const Icon(Icons.calendar_today),
              label: const Text('Prendre RDV'),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFFE9D7C2),
                foregroundColor: Colors.black,
                padding: const EdgeInsets.symmetric(vertical: 16),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
          ),
          const SizedBox(width: 12),
          ElevatedButton(
            onPressed: _openWhatsApp,
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.green,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.all(16),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
            child: const Icon(Icons.chat),
          ),
        ],
      ),
    );
  }

  Widget _buildTeamSection(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Notre Équipe',
            style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),
          SizedBox(
            height: 150,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              itemCount: specialists.length,
              itemBuilder: (context, index) {
                final specialist = specialists[index];
                return Container(
                  width: 120,
                  margin: const EdgeInsets.only(right: 12),
                  child: Column(
                    children: [
                      Expanded(
                        child: Container(
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(12),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.grey.withOpacity(0.2),
                                blurRadius: 10,
                                offset: const Offset(0, 5),
                              ),
                            ],
                          ),
                          child: ClipRRect(
                            borderRadius: BorderRadius.circular(12),
                            child: Image.asset(
                              specialist['image'],
                              fit: BoxFit.cover,
                              errorBuilder: (context, error, stackTrace) {
                                return Container(
                                  color: Colors.grey[300],
                                  child: const Icon(Icons.person, size: 40),
                                );
                              },
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        specialist['name'],
                        style: const TextStyle(fontWeight: FontWeight.bold),
                        textAlign: TextAlign.center,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      Text(
                        specialist['title'],
                        style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                        textAlign: TextAlign.center,
                        maxLines: 2,
                      ),
                    ],
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}






