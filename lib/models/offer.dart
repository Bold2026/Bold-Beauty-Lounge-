class Offer {
  final String id;
  final String title;
  final String description;
  final String imageUrl;
  final double originalPrice;
  final double discountedPrice;
  final double discountPercentage;
  final String category;
  final List<String> services;
  final DateTime validFrom;
  final DateTime validTo;
  final bool isActive;
  final String termsAndConditions;
  final String promoCode;
  final int maxUses;
  final int currentUses;
  final bool isNew;
  final bool isPopular;
  final double rating;
  final int reviewCount;

  const Offer({
    required this.id,
    required this.title,
    required this.description,
    required this.imageUrl,
    required this.originalPrice,
    required this.discountedPrice,
    required this.discountPercentage,
    required this.category,
    required this.services,
    required this.validFrom,
    required this.validTo,
    required this.isActive,
    required this.termsAndConditions,
    required this.promoCode,
    required this.maxUses,
    required this.currentUses,
    this.isNew = false,
    this.isPopular = false,
    this.rating = 0.0,
    this.reviewCount = 0,
  });

  factory Offer.fromJson(Map<String, dynamic> json) {
    return Offer(
      id: json['id'] as String,
      title: json['title'] as String,
      description: json['description'] as String,
      imageUrl: json['imageUrl'] as String,
      originalPrice: (json['originalPrice'] as num).toDouble(),
      discountedPrice: (json['discountedPrice'] as num).toDouble(),
      discountPercentage: (json['discountPercentage'] as num).toDouble(),
      category: json['category'] as String,
      services: List<String>.from(json['services'] as List),
      validFrom: DateTime.parse(json['validFrom'] as String),
      validTo: DateTime.parse(json['validTo'] as String),
      isActive: json['isActive'] as bool,
      termsAndConditions: json['termsAndConditions'] as String,
      promoCode: json['promoCode'] as String,
      maxUses: json['maxUses'] as int,
      currentUses: json['currentUses'] as int,
      isNew: json['isNew'] as bool? ?? false,
      isPopular: json['isPopular'] as bool? ?? false,
      rating: (json['rating'] as num?)?.toDouble() ?? 0.0,
      reviewCount: json['reviewCount'] as int? ?? 0,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'description': description,
      'imageUrl': imageUrl,
      'originalPrice': originalPrice,
      'discountedPrice': discountedPrice,
      'discountPercentage': discountPercentage,
      'category': category,
      'services': services,
      'validFrom': validFrom.toIso8601String(),
      'validTo': validTo.toIso8601String(),
      'isActive': isActive,
      'termsAndConditions': termsAndConditions,
      'promoCode': promoCode,
      'maxUses': maxUses,
      'currentUses': currentUses,
      'isNew': isNew,
      'isPopular': isPopular,
      'rating': rating,
      'reviewCount': reviewCount,
    };
  }

  bool get isExpired => DateTime.now().isAfter(validTo);
  bool get isAvailable => isActive && !isExpired && currentUses < maxUses;
  double get savings => originalPrice - discountedPrice;
  String get discountText => '${discountPercentage.toStringAsFixed(0)}% OFF';
  String get validityText => 'Valid until ${_formatDate(validTo)}';
  String get usageText => '$currentUses/$maxUses uses';

  String _formatDate(DateTime date) {
    return '${date.day}/${date.month}/${date.year}';
  }

  Offer copyWith({
    String? id,
    String? title,
    String? description,
    String? imageUrl,
    double? originalPrice,
    double? discountedPrice,
    double? discountPercentage,
    String? category,
    List<String>? services,
    DateTime? validFrom,
    DateTime? validTo,
    bool? isActive,
    String? termsAndConditions,
    String? promoCode,
    int? maxUses,
    int? currentUses,
    bool? isNew,
    bool? isPopular,
    double? rating,
    int? reviewCount,
  }) {
    return Offer(
      id: id ?? this.id,
      title: title ?? this.title,
      description: description ?? this.description,
      imageUrl: imageUrl ?? this.imageUrl,
      originalPrice: originalPrice ?? this.originalPrice,
      discountedPrice: discountedPrice ?? this.discountedPrice,
      discountPercentage: discountPercentage ?? this.discountPercentage,
      category: category ?? this.category,
      services: services ?? this.services,
      validFrom: validFrom ?? this.validFrom,
      validTo: validTo ?? this.validTo,
      isActive: isActive ?? this.isActive,
      termsAndConditions: termsAndConditions ?? this.termsAndConditions,
      promoCode: promoCode ?? this.promoCode,
      maxUses: maxUses ?? this.maxUses,
      currentUses: currentUses ?? this.currentUses,
      isNew: isNew ?? this.isNew,
      isPopular: isPopular ?? this.isPopular,
      rating: rating ?? this.rating,
      reviewCount: reviewCount ?? this.reviewCount,
    );
  }
}
