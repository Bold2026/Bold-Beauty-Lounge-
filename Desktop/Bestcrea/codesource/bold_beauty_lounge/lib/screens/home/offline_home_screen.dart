import 'package:flutter/material.dart';
import 'package:lucide_icons/lucide_icons.dart';
import 'package:url_launcher/url_launcher.dart';
import '../detail/bold_beauty_detail_page.dart';
import '../pricing/detailed_pricing_screen.dart';
import '../booking/offline_booking_screen.dart';
import '../profile/offline_profile_screen.dart';
import '../auth/login_screen.dart';
import '../auth/signup_screen.dart';
import '../auth/new_login_screen.dart';
import '../booking/service_selection_screen.dart';
import '../../services/auth_service.dart';

class OfflineHomeScreen extends StatefulWidget {
  const OfflineHomeScreen({super.key});

  @override
  State<OfflineHomeScreen> createState() => _OfflineHomeScreenState();
}

class _OfflineHomeScreenState extends State<OfflineHomeScreen> {
  // Services data
  static const List<Map<String, dynamic>> _services = [
    {
      'name': 'Coiffure',
      'image': 'assets/services/coiffure.jpg',
      'price': 'Dès 70 DH',
      'category': 'Coiffure',
      'tagline': 'Coiffures, couleurs & soins capillaires',
    },
    {
      'name': 'Onglerie',
      'image': 'assets/services/onglerie.jpg',
      'price': 'Dès 50 DH',
      'category': 'Onglerie',
      'tagline': 'Beauté des mains & des pieds',
    },
    {
      'name': 'Hammam',
      'image': 'assets/services/hammam.jpg',
      'price': 'Dès 150 DH',
      'category': 'Hammam',
      'tagline': 'Rituels de bien-être traditionnel',
    },
    {
      'name': 'Massages',
      'image': 'assets/services/massages.jpg',
      'price': 'Dès 100 DH',
      'category': 'Massage & Spa',
      'tagline': 'Relaxation & soins du corps',
    },
    {
      'name': 'Head Spa',
      'image': 'assets/services/head spa.jpg',
      'price': 'Dès 350 DH',
      'category': 'Head Spa',
      'tagline': 'Expériences sensorielles du cuir chevelu',
    },
    {
      'name': 'Soins',
      'image': 'assets/services/soins.jpg',
      'price': 'Dès 25 DH',
      'category': 'Soins Esthétiques',
      'tagline': 'Épilations & soins esthétiques',
    },
  ];

  // Specialists data
  static const List<Map<String, dynamic>> _specialists = [
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
    {
      'name': 'Zineb Zineddine',
      'title': 'Esthéticienne Gestion',
      'rating': 4.7,
      'image': 'assets/specialists/zineb zineddine.jpg',
    },
    {
      'name': 'Bachir Bachir',
      'title': 'Technicien Principal',
      'rating': 4.8,
      'image': 'assets/specialists/bachir.jpeg',
    },
    {
      'name': 'Rajaa Jouani',
      'title': 'Gommeuse',
      'rating': 4.6,
      'image': 'assets/specialists/raja jouani.jpeg',
    },
    {
      'name': 'Hiyar Sanae',
      'title': 'Experts beauté & relaxation',
      'rating': 4.9,
      'image': 'assets/specialists/Hiyar Sanae.jpeg',
    },
  ];

  static const List<Map<String, dynamic>> _comboPacks = [
    {
      "name": "Pause Précieuse",
      "tagline": "Un cadeau tout en douceur pour prendre soin de soi.",
      "price": "590 DH",
      "details": [
        "Hammam Expérience (individuel)",
        "Massage relaxant (30 min)",
        "Pédicure + Brushing",
      ],
    },
    {
      "name": "Douce Évasion",
      "tagline": "Un moment de calme et de soin pour se retrouver.",
      "price": "650 DH",
      "details": [
        "Head Spa Bold Experience (30 min)",
        "Hammam Expérience (individuel)",
        "Brushing",
      ],
    },
    {
      "name": "Luxe & Lâcher-Prise",
      "tagline": "Le combo bien-être intense pour corps & esprit.",
      "price": "1 050 DH",
      "details": [
        "Head Spa Sensual (1h)",
        "Hammam Royal (individuel)",
        "Brushing",
      ],
    },
    {
      "name": "L’Instant Royal",
      "tagline": "Un rituel complet pour une détente absolue.",
      "price": "1 300 DH",
      "details": [
        "Head Spa Royal (1h30)",
        "Hammam Sensation (individuel)",
        "Massage relaxant (30 min)",
      ],
    },
    {
      "name": "Évasion Duo Prestige",
      "tagline": "Une expérience complète.",
      "price": "1 350 DH",
      "details": [
        "Hammam Sensation duo",
        "1 Head Spa Sensation",
        "1 Massage relaxant (1h)",
        "1 Brushing offert avec le Head Spa",
        "1 Brushing supplémentaire",
      ],
    },
  ];

  final List<String> _loyaltyTabs = const [
    'Recommandé',
    'Promotion',
    'Distance',
    'Favoris',
    'Gagner',
    'Historique',
  ];

  int _selectedLoyaltyTab = 0;

  // Page controllers
  late final PageController _specialistPageController;
  late final PageController _packPageController;
  bool _animateSections = false;

  // Search controller
  final TextEditingController _searchController = TextEditingController();

  Future<void> _handlePackTap(BuildContext context) async {
    final bool isLoggedIn = await AuthService.isLoggedInStatic();
    if (isLoggedIn) {
      Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => const OfflineProfileScreen()),
      );
    } else {
      _showAccountPrompt(context);
    }
  }

  Widget _buildLoyaltyContent(BuildContext context, String tab) {
    switch (tab) {
      case 'Promotion':
        return _buildPromotionContent(context);
      case 'Distance':
        return _buildDistanceContent(context);
      case 'Favoris':
        return _buildFavoritesContent(context);
      case 'Gagner':
        return _buildEarnPointsContent();
      case 'Historique':
        return _buildHistoryContent(context);
      case 'Recommandé':
      default:
        return _buildRecommendedContent(context);
    }
  }

  Widget _buildRecommendedContent(BuildContext context) {
    final recommendedServices = _services.take(5).toList();
    return Container(
      key: const ValueKey('loyalty_recommended'),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: const Color(0xFFE9D7C2).withOpacity(0.08),
        borderRadius: BorderRadius.circular(26),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.35),
            blurRadius: 24,
            offset: const Offset(0, 16),
          ),
        ],
        border: Border.all(color: const Color(0xFFE9D7C2).withOpacity(0.35)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Nos services phares',
            style: TextStyle(
              color: Colors.white,
              fontSize: 18,
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: 16),
          ...recommendedServices.map((service) {
            final imagePath = service['image'] as String;
            final name = service['name'] as String;
            final tagline = service['tagline'] as String;
            final price = service['price'] as String;
            return Padding(
              padding: const EdgeInsets.only(bottom: 14),
              child: GestureDetector(
                onTap: () => Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => const DetailedPricingScreen(),
                  ),
                ),
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 14,
                  ),
                  decoration: BoxDecoration(
                    color: const Color(0xFF181818),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(
                      color: const Color(0xFFE9D7C2).withOpacity(0.28),
                    ),
                  ),
                  child: Row(
                    children: [
                      ClipRRect(
                        borderRadius: BorderRadius.circular(16),
                        child: Image.asset(
                          imagePath,
                          width: 68,
                          height: 68,
                          fit: BoxFit.cover,
                          errorBuilder: (context, error, stackTrace) {
                            return Container(
                              width: 68,
                              height: 68,
                              decoration: BoxDecoration(
                                color: const Color(
                                  0xFFE9D7C2,
                                ).withOpacity(0.12),
                                borderRadius: BorderRadius.circular(16),
                              ),
                              child: Icon(
                                LucideIcons.sparkles,
                                color: const Color(0xFFE9D7C2).withOpacity(0.7),
                              ),
                            );
                          },
                        ),
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              name,
                              style: const TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w700,
                                color: Colors.white,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              tagline,
                              style: TextStyle(
                                fontSize: 13,
                                color: Colors.white.withOpacity(0.75),
                              ),
                            ),
                          ],
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 10,
                          vertical: 6,
                        ),
                        decoration: BoxDecoration(
                          color: const Color(0xFFE9D7C2),
                          borderRadius: BorderRadius.circular(18),
                          boxShadow: [
                            BoxShadow(
                              color: const Color(0xFFE9D7C2).withOpacity(0.35),
                              blurRadius: 18,
                              offset: const Offset(0, 8),
                            ),
                          ],
                        ),
                        child: Text(
                          price,
                          style: const TextStyle(
                            fontWeight: FontWeight.w700,
                            fontSize: 12,
                            color: Colors.black,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            );
          }),
        ],
      ),
    );
  }

  Widget _buildPromotionContent(BuildContext context) {
    return Container(
      key: const ValueKey('loyalty_promotion'),
      padding: const EdgeInsets.symmetric(vertical: 8),
      decoration: BoxDecoration(
        color: const Color(0xFF141414),
        borderRadius: BorderRadius.circular(26),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.35),
            blurRadius: 24,
            offset: const Offset(0, 16),
          ),
        ],
        border: Border.all(color: Colors.white.withOpacity(0.06)),
      ),
      child: SizedBox(
        height: 220,
        child: PageView.builder(
          controller: PageController(viewportFraction: 0.88),
          itemCount: _comboPacks.length,
          itemBuilder: (context, index) {
            final pack = _comboPacks[index];
            final name = pack['name'] as String;
            final tagline = pack['tagline'] as String;
            final price = pack['price'] as String;
            final details = (pack['details'] as List<dynamic>).cast<String>();
            return Padding(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
              child: GestureDetector(
                onTap: () => _handlePackTap(context),
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(24),
                    gradient: const LinearGradient(
                      colors: [Color(0xFF1F1A17), Color(0xFF2E2318)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.25),
                        blurRadius: 20,
                        offset: const Offset(0, 16),
                      ),
                    ],
                  ),
                  padding: const EdgeInsets.symmetric(
                    horizontal: 24,
                    vertical: 22,
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        name,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 20,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        tagline,
                        style: const TextStyle(
                          color: Colors.white70,
                          fontSize: 13,
                          height: 1.4,
                        ),
                      ),
                      const Spacer(),
                      Wrap(
                        spacing: 8,
                        runSpacing: 6,
                        children: details
                            .map(
                              (detail) => Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 10,
                                  vertical: 6,
                                ),
                                decoration: BoxDecoration(
                                  color: Colors.white.withValues(alpha: 0.1),
                                  borderRadius: BorderRadius.circular(14),
                                ),
                                child: Text(
                                  detail,
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 11,
                                  ),
                                ),
                              ),
                            )
                            .toList(),
                      ),
                      const SizedBox(height: 14),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            price,
                            style: const TextStyle(
                              color: Color(0xFFE9D7C2),
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 14,
                              vertical: 10,
                            ),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(18),
                            ),
                            child: const Text(
                              'Voir le pack',
                              style: TextStyle(
                                color: Colors.black,
                                fontWeight: FontWeight.w600,
                                fontSize: 12,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
            );
          },
        ),
      ),
    );
  }

  Widget _buildDistanceContent(BuildContext context) {
    return Container(
      key: const ValueKey('loyalty_distance'),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: const Color(0xFFE9D7C2).withOpacity(0.08),
        borderRadius: BorderRadius.circular(26),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.35),
            blurRadius: 24,
            offset: const Offset(0, 16),
          ),
        ],
        border: Border.all(color: const Color(0xFFE9D7C2).withOpacity(0.35)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Bold Beauty Lounge sur la carte',
            style: TextStyle(
              color: Colors.white,
              fontSize: 18,
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: 16),
          ClipRRect(
            borderRadius: BorderRadius.circular(24),
            child: Stack(
              children: [
                Image.asset(
                  'assets/icons/map.png',
                  height: 180,
                  width: double.infinity,
                  fit: BoxFit.cover,
                  color: Colors.black.withValues(alpha: 0.1),
                  colorBlendMode: BlendMode.srcOver,
                ),
                Container(
                  height: 180,
                  alignment: Alignment.center,
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [
                        Colors.black.withValues(alpha: 0.15),
                        Colors.black.withValues(alpha: 0.55),
                      ],
                      begin: Alignment.topCenter,
                      end: Alignment.bottomCenter,
                    ),
                  ),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: const [
                      Icon(
                        LucideIcons.mapPin,
                        color: Color(0xFFE9D7C2),
                        size: 42,
                      ),
                      SizedBox(height: 10),
                      Text(
                        '31 rue Abdessalam Aamir\nCasablanca, Maroc',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 18),
          const Text(
            'Consultez l’itinéraire et estimez votre trajet vers le salon.',
            style: TextStyle(color: Colors.white70, fontSize: 13, height: 1.4),
          ),
          const SizedBox(height: 18),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: () => _openGoogleMaps(context),
              icon: const Icon(LucideIcons.navigation),
              label: const Text('Ouvrir dans Google Maps'),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFFE9D7C2),
                foregroundColor: Colors.black,
                padding: const EdgeInsets.symmetric(vertical: 16),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(20),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFavoritesContent(BuildContext context) {
    return Container(
      key: const ValueKey('loyalty_favorites'),
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        color: const Color(0xFFE9D7C2).withOpacity(0.08),
        borderRadius: BorderRadius.circular(26),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.35),
            blurRadius: 24,
            offset: const Offset(0, 16),
          ),
        ],
        border: Border.all(color: const Color(0xFFE9D7C2).withOpacity(0.35)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Retrouvez vos favoris',
            style: TextStyle(
              color: Colors.white,
              fontSize: 18,
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: 12),
          Text(
            'Connectez-vous pour sauvegarder vos soins et produits préférés, et les retrouver en un clin d’œil.',
            style: TextStyle(
              color: Colors.white.withOpacity(0.75),
              fontSize: 13,
              height: 1.5,
            ),
          ),
          const SizedBox(height: 18),
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: const Color(0xFF181818),
              borderRadius: BorderRadius.circular(20),
              border: Border.all(
                color: const Color(0xFFE9D7C2).withOpacity(0.28),
              ),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFE9D7C2).withOpacity(0.25),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: const Icon(
                    LucideIcons.heart,
                    color: Color(0xFF2B2115),
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Synchronisez vos coups de cœur',
                        style: TextStyle(
                          fontWeight: FontWeight.w700,
                          fontSize: 15,
                          color: Colors.white,
                        ),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        'Gardez vos services favoris et bénéficiez de recommandations personnalisées.',
                        style: TextStyle(
                          fontSize: 13,
                          color: Colors.white.withOpacity(0.75),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 18),
          SizedBox(
            width: double.infinity,
            child: OutlinedButton(
              onPressed: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const OfflineProfileScreen()),
              ),
              style: OutlinedButton.styleFrom(
                side: const BorderSide(color: Color(0xFFE9D7C2)),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 16),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(20),
                ),
              ),
              child: const Text(
                'Se connecter pour voir mes favoris',
                style: TextStyle(fontWeight: FontWeight.w600, fontSize: 14),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEarnPointsContent() {
    final tips = [
      'Réservez vos soins via l’application pour cumuler automatiquement des points.',
      'Offrez des cartes cadeaux à vos proches et gagnez un bonus fidélité.',
      'Participez aux événements Bold Beauty Lounge pour recevoir des points exclusifs.',
      'Invitez un proche à découvrir le salon : vous êtes tous les deux récompensés.',
    ];

    return Container(
      key: const ValueKey('loyalty_earn'),
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        color: const Color(0xFFE9D7C2).withOpacity(0.08),
        borderRadius: BorderRadius.circular(26),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.35),
            blurRadius: 24,
            offset: const Offset(0, 16),
          ),
        ],
        border: Border.all(color: const Color(0xFFE9D7C2).withOpacity(0.35)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Comment gagner des points ?',
            style: TextStyle(
              color: Colors.white,
              fontSize: 18,
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: 14),
          ...tips.map(
            (tip) => Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    margin: const EdgeInsets.only(top: 4),
                    width: 8,
                    height: 8,
                    decoration: const BoxDecoration(
                      shape: BoxShape.circle,
                      color: Color(0xFFE9D7C2),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      tip,
                      style: TextStyle(
                        color: Colors.white.withOpacity(0.78),
                        fontSize: 13,
                        height: 1.5,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 12),
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: const Color(0xFF181818),
              borderRadius: BorderRadius.circular(20),
              border: Border.all(
                color: const Color(0xFFE9D7C2).withOpacity(0.28),
              ),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFE9D7C2).withOpacity(0.25),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: const Icon(
                    LucideIcons.badgePercent,
                    color: Color(0xFF2B2115),
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Statut Bold Élite',
                        style: TextStyle(
                          fontWeight: FontWeight.w700,
                          fontSize: 15,
                          color: Colors.white,
                        ),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        'Dès 500 pts cumulés, accédez à des offres privées, priorités de réservation et diagnostics personnalisés.',
                        style: TextStyle(
                          fontSize: 13,
                          color: Colors.white.withOpacity(0.78),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHistoryContent(BuildContext context) {
    final history = [
      {
        'service': 'Head Spa Sensual',
        'date': '12 octobre 2025',
        'status': 'Terminé',
      },
      {
        'service': 'Hammam Sensation Duo',
        'date': '28 septembre 2025',
        'status': 'Terminé',
      },
      {
        'service': 'Coiffure Signature',
        'date': '31 août 2025',
        'status': 'Annulé',
      },
    ];

    return Container(
      key: const ValueKey('loyalty_history'),
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        color: const Color(0xFFE9D7C2).withOpacity(0.08),
        borderRadius: BorderRadius.circular(26),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.35),
            blurRadius: 24,
            offset: const Offset(0, 16),
          ),
        ],
        border: Border.all(color: const Color(0xFFE9D7C2).withOpacity(0.35)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Vos derniers rendez-vous',
            style: TextStyle(
              color: Colors.white,
              fontSize: 18,
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: 16),
          ...history.map(
            (item) => Container(
              margin: const EdgeInsets.only(bottom: 12),
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
              decoration: BoxDecoration(
                color: const Color(0xFF181818),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(
                  color: const Color(0xFFE9D7C2).withOpacity(0.28),
                ),
              ),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: const Color(0xFFE9D7C2).withOpacity(0.25),
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: const Icon(
                      LucideIcons.calendarCheck2,
                      color: Color(0xFF2B2115),
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          item['service'] ?? '',
                          style: const TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.w700,
                            color: Colors.white,
                          ),
                        ),
                        const SizedBox(height: 6),
                        Text(
                          item['date'] ?? '',
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.white.withOpacity(0.75),
                          ),
                        ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 6,
                    ),
                    decoration: BoxDecoration(
                      color: const Color(0xFFE9D7C2),
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Text(
                      item['status'] ?? '',
                      style: const TextStyle(
                        fontWeight: FontWeight.w600,
                        fontSize: 12,
                        color: Colors.black,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            child: OutlinedButton(
              onPressed: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const OfflineBookingScreen()),
              ),
              style: OutlinedButton.styleFrom(
                side: const BorderSide(color: Color(0xFFE9D7C2)),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 16),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(20),
                ),
              ),
              child: const Text(
                'Voir toutes mes réservations',
                style: TextStyle(fontWeight: FontWeight.w600, fontSize: 14),
              ),
            ),
          ),
        ],
      ),
    );
  }

  void _showAccountPrompt(BuildContext context) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.black87,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
      ),
      builder: (sheetContext) {
        return Padding(
          padding: const EdgeInsets.fromLTRB(24, 28, 24, 32),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 46,
                  height: 4,
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.18),
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
              ),
              const SizedBox(height: 24),
              const Text(
                'Créez votre compte pour réserver ce pack',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 18,
                  fontWeight: FontWeight.w700,
                  letterSpacing: 0.4,
                ),
              ),
              const SizedBox(height: 12),
              const Text(
                'Accédez à vos avantages fidélité, suivez vos réservations et gardez vos packs préférés à portée de main.',
                style: TextStyle(
                  color: Colors.white70,
                  fontSize: 14,
                  height: 1.5,
                ),
              ),
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () {
                    Navigator.pop(sheetContext);
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const SignUpScreen()),
                    );
                  },
                  style: ElevatedButton.styleFrom(
                    elevation: 0,
                    backgroundColor: const Color(0xFFE9D7C2),
                    foregroundColor: Colors.black,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(18),
                    ),
                  ),
                  child: const Text(
                    'Créer un compte',
                    style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700),
                  ),
                ),
              ),
              const SizedBox(height: 12),
              SizedBox(
                width: double.infinity,
                child: OutlinedButton(
                  onPressed: () {
                    Navigator.pop(sheetContext);
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const LoginScreen()),
                    );
                  },
                  style: OutlinedButton.styleFrom(
                    foregroundColor: Colors.white,
                    side: BorderSide(
                      color: Colors.white.withValues(alpha: 0.4),
                    ),
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(18),
                    ),
                  ),
                  child: const Text(
                    'J’ai déjà un compte',
                    style: TextStyle(fontSize: 15, fontWeight: FontWeight.w600),
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  // Méthode pour ouvrir Google Maps
  Future<void> _openGoogleMaps(BuildContext context) async {
    const String googleMapsUrl = 'https://maps.app.goo.gl/BW1d99JkP1QK29W17';
    const String fallbackUrl =
        'https://www.google.com/maps/place/2+rez-de-chaussée,+31+rue+Abdessalam+Aamir,+Casablanca+20540,+Morocco';

    try {
      final Uri url = Uri.parse(googleMapsUrl);
      if (await canLaunchUrl(url)) {
        await launchUrl(url, mode: LaunchMode.externalApplication);
      } else {
        // Fallback vers l'URL de recherche Google Maps
        final Uri fallbackUri = Uri.parse(fallbackUrl);
        if (await canLaunchUrl(fallbackUri)) {
          await launchUrl(fallbackUri, mode: LaunchMode.externalApplication);
        }
      }
    } catch (e) {
      print('Erreur lors de l\'ouverture de Google Maps: $e');
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Impossible d\'ouvrir Google Maps'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  @override
  void initState() {
    super.initState();
    _specialistPageController = PageController(viewportFraction: 0.88);
    _packPageController = PageController(viewportFraction: 0.88);
    Future.delayed(const Duration(milliseconds: 150), () {
      if (mounted) {
        setState(() => _animateSections = true);
      }
    });
  }

  @override
  void dispose() {
    _specialistPageController.dispose();
    _packPageController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: ListView(
          physics: const BouncingScrollPhysics(),
          padding: EdgeInsets.zero,
          children: [
            _buildHeader(context),
            _buildBalanceSection(),
            _buildActionButtons(),
            _buildSearchBarSection(),
            _buildCategoriesSection(),
            _buildComboPacksSection(context),
            _buildFAQSection(),
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

  Widget _buildHeader(BuildContext context) {
    return Container(
      color: Colors.black,
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 20),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          _buildHeaderIconButton(
            icon: LucideIcons.userCircle2,
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (context) => const NewLoginScreen()),
              );
            },
          ),
          const Spacer(),
          const Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                'BOLD',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 28,
                  fontWeight: FontWeight.bold,
                  letterSpacing: 1.2,
                ),
              ),
              Text(
                'BEAUTY LOUNGE',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                  letterSpacing: 0.5,
                ),
              ),
            ],
          ),
          const Spacer(),
          _buildHeaderIconButton(
            icon: LucideIcons.bellRing,
            onTap: () {
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('Notifications à venir'),
                  duration: Duration(seconds: 1),
                ),
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildHeaderIconButton({required IconData icon, VoidCallback? onTap}) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: 44,
        height: 44,
        decoration: BoxDecoration(
          color: Colors.transparent,
          shape: BoxShape.circle,
          border: Border.all(
            color: Colors.white.withValues(alpha: 0.3),
            width: 1.5,
          ),
        ),
        child: Icon(icon, color: Colors.white, size: 22),
      ),
    );
  }

  Widget _buildBalanceSection() {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 20),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Votre solde',
                  style: TextStyle(
                    color: Colors.grey,
                    fontSize: 14,
                    fontWeight: FontWeight.w500,
                  ),
                ),
                const SizedBox(height: 4),
                const Text(
                  '0 Bold Coins',
                  style: TextStyle(
                    color: Colors.black,
                    fontSize: 28,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ],
            ),
          ),
          Container(
            width: 48,
            height: 48,
            decoration: BoxDecoration(
              color: Colors.grey[200],
              borderRadius: BorderRadius.circular(8),
            ),
            child: const Icon(
              LucideIcons.qrCode,
              color: Colors.black87,
              size: 24,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildActionButtons() {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          _buildActionButton('Payer', LucideIcons.wallet),
          _buildActionButton('Gagner', LucideIcons.coins),
          _buildActionButton('Transfert', LucideIcons.arrowLeftRight),
          _buildActionButton('Histoire', LucideIcons.clock),
        ],
      ),
    );
  }

  Widget _buildActionButton(String label, IconData icon) {
    return Expanded(
      child: GestureDetector(
        onTap: () {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('$label - Fonctionnalité à venir'),
              duration: const Duration(seconds: 1),
            ),
          );
        },
        child: Container(
          margin: const EdgeInsets.symmetric(horizontal: 4),
          padding: const EdgeInsets.symmetric(vertical: 12),
          decoration: BoxDecoration(
            color: Colors.black,
            borderRadius: BorderRadius.circular(12),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(icon, color: Colors.white, size: 24),
              const SizedBox(height: 6),
              Text(
                label,
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSearchBarSection() {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 20),
      child: _buildSearchBar(),
    );
  }

  Widget _buildLoyaltySection(BuildContext context) {
    return Container(
      margin: const EdgeInsets.fromLTRB(20, 20, 20, 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Padding(
            padding: EdgeInsets.only(left: 4, bottom: 16),
            child: Text(
              'Nos Avantages et Fidélité',
              style: TextStyle(
                color: Colors.white,
                fontSize: 24,
                fontWeight: FontWeight.bold,
                letterSpacing: 0.5,
              ),
            ),
          ),
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: List.generate(_loyaltyTabs.length, (index) {
                final isSelected = _selectedLoyaltyTab == index;
                return Padding(
                  padding: const EdgeInsets.only(right: 8),
                  child: ChoiceChip(
                    label: Text(_loyaltyTabs[index]),
                    selected: isSelected,
                    onSelected: (value) {
                      if (value) {
                        setState(() => _selectedLoyaltyTab = index);
                      }
                    },
                    selectedColor: const Color(0xFFE9D7C2),
                    backgroundColor: const Color(0xFFE9D7C2).withOpacity(0.15),
                    labelStyle: TextStyle(
                      color: isSelected ? Colors.black : Colors.white,
                      fontWeight: FontWeight.w600,
                    ),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(20),
                      side: BorderSide(
                        color: isSelected
                            ? Colors.transparent
                            : const Color(0xFFE9D7C2).withOpacity(0.25),
                      ),
                    ),
                  ),
                );
              }),
            ),
          ),
          const SizedBox(height: 20),
          AnimatedSwitcher(
            duration: const Duration(milliseconds: 250),
            child: _buildLoyaltyContent(
              context,
              _loyaltyTabs[_selectedLoyaltyTab],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLoyaltyCard(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.black.withValues(alpha: 0.35),
        borderRadius: BorderRadius.circular(26),
        border: Border.all(color: Colors.white.withValues(alpha: 0.12)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.35),
            blurRadius: 18,
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Votre solde fidélité',
                      style: TextStyle(
                        color: Colors.white70,
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                        letterSpacing: 0.2,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: const [
                        Text(
                          '0 pts',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 26,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        SizedBox(width: 8),
                        Icon(
                          LucideIcons.badgeCheck,
                          color: Color(0xFFE9D7C2),
                          size: 20,
                        ),
                      ],
                    ),
                    const SizedBox(height: 6),
                    const Text(
                      'Payez ou offrez des services avec vos points.',
                      style: TextStyle(
                        color: Colors.white60,
                        fontSize: 12,
                        height: 1.4,
                      ),
                    ),
                  ],
                ),
              ),
              _buildHeaderIconButton(
                icon: LucideIcons.qrCode,
                onTap: () {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('QR Code fidélité à venir'),
                      duration: Duration(seconds: 1),
                    ),
                  );
                },
              ),
            ],
          ),
          const SizedBox(height: 18),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.06),
              borderRadius: BorderRadius.circular(18),
              border: Border.all(color: Colors.white.withValues(alpha: 0.1)),
            ),
            child: const Text(
              'Profitez de vos points pour régler vos rituels ou les offrir à vos proches.',
              style: TextStyle(
                color: Colors.white70,
                fontSize: 13,
                height: 1.4,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSearchBar() {
    return Row(
      children: [
        Expanded(
          child: Container(
            decoration: BoxDecoration(
              color: Colors.grey[100],
              borderRadius: BorderRadius.circular(26),
            ),
            child: TextField(
              controller: _searchController,
              style: const TextStyle(color: Colors.black87),
              decoration: InputDecoration(
                hintText: 'Rechercher un soin...',
                hintStyle: TextStyle(
                  color: Colors.black.withValues(alpha: 0.35),
                ),
                prefixIcon: const Padding(
                  padding: EdgeInsets.symmetric(horizontal: 16),
                  child: Icon(
                    LucideIcons.search,
                    color: Colors.black54,
                    size: 22,
                  ),
                ),
                border: InputBorder.none,
                contentPadding: const EdgeInsets.symmetric(
                  horizontal: 20,
                  vertical: 16,
                ),
              ),
              onSubmitted: (value) {
                if (value.trim().isNotEmpty) {
                  _performSearch(value.trim());
                }
              },
            ),
          ),
        ),
        const SizedBox(width: 12),
        Container(
          width: 52,
          height: 52,
          decoration: BoxDecoration(
            color: const Color(0xFFE9D7C2),
            borderRadius: BorderRadius.circular(26),
          ),
          child: const Icon(LucideIcons.heart, color: Colors.black, size: 24),
        ),
      ],
    );
  }

  Widget _buildCategoriesSection() {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.symmetric(horizontal: 20.0, vertical: 20.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Text(
                'Nos Catégories',
                style: TextStyle(
                  color: Colors.black,
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                  letterSpacing: 0.5,
                  fontFamily: 'Poppins',
                ),
              ),
              const Spacer(),
              Icon(LucideIcons.chevronRight, color: Colors.grey[600], size: 20),
            ],
          ),
          const SizedBox(height: 20),
          SizedBox(
            height: 220,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              itemCount: _services.length,
              itemBuilder: (context, index) {
                return Padding(
                  padding: EdgeInsets.only(
                    right: index < _services.length - 1 ? 16 : 0,
                  ),
                  child: _buildCategoryCard(context, _services[index]),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildComboPacksSection(BuildContext context) {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.symmetric(horizontal: 20.0, vertical: 24.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Packs Combinés',
            textAlign: TextAlign.center,
            style: TextStyle(
              color: Colors.black,
              fontSize: 24,
              fontWeight: FontWeight.bold,
              letterSpacing: 0.5,
              fontFamily: 'Poppins',
            ),
          ),
          const SizedBox(height: 20),
          SizedBox(
            height: 500,
            child: Stack(
              children: [
                PageView.builder(
                  controller: _packPageController,
                  physics: const BouncingScrollPhysics(),
                  itemCount: _comboPacks.length,
                  itemBuilder: (context, index) {
                    final pack = _comboPacks[index];
                    return _buildPackCard(context, pack);
                  },
                ),
                Positioned(
                  left: 0,
                  top: 0,
                  bottom: 0,
                  child: Center(
                    child: GestureDetector(
                      onTap: () {
                        if (_packPageController.hasClients) {
                          _packPageController.previousPage(
                            duration: const Duration(milliseconds: 300),
                            curve: Curves.easeInOut,
                          );
                        }
                      },
                      child: Container(
                        width: 40,
                        height: 40,
                        decoration: BoxDecoration(
                          color: Colors.grey[300],
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(
                          LucideIcons.chevronLeft,
                          color: Colors.black87,
                        ),
                      ),
                    ),
                  ),
                ),
                Positioned(
                  right: 0,
                  top: 0,
                  bottom: 0,
                  child: Center(
                    child: GestureDetector(
                      onTap: () {
                        if (_packPageController.hasClients) {
                          _packPageController.nextPage(
                            duration: const Duration(milliseconds: 300),
                            curve: Curves.easeInOut,
                          );
                        }
                      },
                      child: Container(
                        width: 40,
                        height: 40,
                        decoration: BoxDecoration(
                          color: Colors.grey[300],
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(
                          LucideIcons.chevronRight,
                          color: Colors.black87,
                        ),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPackCard(BuildContext context, Map<String, dynamic> pack) {
    final List<String> details = (pack['details'] as List<dynamic>)
        .cast<String>();

    return GestureDetector(
      onTap: () => _handlePackTap(context),
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        padding: const EdgeInsets.fromLTRB(28, 32, 28, 24),
        decoration: BoxDecoration(
          color: Colors.black,
          borderRadius: BorderRadius.circular(20),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            Text(
              pack['name'] as String,
              textAlign: TextAlign.center,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 22,
                fontWeight: FontWeight.w700,
                letterSpacing: 0.3,
              ),
            ),
            const SizedBox(height: 10),
            Text(
              pack['tagline'] as String,
              textAlign: TextAlign.center,
              style: const TextStyle(
                color: Colors.white70,
                fontSize: 14,
                height: 1.4,
              ),
            ),
            const SizedBox(height: 18),
            Text(
              pack['price'] as String,
              textAlign: TextAlign.center,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 44,
                fontWeight: FontWeight.w800,
                letterSpacing: 0.6,
              ),
            ),
            const SizedBox(height: 12),
            Expanded(
              child: ListView.separated(
                padding: EdgeInsets.zero,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: details.length,
                separatorBuilder: (_, __) => const SizedBox(height: 6),
                itemBuilder: (context, index) {
                  return Text(
                    details[index],
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                      color: Colors.white70,
                      fontSize: 14,
                      height: 1.3,
                    ),
                  );
                },
              ),
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () => _handlePackTap(context),
                style: ElevatedButton.styleFrom(
                  elevation: 0,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  backgroundColor: Colors.white,
                  foregroundColor: Colors.black,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16),
                  ),
                ),
                child: const Text(
                  'Réservez Maintenant',
                  style: TextStyle(fontWeight: FontWeight.w700, fontSize: 15),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPageIndicator(PageController controller, int itemCount) {
    return SizedBox(
      height: 8,
      child: AnimatedBuilder(
        animation: controller,
        builder: (context, child) {
          double currentPage = 0;
          if (controller.hasClients && controller.position.haveDimensions) {
            currentPage = controller.page ?? controller.initialPage.toDouble();
          }
          return Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: List.generate(itemCount, (index) {
              final isActive = (currentPage - index).abs() < 0.5;
              return AnimatedContainer(
                duration: const Duration(milliseconds: 250),
                margin: const EdgeInsets.symmetric(horizontal: 4),
                height: 6,
                width: isActive ? 24 : 10,
                decoration: BoxDecoration(
                  color: isActive
                      ? const Color(0xFFE9D7C2)
                      : const Color(0xFFD7C9B4),
                  borderRadius: BorderRadius.circular(12),
                ),
              );
            }),
          );
        },
      ),
    );
  }

  Widget _buildFAQSection() {
    final List<Map<String, String>> faqItems = [
      {'question': "Qu'est-ce que Bold Beauty Lounge ?"},
      {'question': 'Comment fonctionne le programme de fidélité Bold ?'},
      {
        'question':
            'Comment puis-je reprogrammer ou modifier mon rendez-vous ?',
      },
      {'question': "Quels sont les horaires d'ouverture de Bold ?"},
      {'question': 'Quels modes de paiement acceptez-vous ?'},
    ];

    return Container(
      color: Colors.white,
      padding: const EdgeInsets.symmetric(horizontal: 20.0, vertical: 24.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Questions fréquentes',
            textAlign: TextAlign.center,
            style: TextStyle(
              color: Colors.black,
              fontSize: 24,
              fontWeight: FontWeight.bold,
              letterSpacing: 0.5,
            ),
          ),
          const SizedBox(height: 20),
          ...faqItems.map(
            (faq) => Container(
              margin: const EdgeInsets.only(bottom: 12),
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.grey[300]!),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: Text(
                      faq['question']!,
                      style: const TextStyle(
                        color: Colors.black,
                        fontSize: 15,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ),
                  const Icon(
                    LucideIcons.chevronDown,
                    color: Colors.black87,
                    size: 20,
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPourElleBanner(BuildContext context) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 20.0, vertical: 20.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Padding(
            padding: EdgeInsets.only(left: 4.0, bottom: 20.0),
            child: Text(
              'Pour Elle',
              style: TextStyle(
                color: Colors.white,
                fontSize: 24,
                fontWeight: FontWeight.bold,
                letterSpacing: 0.5,
              ),
            ),
          ),
          Container(
            height: 220,
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(20),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.15),
                  blurRadius: 15,
                  offset: const Offset(0, 8),
                ),
              ],
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(20),
              child: Stack(
                children: [
                  // Image de fond
                  Image.asset(
                    'assets/boldbeautylounge.jpg',
                    width: double.infinity,
                    height: double.infinity,
                    fit: BoxFit.cover,
                    errorBuilder: (context, error, stackTrace) {
                      return Container(
                        color: Colors.grey[300],
                        child: const Center(
                          child: Icon(Icons.spa, color: Colors.grey, size: 80),
                        ),
                      );
                    },
                  ),
                  // Overlay sombre
                  Container(
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
                        colors: [
                          Colors.black.withValues(alpha: 0.3),
                          Colors.black.withValues(alpha: 0.7),
                        ],
                      ),
                    ),
                  ),
                  // Contenu
                  Positioned(
                    bottom: 20,
                    left: 20,
                    right: 20,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Bold Beauty Lounge',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 24,
                            fontWeight: FontWeight.bold,
                            fontFamily: 'Poppins',
                          ),
                        ),
                        const SizedBox(height: 8),
                        const Text(
                          'Salon de beauté',
                          style: TextStyle(color: Colors.white70, fontSize: 16),
                        ),
                        const SizedBox(height: 12),
                        Row(
                          children: [
                            GestureDetector(
                              onTap: () {
                                // Ouvrir le lien Google Reviews
                              },
                              child: const Text(
                                '★ 4.9 (127)',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontSize: 14,
                                  fontWeight: FontWeight.bold,
                                  decoration: TextDecoration.underline,
                                ),
                              ),
                            ),
                            const SizedBox(width: 12),
                            const Text(
                              'Casablanca, Maroc',
                              style: TextStyle(
                                color: Colors.white70,
                                fontSize: 14,
                              ),
                            ),
                            const Spacer(),
                            GestureDetector(
                              onTap: () {
                                Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (context) =>
                                        const BoldBeautyDetailPage(),
                                  ),
                                );
                              },
                              child: Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 16,
                                  vertical: 8,
                                ),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFE9D7C2),
                                  borderRadius: BorderRadius.circular(20),
                                ),
                                child: const Text(
                                  'Voir plus',
                                  style: TextStyle(
                                    color: Colors.black,
                                    fontSize: 12,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCategoryCard(
    BuildContext context,
    Map<String, dynamic> service,
  ) {
    final String name = service['name'] as String;
    final String imagePath = service['image'] as String;
    final String category = _mapToPricingCategory(
      service['category'] as String? ?? name,
    );

    return GestureDetector(
      onTap: () => _openCategory(context, category),
      child: Container(
        width: 180,
        height: 220,
        margin: const EdgeInsets.symmetric(horizontal: 4),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(26),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.12),
              blurRadius: 16,
              offset: const Offset(0, 12),
            ),
          ],
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(26),
          child: Stack(
            children: [
              Positioned.fill(
                child: Image.asset(
                  imagePath,
                  fit: BoxFit.cover,
                  errorBuilder: (context, error, stackTrace) {
                    return Container(
                      color: Colors.black,
                      alignment: Alignment.center,
                      child: Icon(
                        Icons.image_outlined,
                        color: Colors.white.withValues(alpha: 0.4),
                        size: 40,
                      ),
                    );
                  },
                ),
              ),
              Positioned.fill(
                child: Container(
                  decoration: const BoxDecoration(
                    gradient: LinearGradient(
                      begin: Alignment.topCenter,
                      end: Alignment.bottomCenter,
                      colors: [Colors.transparent, Color(0xCC000000)],
                    ),
                  ),
                ),
              ),
              Positioned(
                left: 18,
                right: 18,
                bottom: 20,
                child: Text(
                  name,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 20,
                    fontWeight: FontWeight.w700,
                    letterSpacing: 0.3,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _openCategory(BuildContext context, String category) {
    // Rediriger vers la sélection de services pour cette catégorie
    Navigator.push(
      context,
      MaterialPageRoute(builder: (context) => const ServiceSelectionScreen()),
    );
  }

  void _performSearch(String query) {
    // Rediriger vers la page de sélection des services pour effectuer la recherche
    Navigator.push(
      context,
      MaterialPageRoute(builder: (context) => const ServiceSelectionScreen()),
    );
    // La recherche sera effectuée dans ServiceSelectionScreen
  }

  String _mapToPricingCategory(String category) {
    switch (category.toLowerCase()) {
      case 'coiffure':
        return 'Coiffure';
      case 'onglerie':
        return 'Onglerie';
      case 'hammam':
        return 'Hammam';
      case 'massage & spa':
      case 'massages':
        return 'Massage & Spa';
      case 'head spa':
        return 'Head Spa';
      case 'soins esthétiques':
      case 'soins':
        return 'Soins Esthétiques';
      default:
        return category;
    }
  }

  Widget _buildTeamSection() {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 20.0, vertical: 20.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Padding(
            padding: EdgeInsets.only(left: 4.0, bottom: 20.0),
            child: Text(
              'Nos Spécialistes',
              style: TextStyle(
                color: Colors.white,
                fontSize: 24,
                fontWeight: FontWeight.bold,
                letterSpacing: 0.5,
              ),
            ),
          ),
          SizedBox(
            height: 420,
            child: PageView.builder(
              controller: _specialistPageController,
              physics: const BouncingScrollPhysics(),
              itemCount: _specialists.length,
              itemBuilder: (context, index) {
                final specialist = _specialists[index];
                return AnimatedBuilder(
                  animation: _specialistPageController,
                  builder: (context, child) {
                    double value = 1.0;
                    if (_specialistPageController.hasClients &&
                        _specialistPageController.position.haveDimensions) {
                      value =
                          (1 -
                                  ((_specialistPageController.page ??
                                                  _specialistPageController
                                                      .initialPage) -
                                              index)
                                          .abs() *
                                      0.12)
                              .clamp(0.9, 1.0);
                    }
                    return Center(
                      child: Transform.scale(
                        scale: Curves.easeOut.transform(value),
                        child: child,
                      ),
                    );
                  },
                  child: _buildSpecialistCard(specialist),
                );
              },
            ),
          ),
          const SizedBox(height: 16),
          _buildPageIndicator(_specialistPageController, _specialists.length),
        ],
      ),
    );
  }

  Widget _buildSpecialistCard(Map<String, dynamic> specialist) {
    final String imagePath = specialist['image'] as String;
    final String name = specialist['name'] as String;
    final String specialty = specialist['title'] as String;
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 8, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(30),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.08),
            blurRadius: 24,
            offset: const Offset(0, 18),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
            child: ClipRRect(
              borderRadius: const BorderRadius.only(
                topLeft: Radius.circular(30),
                topRight: Radius.circular(30),
              ),
              child: Image.asset(
                imagePath,
                width: double.infinity,
                fit: BoxFit.cover,
                alignment: Alignment.topCenter,
                errorBuilder: (context, error, stackTrace) {
                  return Container(
                    color: const Color(0xFFEDEDED),
                    alignment: Alignment.center,
                    child: Icon(
                      Icons.person_outline,
                      color: Colors.black.withValues(alpha: 0.35),
                      size: 52,
                    ),
                  );
                },
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(22, 18, 22, 20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  name,
                  style: const TextStyle(
                    color: Colors.black,
                    fontSize: 20,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  specialty,
                  style: TextStyle(
                    color: Colors.black.withValues(alpha: 0.6),
                    fontSize: 14,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildQuickActions(BuildContext context) {
    final actions = [
      {
        'title': 'Prendre RDV',
        'subtitle': 'Réservez votre prochain rituel',
        'icon': LucideIcons.calendarPlus,
        'onTap': () => Navigator.push(
          context,
          MaterialPageRoute(builder: (context) => const OfflineBookingScreen()),
        ),
      },
      {
        'title': 'Nos Tarifs',
        'subtitle': 'Consultez la carte des prestations',
        'icon': LucideIcons.receipt,
        'onTap': () => Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => const DetailedPricingScreen(),
          ),
        ),
      },
      {
        'title': 'Localisation',
        'subtitle': 'Trouvez-nous en un tap',
        'icon': LucideIcons.mapPin,
        'onTap': () => _openGoogleMaps(context),
      },
      {
        'title': 'Contact',
        'subtitle': 'Discutez avec notre équipe',
        'icon': LucideIcons.messageCircle,
        'onTap': () => Navigator.push(
          context,
          MaterialPageRoute(builder: (context) => const BoldBeautyDetailPage()),
        ),
      },
    ];

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 20.0, vertical: 20.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Padding(
            padding: EdgeInsets.only(left: 4.0, bottom: 20.0),
            child: Text(
              'Actions rapides',
              style: TextStyle(
                color: Colors.white,
                fontSize: 24,
                fontWeight: FontWeight.bold,
                letterSpacing: 0.5,
              ),
            ),
          ),
          LayoutBuilder(
            builder: (context, constraints) {
              final double itemWidth = (constraints.maxWidth - 24) / 2;
              return Wrap(
                spacing: 24,
                runSpacing: 20,
                children: List.generate(actions.length, (index) {
                  final action = actions[index];
                  return SizedBox(
                    width: itemWidth,
                    child: AnimatedOpacity(
                      opacity: _animateSections ? 1 : 0,
                      duration: Duration(milliseconds: 450 + (index * 120)),
                      curve: Curves.easeOut,
                      child: AnimatedSlide(
                        offset: _animateSections
                            ? Offset.zero
                            : const Offset(0, 0.12),
                        duration: Duration(milliseconds: 450 + (index * 120)),
                        curve: Curves.easeOutCubic,
                        child: _buildQuickActionCard(
                          title: action['title'] as String,
                          subtitle: action['subtitle'] as String,
                          icon: action['icon'] as IconData,
                          onTap: action['onTap'] as VoidCallback,
                        ),
                      ),
                    ),
                  );
                }),
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildQuickActionCard({
    required String title,
    required String subtitle,
    required IconData icon,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.fromLTRB(22, 24, 22, 24),
        decoration: BoxDecoration(
          color: const Color(0xFF181818),
          borderRadius: BorderRadius.circular(24),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.35),
              blurRadius: 24,
              offset: const Offset(0, 14),
            ),
          ],
          border: Border.all(color: const Color(0xFFE9D7C2).withOpacity(0.28)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 52,
              height: 52,
              decoration: BoxDecoration(
                color: const Color(0xFFE9D7C2).withOpacity(0.25),
                borderRadius: BorderRadius.circular(18),
              ),
              child: Icon(icon, color: const Color(0xFFE9D7C2), size: 26),
            ),
            const SizedBox(height: 18),
            Text(
              title,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 18,
                fontWeight: FontWeight.w700,
                letterSpacing: 0.3,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              subtitle,
              style: TextStyle(
                color: Colors.white.withOpacity(0.75),
                fontSize: 13,
                height: 1.4,
              ),
            ),
            const Spacer(),
            Row(
              children: const [
                Text(
                  'Ouvrir',
                  style: TextStyle(
                    color: Color(0xFFE9D7C2),
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                    letterSpacing: 0.2,
                  ),
                ),
                SizedBox(width: 8),
                Icon(
                  LucideIcons.arrowRight,
                  color: Color(0xFFE9D7C2),
                  size: 18,
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
