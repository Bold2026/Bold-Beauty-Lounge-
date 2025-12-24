import 'package:flutter/material.dart';
import '../../models/team_member.dart';
import '../../services/team_service.dart';
import 'team_member_detail_screen.dart';
import '../../widgets/team_member_card_widget.dart';

class TeamScreen extends StatefulWidget {
  const TeamScreen({super.key});

  @override
  State<TeamScreen> createState() => _TeamScreenState();
}

class _TeamScreenState extends State<TeamScreen> with TickerProviderStateMixin {
  final TeamService _teamService = TeamService();
  final TextEditingController _searchController = TextEditingController();

  late TabController _tabController;
  String _selectedSpecialization = 'Tous';
  String _sortBy = 'Pertinence';
  int _minExperience = 0;
  double _minRating = 0.0;

  List<TeamMember> _filteredMembers = [];
  bool _isSearching = false;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    _filteredMembers = _teamService.availableMembers;
  }

  @override
  void dispose() {
    _tabController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  void _filterMembers() {
    setState(() {
      List<TeamMember> members = _teamService.availableMembers;

      // Specialization filter
      if (_selectedSpecialization != 'Tous') {
        members = members
            .where((member) => member.specialization == _selectedSpecialization)
            .toList();
      }

      // Experience filter
      members = members
          .where((member) => member.experienceYears >= _minExperience)
          .toList();

      // Rating filter
      members = members.where((member) => member.rating >= _minRating).toList();

      // Search filter
      if (_searchController.text.isNotEmpty) {
        members = _teamService.searchMembers(_searchController.text);
        members = members
            .where((member) =>
                _selectedSpecialization == 'Tous' ||
                member.specialization == _selectedSpecialization)
            .toList();
      }

      // Sort
      switch (_sortBy) {
        case 'Expérience':
          members
              .sort((a, b) => b.experienceYears.compareTo(a.experienceYears));
          break;
        case 'Note':
          members.sort((a, b) => b.rating.compareTo(a.rating));
          break;
        case 'Nom':
          members.sort((a, b) => a.name.compareTo(b.name));
          break;
        case 'Disponibilité':
          members.sort((a, b) =>
              a.isAvailable == b.isAvailable ? 0 : (a.isAvailable ? -1 : 1));
          break;
      }

      _filteredMembers = members;
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
                              _selectedSpecialization = 'Tous';
                              _minExperience = 0;
                              _minRating = 0.0;
                              _sortBy = 'Pertinence';
                            });
                          },
                          child: const Text('Réinitialiser'),
                        ),
                      ],
                    ),
                    const SizedBox(height: 20),

                    // Specialization Filter
                    const Text(
                      'Spécialisation',
                      style:
                          TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
                    ),
                    const SizedBox(height: 10),
                    Wrap(
                      spacing: 8,
                      children: ['Tous', ..._teamService.specializations]
                          .map((specialization) {
                        final isSelected =
                            _selectedSpecialization == specialization;
                        return FilterChip(
                          label: Text(specialization),
                          selected: isSelected,
                          onSelected: (selected) {
                            setModalState(() {
                              _selectedSpecialization = specialization;
                            });
                          },
                          selectedColor: Colors.blue[100],
                          checkmarkColor: Colors.blue[600],
                        );
                      }).toList(),
                    ),

                    const SizedBox(height: 20),

                    // Experience Range
                    const Text(
                      'Années d\'expérience',
                      style:
                          TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
                    ),
                    const SizedBox(height: 10),
                    Slider(
                      value: _minExperience.toDouble(),
                      min: 0,
                      max: 20,
                      divisions: 20,
                      label: '${_minExperience} ans',
                      onChanged: (value) {
                        setModalState(() {
                          _minExperience = value.round();
                        });
                      },
                    ),

                    const SizedBox(height: 20),

                    // Rating Range
                    const Text(
                      'Note minimum',
                      style:
                          TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
                    ),
                    const SizedBox(height: 10),
                    Slider(
                      value: _minRating,
                      min: 0.0,
                      max: 5.0,
                      divisions: 50,
                      label: '${_minRating.toStringAsFixed(1)} ⭐',
                      onChanged: (value) {
                        setModalState(() {
                          _minRating = value;
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
                        'Expérience',
                        'Note',
                        'Nom',
                        'Disponibilité',
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
                      _filterMembers();
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
          'Notre Équipe',
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
                hintText: 'Rechercher un membre de l\'équipe...',
                prefixIcon: const Icon(Icons.search, color: Colors.grey),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear, color: Colors.grey),
                        onPressed: () {
                          _searchController.clear();
                          _filterMembers();
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
                _filterMembers();
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
                Tab(text: 'Tous'),
                Tab(text: 'Senior'),
                Tab(text: 'Top Rated'),
                Tab(text: 'Disponibles'),
              ],
              onTap: (index) {
                setState(() {
                  switch (index) {
                    case 0:
                      _filteredMembers = _teamService.availableMembers;
                      break;
                    case 1:
                      _filteredMembers = _teamService.seniorMembers;
                      break;
                    case 2:
                      _filteredMembers = _teamService.topRatedMembers;
                      break;
                    case 3:
                      _filteredMembers = _teamService.availableMembers;
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
                  '${_filteredMembers.length} membre${_filteredMembers.length > 1 ? 's' : ''} trouvé${_filteredMembers.length > 1 ? 's' : ''}',
                  style: TextStyle(
                    color: Colors.grey[600],
                    fontWeight: FontWeight.w500,
                  ),
                ),
                if (_filteredMembers.isNotEmpty)
                  Text(
                    'Note moyenne: ${_teamService.getAverageRating().toStringAsFixed(1)}/5',
                    style: TextStyle(
                      color: Colors.blue[600],
                      fontWeight: FontWeight.w600,
                    ),
                  ),
              ],
            ),
          ),

          // Team Members List
          Expanded(
            child: _filteredMembers.isEmpty
                ? _buildEmptyState()
                : ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: _filteredMembers.length,
                    itemBuilder: (context, index) {
                      final member = _filteredMembers[index];
                      return Padding(
                        padding: const EdgeInsets.only(bottom: 16),
                        child: TeamMemberCardWidget(
                          member: member,
                          onTap: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (context) =>
                                    TeamMemberDetailScreen(member: member),
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
            Icons.people_outline,
            size: 80,
            color: Colors.grey[400],
          ),
          const SizedBox(height: 16),
          Text(
            _isSearching ? 'Aucun membre trouvé' : 'Aucun membre disponible',
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
                : 'Revenez bientôt pour découvrir notre équipe',
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
                _filterMembers();
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
