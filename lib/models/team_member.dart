class TeamMember {
  final String id;
  final String name;
  final String title;
  final String specialization;
  final String description;
  final String imageUrl;
  final List<String> services;
  final List<String> certifications;
  final int experienceYears;
  final double rating;
  final int reviewCount;
  final List<String> languages;
  final String email;
  final String phone;
  final List<String> socialMedia;
  final bool isAvailable;
  final String availability;
  final String bio;
  final List<String> achievements;
  final String education;
  final List<String> skills;

  const TeamMember({
    required this.id,
    required this.name,
    required this.title,
    required this.specialization,
    required this.description,
    required this.imageUrl,
    required this.services,
    required this.certifications,
    required this.experienceYears,
    required this.rating,
    required this.reviewCount,
    required this.languages,
    required this.email,
    required this.phone,
    required this.socialMedia,
    required this.isAvailable,
    required this.availability,
    required this.bio,
    required this.achievements,
    required this.education,
    required this.skills,
  });

  factory TeamMember.fromJson(Map<String, dynamic> json) {
    return TeamMember(
      id: json['id'] as String,
      name: json['name'] as String,
      title: json['title'] as String,
      specialization: json['specialization'] as String,
      description: json['description'] as String,
      imageUrl: json['imageUrl'] as String,
      services: List<String>.from(json['services'] as List),
      certifications: List<String>.from(json['certifications'] as List),
      experienceYears: json['experienceYears'] as int,
      rating: (json['rating'] as num).toDouble(),
      reviewCount: json['reviewCount'] as int,
      languages: List<String>.from(json['languages'] as List),
      email: json['email'] as String,
      phone: json['phone'] as String,
      socialMedia: List<String>.from(json['socialMedia'] as List),
      isAvailable: json['isAvailable'] as bool,
      availability: json['availability'] as String,
      bio: json['bio'] as String,
      achievements: List<String>.from(json['achievements'] as List),
      education: json['education'] as String,
      skills: List<String>.from(json['skills'] as List),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'title': title,
      'specialization': specialization,
      'description': description,
      'imageUrl': imageUrl,
      'services': services,
      'certifications': certifications,
      'experienceYears': experienceYears,
      'rating': rating,
      'reviewCount': reviewCount,
      'languages': languages,
      'email': email,
      'phone': phone,
      'socialMedia': socialMedia,
      'isAvailable': isAvailable,
      'availability': availability,
      'bio': bio,
      'achievements': achievements,
      'education': education,
      'skills': skills,
    };
  }

  String get experienceText => '$experienceYears ans d\'expérience';
  String get ratingText => '${rating.toStringAsFixed(1)} (${reviewCount} avis)';
  String get availabilityStatus => isAvailable ? 'Disponible' : 'Indisponible';
  String get shortDescription => description.length > 100
      ? '${description.substring(0, 100)}...'
      : description;

  TeamMember copyWith({
    String? id,
    String? name,
    String? title,
    String? specialization,
    String? description,
    String? imageUrl,
    List<String>? services,
    List<String>? certifications,
    int? experienceYears,
    double? rating,
    int? reviewCount,
    List<String>? languages,
    String? email,
    String? phone,
    List<String>? socialMedia,
    bool? isAvailable,
    String? availability,
    String? bio,
    List<String>? achievements,
    String? education,
    List<String>? skills,
  }) {
    return TeamMember(
      id: id ?? this.id,
      name: name ?? this.name,
      title: title ?? this.title,
      specialization: specialization ?? this.specialization,
      description: description ?? this.description,
      imageUrl: imageUrl ?? this.imageUrl,
      services: services ?? this.services,
      certifications: certifications ?? this.certifications,
      experienceYears: experienceYears ?? this.experienceYears,
      rating: rating ?? this.rating,
      reviewCount: reviewCount ?? this.reviewCount,
      languages: languages ?? this.languages,
      email: email ?? this.email,
      phone: phone ?? this.phone,
      socialMedia: socialMedia ?? this.socialMedia,
      isAvailable: isAvailable ?? this.isAvailable,
      availability: availability ?? this.availability,
      bio: bio ?? this.bio,
      achievements: achievements ?? this.achievements,
      education: education ?? this.education,
      skills: skills ?? this.skills,
    );
  }
}
