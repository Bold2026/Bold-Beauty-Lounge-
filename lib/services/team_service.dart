import '../models/team_member.dart';

class TeamService {
  static final TeamService _instance = TeamService._internal();
  factory TeamService() => _instance;
  TeamService._internal();

  final List<TeamMember> _teamMembers = [
    TeamMember(
      id: '1',
      name: 'Leila Bazi Lahlo',
      title: 'Directeur général',
      specialization: 'Management & Stratégie',
      description:
          'Directrice expérimentée avec plus de 15 ans dans l\'industrie de la beauté. Passionnée par l\'innovation et l\'excellence du service client.',
      imageUrl:
          'https://images.unsplash.com/photo-1494790108755-2616b612b786?w=400',
      services: [
        'Consultation stratégique',
        'Gestion d\'équipe',
        'Développement commercial'
      ],
      certifications: [
        'MBA Management',
        'Certification Beauté Avancée',
        'Leadership Excellence'
      ],
      experienceYears: 15,
      rating: 4.9,
      reviewCount: 45,
      languages: ['Français', 'Arabe', 'Anglais'],
      email: 'leila@boldbeautylounge.com',
      phone: '+212 6 12 34 56 78',
      socialMedia: ['@leila_bazi', 'linkedin.com/in/leila-bazi'],
      isAvailable: true,
      availability: 'Lun-Ven 9h-18h',
      bio:
          'Leila a transformé Bold Beauty Lounge en un centre de beauté de renommée internationale. Sa vision et son leadership ont permis à l\'équipe d\'atteindre des standards d\'excellence exceptionnels.',
      achievements: [
        'Prix Excellence Beauté 2023',
        'Top 10 Salons Maroc 2022',
        'Certification Qualité ISO 9001',
      ],
      education: 'MBA Management - Université Hassan II',
      skills: ['Leadership', 'Stratégie', 'Gestion', 'Innovation'],
    ),
    TeamMember(
      id: '2',
      name: 'Laarach Fadoua',
      title: 'Esthéticienne Senior',
      specialization: 'Soins du visage & Corps',
      description:
          'Spécialiste des soins du visage avec une expertise en techniques anti-âge et traitements premium.',
      imageUrl:
          'https://images.unsplash.com/photo-1580618672591-eb180b1a973f?w=400',
      services: [
        'Soins du visage',
        'Traitements anti-âge',
        'Peeling chimique',
        'Hydrafacial'
      ],
      certifications: [
        'Esthéticienne Diplômée',
        'Certification Hydrafacial',
        'Anti-Aging Specialist'
      ],
      experienceYears: 8,
      rating: 4.8,
      reviewCount: 32,
      languages: ['Français', 'Arabe'],
      email: 'fadoua@boldbeautylounge.com',
      phone: '+212 6 23 45 67 89',
      socialMedia: ['@fadoua_laarach'],
      isAvailable: true,
      availability: 'Mar-Sam 9h-17h',
      bio:
          'Fadoua est passionnée par l\'art de la beauté naturelle. Elle se spécialise dans les techniques avancées de soins du visage et a aidé des centaines de clientes à retrouver leur éclat naturel.',
      achievements: [
        'Meilleure Esthéticienne 2022',
        'Certification Hydrafacial Master',
        'Formation Paris - Institut Esthétique',
      ],
      education: 'Diplôme Esthéticienne - Institut de Beauté Casablanca',
      skills: ['Soins du visage', 'Anti-âge', 'Hydrafacial', 'Peeling'],
    ),
    TeamMember(
      id: '3',
      name: 'Zineb Zineddine',
      title: 'Directrice spécialiste beauté',
      specialization: 'Maquillage & Coiffure',
      description:
          'Artiste maquilleuse et coiffeuse créative, spécialisée dans les looks de mariage et événements spéciaux.',
      imageUrl:
          'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=400',
      services: [
        'Maquillage mariage',
        'Coiffure événement',
        'Make-up artist',
        'Styling'
      ],
      certifications: [
        'Maquilleuse Professionnelle',
        'Coiffeuse Diplômée',
        'Wedding Specialist'
      ],
      experienceYears: 12,
      rating: 4.9,
      reviewCount: 67,
      languages: ['Français', 'Arabe', 'Anglais', 'Espagnol'],
      email: 'zineb@boldbeautylounge.com',
      phone: '+212 6 34 56 78 90',
      socialMedia: ['@zineb_zineddine', 'instagram.com/zineb_beauty'],
      isAvailable: true,
      availability: 'Mer-Dim 10h-19h',
      bio:
          'Zineb est une artiste de la beauté reconnue pour ses créations exceptionnelles. Elle a travaillé avec des célébrités et a participé à de nombreux défilés de mode internationaux.',
      achievements: [
        'Maquilleuse de l\'Année 2023',
        'Participation Fashion Week Paris',
        'Collaboration avec Chanel',
      ],
      education: 'École de Maquillage Professionnel - Paris',
      skills: ['Maquillage', 'Coiffure', 'Styling', 'Créativité'],
    ),
    TeamMember(
      id: '4',
      name: 'Nasira Mounir',
      title: 'Beautician',
      specialization: 'Manucure & Pédicure',
      description:
          'Spécialiste des soins des ongles avec une expertise en nail art et techniques de vernis semi-permanent.',
      imageUrl:
          'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=400',
      services: [
        'Manucure française',
        'Pédicure',
        'Nail art',
        'Vernis semi-permanent'
      ],
      certifications: [
        'Nail Technician',
        'Nail Art Specialist',
        'Gel Polish Expert'
      ],
      experienceYears: 6,
      rating: 4.7,
      reviewCount: 28,
      languages: ['Français', 'Arabe'],
      email: 'nasira@boldbeautylounge.com',
      phone: '+212 6 45 67 89 01',
      socialMedia: ['@nasira_nails'],
      isAvailable: true,
      availability: 'Lun-Sam 9h-18h',
      bio:
          'Nasira transforme les ongles en véritables œuvres d\'art. Sa créativité et sa précision font d\'elle une référence dans le domaine de la nail art.',
      achievements: [
        'Championne Nail Art Maroc 2022',
        'Formation Corée du Sud',
        'Certification Gel Polish Master',
      ],
      education: 'Formation Nail Art - Seoul Beauty Academy',
      skills: ['Nail Art', 'Manucure', 'Pédicure', 'Créativité'],
    ),
    TeamMember(
      id: '5',
      name: 'Bachir Bachir',
      title: 'Technicien Principal',
      specialization: 'Épilation & Massage',
      description:
          'Expert en épilation laser et techniques de massage thérapeutique avec plus de 10 ans d\'expérience.',
      imageUrl:
          'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400',
      services: [
        'Épilation laser',
        'Massage thérapeutique',
        'Épilation définitive',
        'Relaxation'
      ],
      certifications: [
        'Technicien Laser',
        'Massage Thérapeutique',
        'Épilation Avancée'
      ],
      experienceYears: 10,
      rating: 4.8,
      reviewCount: 41,
      languages: ['Français', 'Arabe', 'Anglais'],
      email: 'bachir@boldbeautylounge.com',
      phone: '+212 6 56 78 90 12',
      socialMedia: ['@bachir_technician'],
      isAvailable: true,
      availability: 'Lun-Ven 8h-17h',
      bio:
          'Bachir est un technicien expérimenté spécialisé dans les techniques d\'épilation laser les plus avancées. Son expertise et son professionnalisme rassurent nos clients.',
      achievements: [
        'Technicien Laser Certifié',
        'Formation Israël - Technologies Laser',
        'Meilleur Technicien 2021',
      ],
      education: 'Formation Laser - Institut Technologique Israël',
      skills: ['Épilation Laser', 'Massage', 'Technologie', 'Précision'],
    ),
    TeamMember(
      id: '6',
      name: 'Benallou Fatima Zahra',
      title: 'Coiffeur et Coloriste',
      specialization: 'Coloration & Coupe',
      description:
          'Coiffeuse créative spécialisée dans les techniques de coloration avancées et les coupes tendances.',
      imageUrl:
          'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=400',
      services: ['Coloration', 'Coupe', 'Brushing', 'Soins capillaires'],
      certifications: [
        'Coiffeuse Diplômée',
        'Coloriste Expert',
        'Techniques Avancées'
      ],
      experienceYears: 9,
      rating: 4.6,
      reviewCount: 35,
      languages: ['Français', 'Arabe'],
      email: 'fatima@boldbeautylounge.com',
      phone: '+212 6 67 89 01 23',
      socialMedia: ['@fatima_hair'],
      isAvailable: true,
      availability: 'Mar-Sam 9h-18h',
      bio:
          'Fatima Zahra est une coiffeuse passionnée qui suit les dernières tendances de la mode capillaire. Elle excelle dans les techniques de coloration et les coupes créatives.',
      achievements: [
        'Coloriste de l\'Année 2022',
        'Formation Paris - L\'Oréal',
        'Participation Salon de la Coiffure',
      ],
      education: 'École de Coiffure - L\'Oréal Paris',
      skills: ['Coloration', 'Coupe', 'Tendances', 'Créativité'],
    ),
    TeamMember(
      id: '7',
      name: 'Raja Jouani',
      title: 'Épilateur Blond',
      specialization: 'Épilation & Soins',
      description:
          'Spécialiste de l\'épilation avec une expertise particulière en épilation à la cire et techniques de soins post-épilation.',
      imageUrl:
          'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400',
      services: [
        'Épilation à la cire',
        'Épilation définitive',
        'Soins post-épilation',
        'Épilation du visage'
      ],
      certifications: [
        'Épilateur Certifié',
        'Techniques Cire',
        'Soins Spécialisés'
      ],
      experienceYears: 7,
      rating: 4.7,
      reviewCount: 29,
      languages: ['Français', 'Arabe'],
      email: 'raja@boldbeautylounge.com',
      phone: '+212 6 78 90 12 34',
      socialMedia: ['@raja_epilation'],
      isAvailable: true,
      availability: 'Lun-Sam 10h-19h',
      bio:
          'Raja est une experte de l\'épilation qui privilégie le confort et l\'efficacité. Ses techniques douces et professionnelles font d\'elle une favorite de nos clientes.',
      achievements: [
        'Épilateur Expert 2023',
        'Formation Techniques Avancées',
        'Certification Soins Post-Épilation',
      ],
      education: 'Formation Épilation - Institut de Beauté',
      skills: ['Épilation', 'Soins', 'Techniques', 'Confort'],
    ),
    TeamMember(
      id: '8',
      name: 'Siham Laroussi',
      title: 'Cosmétologue Senior',
      specialization: 'Conseil & Produits',
      description:
          'Experte en cosmétologie avec une connaissance approfondie des produits de beauté et des techniques d\'application.',
      imageUrl:
          'https://images.unsplash.com/photo-1488426862026-3ee34a7d11df?w=400',
      services: [
        'Conseil cosmétique',
        'Analyse de peau',
        'Routine beauté',
        'Formation produits'
      ],
      certifications: [
        'Cosmétologue Diplômée',
        'Analyse de Peau',
        'Conseil Beauté'
      ],
      experienceYears: 11,
      rating: 4.8,
      reviewCount: 38,
      languages: ['Français', 'Arabe', 'Anglais'],
      email: 'siham@boldbeautylounge.com',
      phone: '+212 6 89 01 23 45',
      socialMedia: ['@siham_cosmetology'],
      isAvailable: true,
      availability: 'Lun-Ven 9h-17h',
      bio:
          'Siham est une cosmétologue passionnée qui aide ses clientes à choisir les meilleurs produits pour leur type de peau. Son expertise est reconnue dans tout le Maroc.',
      achievements: [
        'Cosmétologue de l\'Année 2023',
        'Formation Suisse - Laboratoires',
        'Certification Analyse de Peau',
      ],
      education: 'Diplôme Cosmétologie - Université de Genève',
      skills: ['Cosmétologie', 'Conseil', 'Analyse', 'Produits'],
    ),
    TeamMember(
      id: '9',
      name: 'Hiyar Sanae',
      title: 'Esthéticienne et Masseuse',
      specialization: 'Massage & Relaxation',
      description:
          'Spécialiste des techniques de massage thérapeutique et de relaxation avec une approche holistique de la beauté.',
      imageUrl:
          'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=400',
      services: [
        'Massage thérapeutique',
        'Massage relaxant',
        'Soins du corps',
        'Aromathérapie'
      ],
      certifications: [
        'Masseuse Diplômée',
        'Thérapie Manuelle',
        'Aromathérapie'
      ],
      experienceYears: 8,
      rating: 4.9,
      reviewCount: 42,
      languages: ['Français', 'Arabe', 'Anglais'],
      email: 'sanae@boldbeautylounge.com',
      phone: '+212 6 90 12 34 56',
      socialMedia: ['@sanae_massage'],
      isAvailable: true,
      availability: 'Mar-Dim 10h-20h',
      bio:
          'Sanae combine l\'art du massage avec une approche holistique de la beauté. Ses techniques uniques procurent détente et bien-être à nos clientes.',
      achievements: [
        'Masseuse de l\'Année 2022',
        'Formation Thaïlande - Massage Traditionnel',
        'Certification Aromathérapie',
      ],
      education: 'Formation Massage - École de Massage Traditionnel',
      skills: ['Massage', 'Relaxation', 'Aromathérapie', 'Bien-être'],
    ),
    TeamMember(
      id: '10',
      name: 'Hasnae Idrissi',
      title: 'Épilateur Blond',
      specialization: 'Épilation & Esthétique',
      description:
          'Jeune talent spécialisé dans les techniques d\'épilation modernes et les soins esthétiques personnalisés.',
      imageUrl:
          'https://images.unsplash.com/photo-1508214751196-bcfd4ca60f91?w=400',
      services: [
        'Épilation moderne',
        'Soins esthétiques',
        'Épilation du visage',
        'Conseils beauté'
      ],
      certifications: [
        'Épilateur Moderne',
        'Esthétique Avancée',
        'Techniques Nouvelles'
      ],
      experienceYears: 4,
      rating: 4.5,
      reviewCount: 18,
      languages: ['Français', 'Arabe'],
      email: 'hasnae@boldbeautylounge.com',
      phone: '+212 6 01 23 45 67',
      socialMedia: ['@hasnae_beauty'],
      isAvailable: true,
      availability: 'Lun-Sam 9h-18h',
      bio:
          'Hasnae apporte une touche moderne et innovante à notre équipe. Sa passion pour les nouvelles techniques d\'épilation en fait une professionnelle prometteuse.',
      achievements: [
        'Révélation Épilateur 2023',
        'Formation Techniques Modernes',
        'Certification Esthétique',
      ],
      education: 'Formation Esthétique - Institut Moderne',
      skills: ['Épilation Moderne', 'Esthétique', 'Innovation', 'Jeunesse'],
    ),
  ];

  List<TeamMember> get allMembers => List.unmodifiable(_teamMembers);

  List<TeamMember> get availableMembers =>
      _teamMembers.where((member) => member.isAvailable).toList();

  List<TeamMember> get seniorMembers =>
      _teamMembers.where((member) => member.experienceYears >= 8).toList();

  List<TeamMember> get topRatedMembers =>
      _teamMembers.where((member) => member.rating >= 4.7).toList();

  List<TeamMember> getMembersBySpecialization(String specialization) {
    return _teamMembers
        .where((member) =>
            member.specialization
                .toLowerCase()
                .contains(specialization.toLowerCase()) ||
            member.services.any((service) =>
                service.toLowerCase().contains(specialization.toLowerCase())))
        .toList();
  }

  List<String> get specializations {
    return _teamMembers.map((member) => member.specialization).toSet().toList()
      ..sort();
  }

  List<String> get services {
    final allServices = <String>[];
    for (final member in _teamMembers) {
      allServices.addAll(member.services);
    }
    return allServices.toSet().toList()..sort();
  }

  TeamMember? getMemberById(String id) {
    try {
      return _teamMembers.firstWhere((member) => member.id == id);
    } catch (e) {
      return null;
    }
  }

  List<TeamMember> searchMembers(String query) {
    if (query.isEmpty) return availableMembers;

    final lowercaseQuery = query.toLowerCase();
    return _teamMembers
        .where((member) =>
            member.isAvailable &&
            (member.name.toLowerCase().contains(lowercaseQuery) ||
                member.title.toLowerCase().contains(lowercaseQuery) ||
                member.specialization.toLowerCase().contains(lowercaseQuery) ||
                member.services.any((service) =>
                    service.toLowerCase().contains(lowercaseQuery))))
        .toList();
  }

  List<TeamMember> getMembersByExperience(int minYears) {
    return _teamMembers
        .where((member) =>
            member.isAvailable && member.experienceYears >= minYears)
        .toList();
  }

  List<TeamMember> getMembersByRating(double minRating) {
    return _teamMembers
        .where((member) => member.isAvailable && member.rating >= minRating)
        .toList();
  }

  Map<String, int> getSpecializationStats() {
    final Map<String, int> stats = {};
    for (final member in availableMembers) {
      stats[member.specialization] = (stats[member.specialization] ?? 0) + 1;
    }
    return stats;
  }

  double getAverageRating() {
    if (availableMembers.isEmpty) return 0.0;
    final totalRating =
        availableMembers.fold(0.0, (sum, member) => sum + member.rating);
    return totalRating / availableMembers.length;
  }

  int getTotalExperience() {
    return availableMembers.fold(
        0, (sum, member) => sum + member.experienceYears);
  }

  TeamMember? getMostExperiencedMember() {
    if (availableMembers.isEmpty) return null;
    return availableMembers
        .reduce((a, b) => a.experienceYears > b.experienceYears ? a : b);
  }

  TeamMember? getTopRatedMember() {
    if (availableMembers.isEmpty) return null;
    return availableMembers.reduce((a, b) => a.rating > b.rating ? a : b);
  }

  List<TeamMember> getFeaturedMembers({int limit = 6}) {
    final sortedMembers = List<TeamMember>.from(availableMembers);
    sortedMembers.sort((a, b) => b.rating.compareTo(a.rating));
    return sortedMembers.take(limit).toList();
  }
}
