import 'package:flutter/material.dart';
import 'date_time_selection_screen.dart';

class ServiceSelectionScreen extends StatefulWidget {
  const ServiceSelectionScreen({super.key});

  @override
  State<ServiceSelectionScreen> createState() => _ServiceSelectionScreenState();
}

class _ServiceSelectionScreenState extends State<ServiceSelectionScreen> {
  final TextEditingController _searchController = TextEditingController();
  final ScrollController _scrollController = ScrollController();
  late final Map<String, GlobalKey> _sectionKeys;

  String _selectedCategoryId = _kServiceCategories.first.id;
  String _searchQuery = '';
  final List<_ServiceItem> _selectedServices = [];

  @override
  void initState() {
    super.initState();
    _sectionKeys = {
      for (final category in _kServiceCategories) category.id: GlobalKey(),
    };
    _searchController.addListener(() {
      final query = _searchController.text.trim().toLowerCase();
      if (query == _searchQuery) return;
      setState(() {
        _searchQuery = query;
        final categories = _filteredCategories;
        if (categories.isNotEmpty &&
            categories.every((cat) => cat.id != _selectedCategoryId)) {
          _selectedCategoryId = categories.first.id;
        }
      });
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  List<_ServiceCategory> get _filteredCategories {
    if (_searchQuery.isEmpty) {
      return _kServiceCategories;
    }
    final List<_ServiceCategory> filtered = [];
    for (final category in _kServiceCategories) {
      final services = category.services
          .where(
            (service) =>
                service.name.toLowerCase().contains(_searchQuery) ||
                service.durationLabel.toLowerCase().contains(_searchQuery),
          )
          .toList();
      if (services.isNotEmpty) {
        filtered.add(category.copyWith(services: services));
      }
    }
    return filtered;
  }

  void _onCategoryTap(String categoryId) {
    setState(() => _selectedCategoryId = categoryId);
    _scrollToCategory(categoryId);
  }

  void _scrollToCategory(String categoryId) {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final key = _sectionKeys[categoryId];
      if (key == null) return;
      final context = key.currentContext;
      if (context != null) {
        Scrollable.ensureVisible(
          context,
          duration: const Duration(milliseconds: 400),
          curve: Curves.easeInOut,
          alignment: 0.1,
        );
      }
    });
  }

  void _toggleService(_ServiceItem service) {
    setState(() {
      final existingIndex = _selectedServices.indexWhere(
        (item) => item.id == service.id,
      );
      if (existingIndex >= 0) {
        _selectedServices.removeAt(existingIndex);
      } else {
        _selectedServices.add(service);
      }
    });
  }

  bool _isServiceSelected(_ServiceItem service) {
    return _selectedServices.any((item) => item.id == service.id);
  }

  int get _totalDurationMinutes => _selectedServices.fold(
    0,
    (previousValue, element) => previousValue + element.durationMinutes,
  );

  String get _formattedTotalDuration {
    final minutes = _totalDurationMinutes;
    if (minutes <= 0) return '0 min';
    final hours = minutes ~/ 60;
    final remainingMinutes = minutes % 60;
    if (hours > 0 && remainingMinutes > 0) {
      return '${hours} h ${remainingMinutes} min';
    } else if (hours > 0) {
      return '${hours} h';
    } else {
      return '$remainingMinutes min';
    }
  }

  int get _totalPrice =>
      _selectedServices.fold(0, (sum, service) => sum + service.price);

  List<Map<String, dynamic>> get _selectedServicesPayload => _selectedServices
      .map(
        (service) => {
          'name': service.name,
          'duration': service.durationLabel,
          'price': service.price,
        },
      )
      .toList();

  @override
  Widget build(BuildContext context) {
    final categoriesToDisplay = _filteredCategories;

    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        backgroundColor: Colors.white,
        foregroundColor: Colors.black,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, size: 18),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'Sélection des services',
          style: TextStyle(
            color: Colors.black,
            fontSize: 18,
            fontWeight: FontWeight.bold,
          ),
        ),
        centerTitle: true,
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 4, 20, 12),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Rechercher un service...',
                hintStyle: TextStyle(color: Colors.grey.shade500),
                prefixIcon: Icon(Icons.search, color: Colors.grey.shade600),
                filled: true,
                fillColor: Colors.grey.shade100,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(16),
                  borderSide: BorderSide.none,
                ),
                contentPadding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 14,
                ),
              ),
            ),
          ),
          SizedBox(
            height: 52,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 20),
              itemCount: _kServiceCategories.length,
              itemBuilder: (context, index) {
                final category = _kServiceCategories[index];
                final bool isSelected = category.id == _selectedCategoryId;
                return GestureDetector(
                  onTap: () => _onCategoryTap(category.id),
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 220),
                    margin: EdgeInsets.only(
                      right: index == _kServiceCategories.length - 1 ? 0 : 12,
                    ),
                    padding: const EdgeInsets.symmetric(
                      horizontal: 18,
                      vertical: 10,
                    ),
                    decoration: BoxDecoration(
                      color: isSelected ? Colors.black : Colors.white,
                      borderRadius: BorderRadius.circular(26),
                      border: Border.all(
                        color: isSelected
                            ? Colors.transparent
                            : Colors.grey.shade300,
                      ),
                      boxShadow: isSelected
                          ? [
                              BoxShadow(
                                color: Colors.black.withValues(alpha: 0.18),
                                blurRadius: 18,
                                offset: const Offset(0, 8),
                              ),
                            ]
                          : [],
                    ),
                    child: Row(
                      children: [
                        Text(
                          category.title,
                          style: TextStyle(
                            color: isSelected ? Colors.white : Colors.black,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 4,
                          ),
                          decoration: BoxDecoration(
                            color: isSelected
                                ? Colors.white
                                : Colors.grey.shade200,
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Text(
                            '${category.services.length}',
                            style: TextStyle(
                              color: isSelected
                                  ? Colors.black
                                  : Colors.grey.shade700,
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
          ),
          const SizedBox(height: 12),
          if (categoriesToDisplay.isEmpty)
            Expanded(
              child: Center(
                child: Text(
                  'Aucun service trouvé.',
                  style: TextStyle(color: Colors.grey.shade600),
                ),
              ),
            )
          else
            Expanded(
              child: ListView.builder(
                controller: _scrollController,
                padding: EdgeInsets.fromLTRB(
                  20,
                  0,
                  20,
                  _selectedServices.isEmpty ? 20 : 140,
                ),
                itemCount: categoriesToDisplay.length,
                itemBuilder: (context, index) {
                  final category = categoriesToDisplay[index];
                  return Container(
                    key: _sectionKeys[category.id],
                    margin: EdgeInsets.only(
                      bottom: index == categoriesToDisplay.length - 1 ? 0 : 28,
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              category.title,
                              style: const TextStyle(
                                fontSize: 20,
                                fontWeight: FontWeight.w700,
                                color: Colors.black,
                              ),
                            ),
                            Text(
                              '${category.services.length} service${category.services.length > 1 ? 's' : ''}',
                              style: TextStyle(
                                fontSize: 14,
                                color: Colors.grey.shade500,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 16),
                        ...category.services.map((service) {
                          final bool isSelected = _isServiceSelected(service);
                          return GestureDetector(
                            onTap: () => _toggleService(service),
                            child: AnimatedContainer(
                              duration: const Duration(milliseconds: 200),
                              margin: const EdgeInsets.only(bottom: 12),
                              padding: const EdgeInsets.all(18),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(18),
                                border: Border.all(
                                  color: Colors.grey.shade200,
                                  width: 1,
                                ),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withValues(alpha: 0.06),
                                    blurRadius: 12,
                                    offset: const Offset(0, 6),
                                  ),
                                ],
                              ),
                              child: Row(
                                children: [
                                  Container(
                                    width: 44,
                                    height: 44,
                                    decoration: BoxDecoration(
                                      color: isSelected
                                          ? const Color(0xFFE9D7C2)
                                          : Colors.grey.shade200,
                                      borderRadius: BorderRadius.circular(22),
                                    ),
                                    child: Icon(
                                      service.icon,
                                      color: isSelected
                                          ? Colors.white
                                          : Colors.grey.shade700,
                                      size: 22,
                                    ),
                                  ),
                                  const SizedBox(width: 16),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          service.name,
                                          style: const TextStyle(
                                            fontSize: 16,
                                            fontWeight: FontWeight.w600,
                                            color: Colors.black,
                                          ),
                                        ),
                                        const SizedBox(height: 6),
                                        Row(
                                          children: [
                                            Icon(
                                              Icons.access_time,
                                              size: 14,
                                              color: Colors.grey.shade500,
                                            ),
                                            const SizedBox(width: 4),
                                            Text(
                                              service.durationLabel,
                                              style: TextStyle(
                                                fontSize: 13,
                                                color: Colors.grey.shade600,
                                              ),
                                            ),
                                            const SizedBox(width: 8),
                                            Text(
                                              '${service.price} DH',
                                              style: TextStyle(
                                                fontSize: 13,
                                                color: Colors.grey.shade600,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ],
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  Container(
                                    width: 32,
                                    height: 32,
                                    decoration: BoxDecoration(
                                      color: isSelected
                                          ? const Color(0xFFE9D7C2)
                                          : Colors.grey.shade200,
                                      shape: BoxShape.circle,
                                    ),
                                    child: isSelected
                                        ? const Icon(
                                            Icons.check,
                                            color: Colors.white,
                                            size: 20,
                                          )
                                        : const Icon(
                                            Icons.add,
                                            color: Colors.black87,
                                            size: 20,
                                          ),
                                  ),
                                ],
                              ),
                            ),
                          );
                        }),
                      ],
                    ),
                  );
                },
              ),
            ),
          if (_selectedServices.isNotEmpty)
            Container(
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
              decoration: BoxDecoration(
                color: Colors.white,
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.12),
                    blurRadius: 20,
                    offset: const Offset(0, -6),
                  ),
                ],
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          '${_selectedServices.length} SERVICE${_selectedServices.length > 1 ? 'S' : ''} SÉLECTIONNÉ${_selectedServices.length > 1 ? 'S' : ''}',
                          style: const TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                            color: Colors.black,
                            letterSpacing: 0.5,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Row(
                          children: [
                            Icon(
                              Icons.access_time,
                              size: 14,
                              color: Colors.grey.shade600,
                            ),
                            const SizedBox(width: 4),
                            Text(
                              _formattedTotalDuration,
                              style: const TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.bold,
                                color: Colors.black,
                              ),
                            ),
                            const SizedBox(width: 12),
                            Text(
                              '${_totalPrice} DH',
                              style: const TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.bold,
                                color: Colors.black,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 16),
                  ElevatedButton(
                    onPressed: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => DateTimeSelectionScreen(
                            selectedServices: _selectedServicesPayload,
                            totalPrice: _totalPrice,
                            totalDuration: _totalDurationMinutes,
                          ),
                        ),
                      );
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFFE9D7C2),
                      foregroundColor: Colors.black,
                      padding: const EdgeInsets.symmetric(
                        horizontal: 32,
                        vertical: 14,
                      ),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(26),
                      ),
                    ),
                    child: const Text(
                      'Suivant →',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
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
}

class _ServiceItem {
  const _ServiceItem({
    required this.id,
    required this.name,
    required this.durationMinutes,
    required this.durationLabel,
    required this.price,
    required this.icon,
  });

  final String id;
  final String name;
  final int durationMinutes;
  final String durationLabel;
  final int price;
  final IconData icon;
}

class _ServiceCategory {
  const _ServiceCategory({
    required this.id,
    required this.title,
    required this.services,
  });

  final String id;
  final String title;
  final List<_ServiceItem> services;

  _ServiceCategory copyWith({List<_ServiceItem>? services}) => _ServiceCategory(
    id: id,
    title: title,
    services: services ?? this.services,
  );
}

const List<_ServiceCategory> _kServiceCategories = [
  _ServiceCategory(
    id: 'coiffure',
    title: 'Coiffure',
    services: [
      _ServiceItem(
        id: 'hair-wash-short',
        name: 'Hair Wash - Spécial Short',
        durationMinutes: 30,
        durationLabel: '30 min',
        price: 70,
        icon: Icons.water_drop,
      ),
      _ServiceItem(
        id: 'hair-wash-medium',
        name: 'Hair Wash - Spécial Medium',
        durationMinutes: 30,
        durationLabel: '30 min',
        price: 80,
        icon: Icons.water_drop,
      ),
      _ServiceItem(
        id: 'hair-wash-long',
        name: 'Hair Wash - Spécial Long',
        durationMinutes: 30,
        durationLabel: '30 min',
        price: 80,
        icon: Icons.water_drop,
      ),
      _ServiceItem(
        id: 'brushing-simple-short',
        name: 'Brushing Simple Short',
        durationMinutes: 30,
        durationLabel: '30 min',
        price: 60,
        icon: Icons.air,
      ),
      _ServiceItem(
        id: 'brushing-simple-medium',
        name: 'Brushing Simple Medium',
        durationMinutes: 45,
        durationLabel: '45 min',
        price: 100,
        icon: Icons.air,
      ),
      _ServiceItem(
        id: 'brushing-simple-long',
        name: 'Brushing Simple Long',
        durationMinutes: 60,
        durationLabel: '60 min',
        price: 120,
        icon: Icons.air,
      ),
      _ServiceItem(
        id: 'brushing-wavy-short',
        name: 'Brushing Wavy Short',
        durationMinutes: 30,
        durationLabel: '30 min',
        price: 80,
        icon: Icons.air,
      ),
      _ServiceItem(
        id: 'brushing-wavy-medium',
        name: 'Brushing Wavy Medium',
        durationMinutes: 45,
        durationLabel: '45 min',
        price: 100,
        icon: Icons.air,
      ),
      _ServiceItem(
        id: 'brushing-wavy-long',
        name: 'Brushing Wavy Long',
        durationMinutes: 60,
        durationLabel: '60 min',
        price: 120,
        icon: Icons.air,
      ),
      _ServiceItem(
        id: 'coiffure-signature',
        name: 'Coiffure Signature',
        durationMinutes: 60,
        durationLabel: '60 min',
        price: 150,
        icon: Icons.content_cut,
      ),
      _ServiceItem(
        id: 'coloration-complete',
        name: 'Coloration Complète',
        durationMinutes: 90,
        durationLabel: '90 min',
        price: 200,
        icon: Icons.palette,
      ),
    ],
  ),
  _ServiceCategory(
    id: 'onglerie',
    title: 'Onglerie',
    services: [
      _ServiceItem(
        id: 'manucure-classique',
        name: 'Manucure Classique',
        durationMinutes: 45,
        durationLabel: '45 min',
        price: 80,
        icon: Icons.brush,
      ),
      _ServiceItem(
        id: 'pedicure-classique',
        name: 'Pédicure Classique',
        durationMinutes: 60,
        durationLabel: '60 min',
        price: 100,
        icon: Icons.brush,
      ),
      _ServiceItem(
        id: 'pose-ongles',
        name: 'Pose Ongles',
        durationMinutes: 90,
        durationLabel: '90 min',
        price: 150,
        icon: Icons.brush,
      ),
      _ServiceItem(
        id: 'decoration-ongles',
        name: 'Décoration Ongles',
        durationMinutes: 30,
        durationLabel: '30 min',
        price: 50,
        icon: Icons.brush,
      ),
    ],
  ),
  _ServiceCategory(
    id: 'hammam',
    title: 'Hammam',
    services: [
      _ServiceItem(
        id: 'hammam-experience',
        name: 'Hammam Expérience',
        durationMinutes: 60,
        durationLabel: '60 min',
        price: 150,
        icon: Icons.spa,
      ),
      _ServiceItem(
        id: 'hammam-royal',
        name: 'Hammam Royal',
        durationMinutes: 90,
        durationLabel: '90 min',
        price: 200,
        icon: Icons.spa,
      ),
      _ServiceItem(
        id: 'hammam-sensation',
        name: 'Hammam Sensation',
        durationMinutes: 120,
        durationLabel: '120 min',
        price: 250,
        icon: Icons.spa,
      ),
    ],
  ),
  _ServiceCategory(
    id: 'head-spa',
    title: 'Head Spa',
    services: [
      _ServiceItem(
        id: 'head-spa-bold',
        name: 'Head Spa Bold Experience',
        durationMinutes: 30,
        durationLabel: '30 min',
        price: 200,
        icon: Icons.spa,
      ),
      _ServiceItem(
        id: 'head-spa-sensual',
        name: 'Head Spa Sensual',
        durationMinutes: 60,
        durationLabel: '60 min',
        price: 350,
        icon: Icons.spa,
      ),
      _ServiceItem(
        id: 'head-spa-royal',
        name: 'Head Spa Royal',
        durationMinutes: 90,
        durationLabel: '90 min',
        price: 450,
        icon: Icons.spa,
      ),
    ],
  ),
  _ServiceCategory(
    id: 'soins-esthetiques',
    title: 'Soins Esthétiques',
    services: [
      _ServiceItem(
        id: 'epilation-jambes',
        name: 'Épilation Jambes',
        durationMinutes: 45,
        durationLabel: '45 min',
        price: 100,
        icon: Icons.content_cut,
      ),
      _ServiceItem(
        id: 'epilation-maillot',
        name: 'Épilation Maillot',
        durationMinutes: 30,
        durationLabel: '30 min',
        price: 80,
        icon: Icons.content_cut,
      ),
    ],
  ),
];
