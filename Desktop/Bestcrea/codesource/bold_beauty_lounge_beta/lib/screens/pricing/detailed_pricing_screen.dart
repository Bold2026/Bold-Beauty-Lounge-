import 'package:flutter/material.dart';
import '../booking/offline_booking_screen.dart';

class DetailedPricingScreen extends StatefulWidget {
  final String? initialCategory;

  const DetailedPricingScreen({super.key, this.initialCategory});

  @override
  State<DetailedPricingScreen> createState() => _DetailedPricingScreenState();
}

class _DetailedPricingScreenState extends State<DetailedPricingScreen> {
  static const Color _accentColor = Color(0xFFE9D7C2);

  final Map<String, String> _categoryImages = const {
    'Coiffure': 'assets/services/coiffure.jpg',
    'Onglerie': 'assets/services/onglerie.jpg',
    'Hammam': 'assets/services/hammam.jpg',
    'Massage & Spa': 'assets/services/massages.jpg',
    'Head Spa': 'assets/services/head spa.jpg',
    'Soins Esthétiques': 'assets/services/soins.jpg',
  };

  final Map<String, List<Map<String, String>>> _categoryServices = {
    'Coiffure': [
      {'name': 'Hair Wash - Spécial Short', 'price': '70 DH'},
      {'name': 'Hair Wash - Spécial Medium', 'price': '80 DH'},
      {'name': 'Hair Wash - Spécial Long', 'price': '80 DH'},
      {'name': 'Brushing Simple Short', 'price': '60 DH'},
      {'name': 'Brushing Simple Medium', 'price': '100 DH'},
      {'name': 'Brushing Simple Long', 'price': '120 DH'},
      {'name': 'Brushing Wavy Short', 'price': '80 DH'},
      {'name': 'Brushing Wavy Medium', 'price': '120 DH'},
      {'name': 'Brushing Wavy Long', 'price': '150 DH'},
      {'name': '+STEAMPOD Short', 'price': '50 DH'},
      {'name': '+STEAMPOD Medium', 'price': '50 DH'},
      {'name': '+STEAMPOD Long', 'price': '50 DH'},
      {'name': 'Bold Updo Coiffure', 'price': 'À partir de 400 DH'},
      {'name': 'Racines avec ammoniaque Short', 'price': '250 DH'},
      {'name': 'Racines avec ammoniaque Medium', 'price': '350 DH'},
      {'name': 'Racines avec ammoniaque Long', 'price': '350 DH'},
      {'name': 'Racines sans ammoniaque Short', 'price': '300 DH'},
      {'name': 'Racines sans ammoniaque Medium', 'price': '400 DH'},
      {'name': 'Racines sans ammoniaque Long', 'price': '400 DH'},
      {
        'name': 'Coloration ammoniaque Short',
        'price': '500 DH',
        'description': 'Coloration totale avec ammoniaque',
      },
      {
        'name': 'Coloration ammoniaque Medium',
        'price': '600 DH',
        'description': 'Coloration totale avec ammoniaque',
      },
      {
        'name': 'Coloration ammoniaque Long',
        'price': '700 DH',
        'description': 'Coloration totale avec ammoniaque',
      },
      {'name': 'Coloration sans ammoniaque Short', 'price': '600 DH'},
      {'name': 'Coloration sans ammoniaque Medium', 'price': '700 DH'},
      {'name': 'Coloration sans ammoniaque Long', 'price': '800 DH'},
      {'name': 'Coupes Pointes Short', 'price': '150 DH'},
      {'name': 'Coupes Pointes Medium', 'price': '150 DH'},
      {'name': 'Coupes Pointes Long', 'price': '150 DH'},
      {'name': 'Coupes – Autres', 'price': 'À partir de 300 DH'},
      {'name': 'Soin Ampoule Short', 'price': '200 DH'},
      {'name': 'Soin Ampoule Medium', 'price': '200 DH'},
      {'name': 'Soin Ampoule Long', 'price': '200 DH'},
      {'name': 'Protein Treatment', 'price': 'À partir de 1000 DH'},
      {'name': 'Hair Extensions', 'price': 'Sur consultation'},
    ],
    'Onglerie': [
      {'name': 'Classic Manicure', 'price': '80 DH'},
      {'name': 'Classic Pédicure', 'price': '180 DH'},
      {'name': 'Sensual Spa Manicure', 'price': '180 DH'},
      {'name': 'Sensual Spa Pédicure', 'price': '250 DH'},
      {'name': 'BOLD Luxury Manicure', 'price': '280 DH'},
      {'name': 'BOLD Luxury Pédicure', 'price': '350 DH'},
      {'name': 'Vernis Permanent', 'price': '180 DH'},
      {'name': 'Pause Vernis normale', 'price': '50 DH'},
      {'name': "Extensions d'Ongles - Full Set", 'price': '500 DH'},
      {'name': 'Remplissage Extensions', 'price': '300 DH'},
      {'name': "Capsule vernis permanent", 'price': '280 DH'},
      {'name': 'Nail Art', 'price': 'Sur consultation'},
      {'name': "Dépose Vernis Permanent", 'price': '50 DH'},
      {'name': "Dépose Extensions d'Ongles", 'price': '100 DH'},
    ],
    'Hammam': [
      {'name': 'Savonnage Individuel', 'price': '150 DH'},
      {'name': 'Savonnage Double', 'price': '300 DH'},
      {'name': 'Hammam expérience Individuel', 'price': '280 DH'},
      {
        'name': 'Hammam expérience Double',
        'price': '400 DH',
        'description': '200 DH / pers.',
      },
      {'name': 'Hammam sensation Individuel', 'price': '380 DH'},
      {
        'name': 'Hammam sensation Double',
        'price': '600 DH',
        'description': '300 DH / pers.',
      },
      {'name': 'Hammam Royale Individuel', 'price': '480 DH'},
      {
        'name': 'Hammam Royale Double',
        'price': '800 DH',
        'description': '400 DH / pers.',
      },
    ],
    'Massage & Spa': [
      {'name': 'Massage Relaxant 1H', 'price': '300 DH'},
      {'name': 'Massage Relaxant 1H30', 'price': '450 DH'},
      {'name': 'Head & Shoulders – Express 15 min', 'price': '100 DH'},
      {'name': 'Head & Shoulders – Express 30 min', 'price': '150 DH'},
      {'name': 'Feet Massage – Express 15 min', 'price': '100 DH'},
      {'name': 'Feet Massage – Express 30 min', 'price': '150 DH'},
      {'name': 'Massage Amincissant (Cure) 10 sc', 'price': '2000 DH'},
      {'name': 'Massage Amincissant 45 min/sc', 'price': '250 DH'},
    ],
    'Head Spa': [
      {'name': 'Head Spa Bold Experience 30 min', 'price': '350 DH'},
      {'name': 'Head Spa Sensual 1H', 'price': '600 DH'},
      {'name': 'Head Spa Royal 1H30', 'price': '800 DH'},
    ],
    'Soins Esthétiques': [
      {'name': 'Épilation Complète', 'price': '400 DH'},
      {'name': 'Épilation Jambes', 'price': '180 DH'},
      {'name': 'Épilation Aisselles', 'price': '50 DH'},
      {'name': 'Épilation Duvet', 'price': '30 DH'},
      {'name': 'Épilation Menton', 'price': '30 DH'},
      {'name': 'Épilation Narines', 'price': '25 DH'},
      {'name': 'Épilation Sourcils', 'price': '50 DH'},
      {'name': 'Coloration Sourcils', 'price': '50 DH'},
      {'name': 'Coloration Sourcils – The Brow Code', 'price': '80 DH'},
      {'name': 'Épilation Lumière Pulsée 15 zones', 'price': '2500 DH'},
    ],
  };

  late String _selectedCategory;

  @override
  void initState() {
    super.initState();
    _selectedCategory = _resolveInitialCategory();
  }

  String _resolveInitialCategory() {
    if (widget.initialCategory != null &&
        _categoryServices.containsKey(widget.initialCategory)) {
      return widget.initialCategory!;
    }
    return _categoryServices.keys.first;
  }

  @override
  Widget build(BuildContext context) {
    final services = _categoryServices[_selectedCategory] ?? [];

    return Scaffold(
      backgroundColor: const Color(0xFFF9F7F3),
      appBar: AppBar(
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
        title: const Text(
          'Nos Tarifs',
          style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
        ),
        centerTitle: true,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Consultez nos tarifs par univers',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.w700,
                color: Colors.black87,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              'Touchez un service pour ouvrir la page de réservation détaillée.',
              style: TextStyle(
                fontSize: 13,
                color: Colors.black.withValues(alpha: 0.6),
              ),
            ),
            const SizedBox(height: 24),
            _buildCategorySelector(),
            const SizedBox(height: 24),
            ...services.map((service) => _buildServiceCard(context, service)),
            const SizedBox(height: 40),
          ],
        ),
      ),
    );
  }

  Widget _buildCategorySelector() {
    final categories = _categoryServices.keys.toList();
    return SizedBox(
      height: 48,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: categories.length,
        separatorBuilder: (_, __) => const SizedBox(width: 12),
        itemBuilder: (context, index) {
          final category = categories[index];
          final isSelected = _selectedCategory == category;
          return GestureDetector(
            onTap: () {
              setState(() {
                _selectedCategory = category;
              });
            },
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 200),
              padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 10),
              decoration: BoxDecoration(
                color: isSelected ? const Color(0xFFE9D7C2) : Colors.white,
                borderRadius: BorderRadius.circular(24),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(
                      alpha: isSelected ? 0.2 : 0.08,
                    ),
                    blurRadius: isSelected ? 18 : 12,
                    offset: const Offset(0, 6),
                  ),
                  if (isSelected)
                    BoxShadow(
                      color: Colors.white.withValues(alpha: 0.6),
                      blurRadius: 8,
                      offset: const Offset(-3, -3),
                    ),
                ],
                border: Border.all(
                  color:
                      isSelected ? Colors.transparent : const Color(0xFFE5DED0),
                ),
              ),
              child: Row(
                children: [
                  Icon(
                    Icons.circle,
                    size: 8,
                    color: isSelected ? Colors.black : const Color(0xFFE9D7C2),
                  ),
                  const SizedBox(width: 8),
                  Text(
                    category,
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                      color: isSelected ? Colors.black : Colors.black87,
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildServiceCard(BuildContext context, Map<String, String> service) {
    final imagePath =
        _categoryImages[_selectedCategory] ?? _categoryImages.values.first;
    final hasDescription = (service['description'] ?? '').isNotEmpty;

    return Padding(
      padding: const EdgeInsets.only(bottom: 20),
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(26),
        child: InkWell(
          borderRadius: BorderRadius.circular(26),
          onTap: () => _openBooking(context),
          child: Container(
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(26),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.08),
                  blurRadius: 18,
                  offset: const Offset(0, 12),
                ),
                BoxShadow(
                  color: Colors.white.withValues(alpha: 0.6),
                  blurRadius: 10,
                  offset: const Offset(-4, -4),
                ),
              ],
            ),
            child: Row(
              children: [
                ClipRRect(
                  borderRadius: const BorderRadius.only(
                    topLeft: Radius.circular(26),
                    bottomLeft: Radius.circular(26),
                  ),
                  child: Stack(
                    children: [
                      Image.asset(
                        imagePath,
                        width: 110,
                        height: 120,
                        fit: BoxFit.cover,
                        errorBuilder: (context, error, stackTrace) {
                          return Container(
                            width: 110,
                            height: 120,
                            color: const Color(0xFFF1E8D9),
                            child: const Icon(
                              Icons.image_not_supported,
                              color: Colors.grey,
                            ),
                          );
                        },
                      ),
                      Positioned(
                        right: 10,
                        top: 10,
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 4,
                          ),
                          decoration: BoxDecoration(
                            color: Colors.black.withValues(alpha: 0.35),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Text(
                            _selectedCategory,
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 10,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                Expanded(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 18,
                      vertical: 18,
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          service['name'] ?? '',
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w700,
                            color: Colors.black,
                          ),
                        ),
                        const SizedBox(height: 6),
                        Row(
                          children: [
                            Container(
                              width: 6,
                              height: 6,
                              decoration: BoxDecoration(
                                color: _accentColor,
                                borderRadius: BorderRadius.circular(3),
                              ),
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                hasDescription
                                    ? service['description']!
                                    : 'Offre spéciale Bold Beauty Lounge',
                                style: TextStyle(
                                  fontSize: 13,
                                  color: Colors.grey[600],
                                  height: 1.4,
                                ),
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.only(
                    right: 18,
                    top: 18,
                    bottom: 18,
                  ),
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical: 10,
                    ),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFFE9DFD0), Color(0xFFE9D7C2)],
                        begin: Alignment.centerLeft,
                        end: Alignment.centerRight,
                      ),
                      borderRadius: BorderRadius.circular(18),
                      border: Border.all(color: const Color(0xFFE9D7C2)),
                    ),
                    child: Text(
                      service['price'] ?? '',
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF2B2115),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  void _openBooking(BuildContext context) {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (context) => const OfflineBookingScreen()),
    );
  }
}
