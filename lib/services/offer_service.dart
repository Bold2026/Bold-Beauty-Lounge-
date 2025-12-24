import '../models/offer.dart';

class OfferService {
  static final OfferService _instance = OfferService._internal();
  factory OfferService() => _instance;
  OfferService._internal();

  final List<Offer> _offers = [
    Offer(
      id: '1',
      title: 'Package Beauté Complète',
      description:
          'Soins du visage, manucure et pédicure avec un rabais exceptionnel',
      imageUrl:
          'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?w=400',
      originalPrice: 200.0,
      discountedPrice: 120.0,
      discountPercentage: 40.0,
      category: 'Package',
      services: ['Soins du visage', 'Manucure', 'Pédicure'],
      validFrom: DateTime.now().subtract(const Duration(days: 5)),
      validTo: DateTime.now().add(const Duration(days: 25)),
      isActive: true,
      termsAndConditions:
          'Valable pour une personne. Réservation obligatoire. Non cumulable avec d\'autres offres.',
      promoCode: 'BEAUTE40',
      maxUses: 50,
      currentUses: 23,
      isNew: true,
      isPopular: true,
      rating: 4.8,
      reviewCount: 15,
    ),
    Offer(
      id: '2',
      title: 'Soins du Visage Premium',
      description:
          'Nettoyage en profondeur, masque hydratant et massage facial',
      imageUrl:
          'https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?w=400',
      originalPrice: 80.0,
      discountedPrice: 55.0,
      discountPercentage: 31.25,
      category: 'Soins du visage',
      services: ['Nettoyage', 'Masque hydratant', 'Massage facial'],
      validFrom: DateTime.now().subtract(const Duration(days: 2)),
      validTo: DateTime.now().add(const Duration(days: 18)),
      isActive: true,
      termsAndConditions:
          'Valable du lundi au vendredi. Réservation 24h à l\'avance.',
      promoCode: 'VISAGE31',
      maxUses: 30,
      currentUses: 12,
      isNew: false,
      isPopular: true,
      rating: 4.6,
      reviewCount: 8,
    ),
    Offer(
      id: '3',
      title: 'Manucure & Pédicure Express',
      description: 'Manucure française et pédicure avec vernis semi-permanent',
      imageUrl:
          'https://images.unsplash.com/photo-1516975080664-ed2fc6a32937?w=400',
      originalPrice: 60.0,
      discountedPrice: 40.0,
      discountPercentage: 33.33,
      category: 'Manucure',
      services: ['Manucure française', 'Pédicure', 'Vernis semi-permanent'],
      validFrom: DateTime.now().subtract(const Duration(days: 1)),
      validTo: DateTime.now().add(const Duration(days: 14)),
      isActive: true,
      termsAndConditions:
          'Durée: 1h30. Vernis inclus. Réservation recommandée.',
      promoCode: 'NAILS33',
      maxUses: 25,
      currentUses: 8,
      isNew: true,
      isPopular: false,
      rating: 4.7,
      reviewCount: 6,
    ),
    Offer(
      id: '4',
      title: 'Massage Relaxant',
      description: 'Massage du corps de 60 minutes avec huiles essentielles',
      imageUrl:
          'https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=400',
      originalPrice: 100.0,
      discountedPrice: 75.0,
      discountPercentage: 25.0,
      category: 'Massage',
      services: [
        'Massage du corps',
        'Huiles essentielles',
        'Musique relaxante'
      ],
      validFrom: DateTime.now().subtract(const Duration(days: 3)),
      validTo: DateTime.now().add(const Duration(days: 20)),
      isActive: true,
      termsAndConditions: 'Durée: 60 minutes. Disponible du mardi au samedi.',
      promoCode: 'MASSAGE25',
      maxUses: 20,
      currentUses: 5,
      isNew: false,
      isPopular: false,
      rating: 4.9,
      reviewCount: 12,
    ),
    Offer(
      id: '5',
      title: 'Épilage Laser - 3 Séances',
      description: 'Pack de 3 séances d\'épilation laser pour les jambes',
      imageUrl:
          'https://images.unsplash.com/photo-1594736797933-d0c0b0b0b0b0?w=400',
      originalPrice: 300.0,
      discountedPrice: 200.0,
      discountPercentage: 33.33,
      category: 'Épilage',
      services: ['Épilage laser jambes', '3 séances', 'Consultation incluse'],
      validFrom: DateTime.now().subtract(const Duration(days: 7)),
      validTo: DateTime.now().add(const Duration(days: 30)),
      isActive: true,
      termsAndConditions:
          'Valable 3 mois. Consultation obligatoire avant la première séance.',
      promoCode: 'LASER33',
      maxUses: 15,
      currentUses: 3,
      isNew: false,
      isPopular: true,
      rating: 4.5,
      reviewCount: 4,
    ),
    Offer(
      id: '6',
      title: 'Coiffure & Maquillage',
      description: 'Coupe, brushing et maquillage pour une occasion spéciale',
      imageUrl:
          'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?w=400',
      originalPrice: 90.0,
      discountedPrice: 65.0,
      discountPercentage: 27.78,
      category: 'Coiffure',
      services: ['Coupe', 'Brushing', 'Maquillage'],
      validFrom: DateTime.now().subtract(const Duration(days: 4)),
      validTo: DateTime.now().add(const Duration(days: 16)),
      isActive: true,
      termsAndConditions:
          'Durée: 2h30. Réservation 48h à l\'avance. Apportez vos photos de référence.',
      promoCode: 'STYLE28',
      maxUses: 12,
      currentUses: 2,
      isNew: true,
      isPopular: false,
      rating: 4.8,
      reviewCount: 7,
    ),
  ];

  List<Offer> get allOffers => List.unmodifiable(_offers);

  List<Offer> get activeOffers =>
      _offers.where((offer) => offer.isAvailable).toList();

  List<Offer> get newOffers =>
      _offers.where((offer) => offer.isNew && offer.isAvailable).toList();

  List<Offer> get popularOffers =>
      _offers.where((offer) => offer.isPopular && offer.isAvailable).toList();

  List<Offer> getOffersByCategory(String category) {
    return _offers
        .where((offer) =>
            offer.category.toLowerCase() == category.toLowerCase() &&
            offer.isAvailable)
        .toList();
  }

  List<String> get categories {
    return _offers.map((offer) => offer.category).toSet().toList()..sort();
  }

  Offer? getOfferById(String id) {
    try {
      return _offers.firstWhere((offer) => offer.id == id);
    } catch (e) {
      return null;
    }
  }

  List<Offer> searchOffers(String query) {
    if (query.isEmpty) return activeOffers;

    final lowercaseQuery = query.toLowerCase();
    return _offers
        .where((offer) =>
            offer.isAvailable &&
            (offer.title.toLowerCase().contains(lowercaseQuery) ||
                offer.description.toLowerCase().contains(lowercaseQuery) ||
                offer.category.toLowerCase().contains(lowercaseQuery) ||
                offer.services.any((service) =>
                    service.toLowerCase().contains(lowercaseQuery))))
        .toList();
  }

  List<Offer> getOffersByPriceRange(double minPrice, double maxPrice) {
    return _offers
        .where((offer) =>
            offer.isAvailable &&
            offer.discountedPrice >= minPrice &&
            offer.discountedPrice <= maxPrice)
        .toList();
  }

  List<Offer> getOffersByDiscountRange(double minDiscount, double maxDiscount) {
    return _offers
        .where((offer) =>
            offer.isAvailable &&
            offer.discountPercentage >= minDiscount &&
            offer.discountPercentage <= maxDiscount)
        .toList();
  }

  bool useOffer(String offerId) {
    final offerIndex = _offers.indexWhere((offer) => offer.id == offerId);
    if (offerIndex == -1) return false;

    final offer = _offers[offerIndex];
    if (!offer.isAvailable) return false;

    _offers[offerIndex] = offer.copyWith(currentUses: offer.currentUses + 1);
    return true;
  }

  bool validatePromoCode(String promoCode) {
    return _offers.any((offer) =>
        offer.promoCode.toLowerCase() == promoCode.toLowerCase() &&
        offer.isAvailable);
  }

  Offer? getOfferByPromoCode(String promoCode) {
    try {
      return _offers.firstWhere((offer) =>
          offer.promoCode.toLowerCase() == promoCode.toLowerCase() &&
          offer.isAvailable);
    } catch (e) {
      return null;
    }
  }

  List<Offer> getExpiringOffers({int days = 7}) {
    final expiryDate = DateTime.now().add(Duration(days: days));
    return _offers
        .where((offer) =>
            offer.isAvailable &&
            offer.validTo.isBefore(expiryDate) &&
            offer.validTo.isAfter(DateTime.now()))
        .toList();
  }

  List<Offer> getRecentlyAddedOffers({int days = 7}) {
    final recentDate = DateTime.now().subtract(Duration(days: days));
    return _offers
        .where(
            (offer) => offer.isAvailable && offer.validFrom.isAfter(recentDate))
        .toList();
  }

  Map<String, int> getCategoryStats() {
    final Map<String, int> stats = {};
    for (final offer in activeOffers) {
      stats[offer.category] = (stats[offer.category] ?? 0) + 1;
    }
    return stats;
  }

  double getAverageDiscount() {
    if (activeOffers.isEmpty) return 0.0;
    final totalDiscount =
        activeOffers.fold(0.0, (sum, offer) => sum + offer.discountPercentage);
    return totalDiscount / activeOffers.length;
  }

  Offer? getBestOffer() {
    if (activeOffers.isEmpty) return null;
    return activeOffers
        .reduce((a, b) => a.discountPercentage > b.discountPercentage ? a : b);
  }

  List<Offer> getTopOffers({int limit = 5}) {
    final sortedOffers = List<Offer>.from(activeOffers);
    sortedOffers
        .sort((a, b) => b.discountPercentage.compareTo(a.discountPercentage));
    return sortedOffers.take(limit).toList();
  }
}
