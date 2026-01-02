import 'package:flutter/material.dart';
import '../../models/offer.dart';
import '../../services/offer_service.dart';
import 'offer_detail_screen.dart';
import '../../widgets/offer_card_widget.dart';

class OffersScreen extends StatefulWidget {
  const OffersScreen({super.key});

  @override
  State<OffersScreen> createState() => _OffersScreenState();
}

class _OffersScreenState extends State<OffersScreen>
    with TickerProviderStateMixin {
  final OfferService _offerService = OfferService();
  final TextEditingController _searchController = TextEditingController();

  late TabController _tabController;
  String _selectedCategory = 'Tous';
  String _sortBy = 'Pertinence';
  double _minPrice = 0.0;
  double _maxPrice = 500.0;
  double _minDiscount = 0.0;
  double _maxDiscount = 100.0;

  List<Offer> _filteredOffers = [];
  bool _isSearching = false;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    _filteredOffers = _offerService.activeOffers;
  }

  @override
  void dispose() {
    _tabController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  void _filterOffers() {
    setState(() {
      List<Offer> offers = _offerService.activeOffers;

      // Category filter
      if (_selectedCategory != 'Tous') {
        offers = offers
            .where((offer) => offer.category == _selectedCategory)
            .toList();
      }

      // Price filter
      offers = offers
          .where((offer) =>
              offer.discountedPrice >= _minPrice &&
              offer.discountedPrice <= _maxPrice)
          .toList();

      // Discount filter
      offers = offers
          .where((offer) =>
              offer.discountPercentage >= _minDiscount &&
              offer.discountPercentage <= _maxDiscount)
          .toList();

      // Search filter
      if (_searchController.text.isNotEmpty) {
        offers = _offerService.searchOffers(_searchController.text);
        offers = offers
            .where((offer) =>
                _selectedCategory == 'Tous' ||
                offer.category == _selectedCategory)
            .toList();
      }

      // Sort
      switch (_sortBy) {
        case 'Prix croissant':
          offers.sort((a, b) => a.discountedPrice.compareTo(b.discountedPrice));
          break;
        case 'Prix décroissant':
          offers.sort((a, b) => b.discountedPrice.compareTo(a.discountedPrice));
          break;
        case 'Remise croissante':
          offers.sort(
              (a, b) => a.discountPercentage.compareTo(b.discountPercentage));
          break;
        case 'Remise décroissante':
          offers.sort(
              (a, b) => b.discountPercentage.compareTo(a.discountPercentage));
          break;
        case 'Nouveautés':
          offers = offers.where((offer) => offer.isNew).toList();
          break;
        case 'Populaires':
          offers = offers.where((offer) => offer.isPopular).toList();
          break;
        case 'Expiration proche':
          offers = _offerService.getExpiringOffers();
          break;
      }

      _filteredOffers = offers;
    });
  }

  void _showFilterDialog() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => StatefulBuilder(
        builder: (context, setModalState) => Container(
          height: MediaQuery.of(context).size.height * 0.8,
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
          ),
          child: Column(
            children: [
              Container(
                width: 40,
                height: 4,
                margin: const EdgeInsets.symmetric(vertical: 12),
                decoration: BoxDecoration(
                  color: Colors.grey[300],
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'Filtres',
                          style: TextStyle(
                            fontSize: 24,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        TextButton(
                          onPressed: () {
                            setModalState(() {
                              _selectedCategory = 'Tous';
                              _minPrice = 0.0;
                              _maxPrice = 500.0;
                              _minDiscount = 0.0;
                              _maxDiscount = 100.0;
                              _sortBy = 'Pertinence';
                            });
                          },
                          child: const Text('Réinitialiser'),
                        ),
                      ],
                    ),
                    const SizedBox(height: 20),

                    // Category Filter
                    const Text(
                      'Catégorie',
                      style:
                          TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
                    ),
                    const SizedBox(height: 10),
                    Wrap(
                      spacing: 8,
                      children:
                          ['Tous', ..._offerService.categories].map((category) {
                        final isSelected = _selectedCategory == category;
                        return FilterChip(
                          label: Text(category),
                          selected: isSelected,
                          onSelected: (selected) {
                            setModalState(() {
                              _selectedCategory = category;
                            });
                          },
                          selectedColor: Colors.blue[100],
                          checkmarkColor: Colors.blue[600],
                        );
                      }).toList(),
                    ),

                    const SizedBox(height: 20),

                    // Price Range
                    const Text(
                      'Prix (€)',
                      style:
                          TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
                    ),
                    const SizedBox(height: 10),
                    RangeSlider(
                      values: RangeValues(_minPrice, _maxPrice),
                      min: 0,
                      max: 500,
                      divisions: 50,
                      labels: RangeLabels(
                        '${_minPrice.toInt()}€',
                        '${_maxPrice.toInt()}€',
                      ),
                      onChanged: (values) {
                        setModalState(() {
                          _minPrice = values.start;
                          _maxPrice = values.end;
                        });
                      },
                    ),

                    const SizedBox(height: 20),

                    // Discount Range
                    const Text(
                      'Remise (%)',
                      style:
                          TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
                    ),
                    const SizedBox(height: 10),
                    RangeSlider(
                      values: RangeValues(_minDiscount, _maxDiscount),
                      min: 0,
                      max: 100,
                      divisions: 20,
                      labels: RangeLabels(
                        '${_minDiscount.toInt()}%',
                        '${_maxDiscount.toInt()}%',
                      ),
                      onChanged: (values) {
                        setModalState(() {
                          _minDiscount = values.start;
                          _maxDiscount = values.end;
                        });
                      },
                    ),

                    const SizedBox(height: 20),

                    // Sort Options
                    const Text(
                      'Trier par',
                      style:
                          TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
                    ),
                    const SizedBox(height: 10),
                    DropdownButtonFormField<String>(
                      value: _sortBy,
                      decoration: const InputDecoration(
                        border: OutlineInputBorder(),
                        contentPadding:
                            EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      ),
                      items: [
                        'Pertinence',
                        'Prix croissant',
                        'Prix décroissant',
                        'Remise croissante',
                        'Remise décroissante',
                        'Nouveautés',
                        'Populaires',
                        'Expiration proche',
                      ].map((String value) {
                        return DropdownMenuItem<String>(
                          value: value,
                          child: Text(value),
                        );
                      }).toList(),
                      onChanged: (value) {
                        setModalState(() {
                          _sortBy = value!;
                        });
                      },
                    ),
                  ],
                ),
              ),
              const Spacer(),
              Padding(
                padding: const EdgeInsets.all(20),
                child: SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () {
                      _filterOffers();
                      Navigator.pop(context);
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.blue[600],
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    child: const Text(
                      'Appliquer les filtres',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                        color: Colors.white,
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: const Text(
          'Offres & Promotions',
          style: TextStyle(
            fontWeight: FontWeight.bold,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.blue[600],
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.filter_list, color: Colors.white),
            onPressed: _showFilterDialog,
          ),
        ],
      ),
      body: Column(
        children: [
          // Search Bar
          Container(
            color: Colors.blue[600],
            padding: const EdgeInsets.all(16),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Rechercher une offre...',
                prefixIcon: const Icon(Icons.search, color: Colors.grey),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear, color: Colors.grey),
                        onPressed: () {
                          _searchController.clear();
                          _filterOffers();
                        },
                      )
                    : null,
                filled: true,
                fillColor: Colors.white,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide.none,
                ),
                contentPadding:
                    const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              ),
              onChanged: (value) {
                setState(() {
                  _isSearching = value.isNotEmpty;
                });
                _filterOffers();
              },
            ),
          ),

          // Tab Bar
          Container(
            color: Colors.white,
            child: TabBar(
              controller: _tabController,
              labelColor: Colors.blue[600],
              unselectedLabelColor: Colors.grey[600],
              indicatorColor: Colors.blue[600],
              indicatorWeight: 3,
              tabs: const [
                Tab(text: 'Toutes'),
                Tab(text: 'Nouveautés'),
                Tab(text: 'Populaires'),
                Tab(text: 'Expiration'),
              ],
              onTap: (index) {
                setState(() {
                  switch (index) {
                    case 0:
                      _filteredOffers = _offerService.activeOffers;
                      break;
                    case 1:
                      _filteredOffers = _offerService.newOffers;
                      break;
                    case 2:
                      _filteredOffers = _offerService.popularOffers;
                      break;
                    case 3:
                      _filteredOffers = _offerService.getExpiringOffers();
                      break;
                  }
                });
              },
            ),
          ),

          // Results Count
          Container(
            color: Colors.white,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  '${_filteredOffers.length} offre${_filteredOffers.length > 1 ? 's' : ''} trouvée${_filteredOffers.length > 1 ? 's' : ''}',
                  style: TextStyle(
                    color: Colors.grey[600],
                    fontWeight: FontWeight.w500,
                  ),
                ),
                if (_filteredOffers.isNotEmpty)
                  Text(
                    'Économie moyenne: ${_offerService.getAverageDiscount().toStringAsFixed(0)}%',
                    style: TextStyle(
                      color: Colors.green[600],
                      fontWeight: FontWeight.w600,
                    ),
                  ),
              ],
            ),
          ),

          // Offers List
          Expanded(
            child: _filteredOffers.isEmpty
                ? _buildEmptyState()
                : ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: _filteredOffers.length,
                    itemBuilder: (context, index) {
                      final offer = _filteredOffers[index];
                      return Padding(
                        padding: const EdgeInsets.only(bottom: 16),
                        child: OfferCardWidget(
                          offer: offer,
                          onTap: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (context) =>
                                    OfferDetailScreen(offer: offer),
                              ),
                            );
                          },
                        ),
                      );
                    },
                  ),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.local_offer_outlined,
            size: 80,
            color: Colors.grey[400],
          ),
          const SizedBox(height: 16),
          Text(
            _isSearching ? 'Aucune offre trouvée' : 'Aucune offre disponible',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w600,
              color: Colors.grey[600],
            ),
          ),
          const SizedBox(height: 8),
          Text(
            _isSearching
                ? 'Essayez de modifier vos critères de recherche'
                : 'Revenez bientôt pour découvrir nos nouvelles offres',
            style: TextStyle(
              fontSize: 14,
              color: Colors.grey[500],
            ),
            textAlign: TextAlign.center,
          ),
          if (_isSearching) ...[
            const SizedBox(height: 20),
            ElevatedButton(
              onPressed: () {
                _searchController.clear();
                _filterOffers();
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.blue[600],
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
              child: const Text(
                'Effacer la recherche',
                style: TextStyle(color: Colors.white),
              ),
            ),
          ],
        ],
      ),
    );
  }
}
