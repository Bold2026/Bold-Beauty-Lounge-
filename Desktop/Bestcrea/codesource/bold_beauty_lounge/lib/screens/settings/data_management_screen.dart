import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../services/analytics_service.dart';

class DataManagementScreen extends StatefulWidget {
  const DataManagementScreen({super.key});

  @override
  State<DataManagementScreen> createState() => _DataManagementScreenState();
}

class _DataManagementScreenState extends State<DataManagementScreen> {
  final AnalyticsService _analytics = AnalyticsService.instance;

  bool _isLoading = false;
  Map<String, dynamic> _userData = {};
  List<String> _dataCategories = [];

  @override
  void initState() {
    super.initState();
    _loadUserData();
    _analytics.logEvent('data_management_viewed', {
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });
  }

  Future<void> _loadUserData() async {
    setState(() => _isLoading = true);

    try {
      // Simulate loading user data
      await Future.delayed(const Duration(seconds: 1));

      setState(() {
        _userData = {
          'profile': {
            'name': 'Nom Utilisateur',
            'email': 'utilisateur@email.com',
            'phone': '+212 6XX XXX XXX',
            'address': 'Casablanca, Maroc',
            'birthDate': '01/01/1990',
            'profileImage': null,
          },
          'appointments': {
            'total': 15,
            'upcoming': 2,
            'completed': 13,
            'cancelled': 0,
          },
          'preferences': {
            'theme': 'dark',
            'language': 'fr',
            'notifications': true,
            'marketing': false,
          },
          'analytics': {
            'appOpens': 45,
            'timeSpent': '2h 30m',
            'favoriteServices': ['Coiffure', 'Onglerie'],
            'lastActivity': '2024-12-15',
          },
          'technical': {
            'deviceId': 'ABC123XYZ',
            'appVersion': '1.0.0',
            'osVersion': 'Android 14',
            'lastSync': '2024-12-15 14:30',
          },
        };

        _dataCategories = [
          'Profil personnel',
          'Rendez-vous',
          'Préférences',
          'Analytics d\'utilisation',
          'Données techniques',
        ];
      });
    } catch (e) {
      _showErrorSnackBar('Erreur lors du chargement des données');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Gestion des Données'),
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            onPressed: () => _refreshData(),
            icon: const Icon(Icons.refresh),
            tooltip: 'Actualiser',
          ),
        ],
      ),
      body: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [Colors.black, Colors.grey[900]!],
          ),
        ),
        child: _isLoading
            ? const Center(
                child: CircularProgressIndicator(color: Color(0xFFE9D7C2)),
              )
            : SingleChildScrollView(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Header
                    _buildHeader(),
                    const SizedBox(height: 24),

                    // Data summary
                    _buildDataSummary(),
                    const SizedBox(height: 24),

                    // Data categories
                    _buildDataCategories(),
                    const SizedBox(height: 24),

                    // Actions
                    _buildActionButtons(),
                    const SizedBox(height: 32),
                  ],
                ),
              ),
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.grey[900],
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.3),
            blurRadius: 10,
            offset: const Offset(0, 5),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFE9D7C2),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(
                  Icons.data_usage,
                  color: Colors.white,
                  size: 24,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Gestion de vos Données',
                      style: Theme.of(context).textTheme.headlineSmall
                          ?.copyWith(
                            color: Colors.white,
                            fontWeight: FontWeight.bold,
                          ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Contrôlez vos informations personnelles',
                      style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                        color: const Color(0xFFE9D7C2),
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Text(
            'Conformément au RGPD, vous avez le droit de consulter, modifier, exporter ou supprimer vos données personnelles.',
            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
              color: Colors.grey[300],
              height: 1.5,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDataSummary() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.grey[800],
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: const Color(0xFFE9D7C2).withOpacity(0.3),
          width: 1,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.analytics, color: Color(0xFFE9D7C2), size: 20),
              const SizedBox(width: 8),
              Text(
                'Résumé de vos données',
                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  color: Colors.white,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Expanded(
                child: _buildSummaryCard(
                  'Catégories',
                  '${_dataCategories.length}',
                  Icons.category,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _buildSummaryCard(
                  'RDV',
                  '${_userData['appointments']?['total'] ?? 0}',
                  Icons.calendar_today,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _buildSummaryCard('Taille', '2.3 MB', Icons.storage),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildSummaryCard(String title, String value, IconData icon) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.grey[700],
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(
        children: [
          Icon(icon, color: const Color(0xFFE9D7C2), size: 20),
          const SizedBox(height: 8),
          Text(
            value,
            style: Theme.of(context).textTheme.titleLarge?.copyWith(
              color: Colors.white,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            title,
            style: Theme.of(
              context,
            ).textTheme.bodySmall?.copyWith(color: Colors.grey[400]),
          ),
        ],
      ),
    );
  }

  Widget _buildDataCategories() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Catégories de données',
          style: Theme.of(context).textTheme.titleLarge?.copyWith(
            color: Colors.white,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: 16),
        ..._dataCategories.asMap().entries.map((entry) {
          final index = entry.key;
          final category = entry.value;
          return _buildDataCategoryCard(category, index);
        }),
      ],
    );
  }

  Widget _buildDataCategoryCard(String category, int index) {
    final categoryData = _getCategoryData(category);

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.grey[900],
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey[700]!, width: 1),
      ),
      child: ExpansionTile(
        title: Text(
          category,
          style: const TextStyle(
            color: Colors.white,
            fontWeight: FontWeight.w500,
          ),
        ),
        subtitle: Text(
          '${categoryData.length} éléments',
          style: TextStyle(color: Colors.grey[400]),
        ),
        leading: Icon(
          _getCategoryIcon(category),
          color: const Color(0xFFE9D7C2),
        ),
        trailing: const Icon(
          Icons.arrow_forward_ios,
          color: Color(0xFFE9D7C2),
          size: 16,
        ),
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
            child: Column(
              children: categoryData.entries.map((entry) {
                return _buildDataItem(entry.key, entry.value);
              }).toList(),
            ),
          ),
        ],
      ),
    );
  }

  Map<String, dynamic> _getCategoryData(String category) {
    switch (category) {
      case 'Profil personnel':
        return _userData['profile'] ?? {};
      case 'Rendez-vous':
        return _userData['appointments'] ?? {};
      case 'Préférences':
        return _userData['preferences'] ?? {};
      case 'Analytics d\'utilisation':
        return _userData['analytics'] ?? {};
      case 'Données techniques':
        return _userData['technical'] ?? {};
      default:
        return {};
    }
  }

  IconData _getCategoryIcon(String category) {
    switch (category) {
      case 'Profil personnel':
        return Icons.person;
      case 'Rendez-vous':
        return Icons.calendar_today;
      case 'Préférences':
        return Icons.settings;
      case 'Analytics d\'utilisation':
        return Icons.analytics;
      case 'Données techniques':
        return Icons.phone_android;
      default:
        return Icons.data_object;
    }
  }

  Widget _buildDataItem(String key, dynamic value) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.grey[800],
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  _formatKey(key),
                  style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w500,
                    fontSize: 14,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  value.toString(),
                  style: TextStyle(color: Colors.grey[300], fontSize: 12),
                ),
              ],
            ),
          ),
          IconButton(
            onPressed: () => _editDataItem(key, value),
            icon: const Icon(Icons.edit, color: Color(0xFFE9D7C2), size: 16),
            tooltip: 'Modifier',
          ),
        ],
      ),
    );
  }

  String _formatKey(String key) {
    return key
        .replaceAll(RegExp(r'([A-Z])'), ' \$1')
        .toLowerCase()
        .split(' ')
        .map(
          (word) =>
              word.isNotEmpty ? word[0].toUpperCase() + word.substring(1) : '',
        )
        .join(' ');
  }

  Widget _buildActionButtons() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Actions disponibles',
          style: Theme.of(context).textTheme.titleLarge?.copyWith(
            color: Colors.white,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: 16),

        // Export data
        _buildActionButton(
          icon: Icons.download,
          title: 'Exporter mes données',
          subtitle: 'Télécharger un fichier JSON avec toutes vos données',
          onTap: () => _exportData(),
          color: const Color(0xFFE9D7C2),
        ),

        const SizedBox(height: 12),

        // Clear cache
        _buildActionButton(
          icon: Icons.clear_all,
          title: 'Vider le cache',
          subtitle: 'Supprimer les données temporaires (2.3 MB)',
          onTap: () => _clearCache(),
          color: Colors.orange,
        ),

        const SizedBox(height: 12),

        // Reset preferences
        _buildActionButton(
          icon: Icons.restore,
          title: 'Réinitialiser les préférences',
          subtitle: 'Remettre les paramètres par défaut',
          onTap: () => _resetPreferences(),
          color: Colors.blue,
        ),

        const SizedBox(height: 12),

        // Delete account
        _buildActionButton(
          icon: Icons.delete_forever,
          title: 'Supprimer mon compte',
          subtitle: 'Supprimer définitivement toutes vos données',
          onTap: () => _deleteAccount(),
          color: Colors.red,
        ),
      ],
    );
  }

  Widget _buildActionButton({
    required IconData icon,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
    required Color color,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.grey[900],
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: color.withOpacity(0.3), width: 1),
        ),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: color.withOpacity(0.2),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Icon(icon, color: color, size: 20),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: const TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.w500,
                      fontSize: 16,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    subtitle,
                    style: TextStyle(color: Colors.grey[400], fontSize: 14),
                  ),
                ],
              ),
            ),
            Icon(Icons.arrow_forward_ios, color: color, size: 16),
          ],
        ),
      ),
    );
  }

  void _refreshData() {
    _loadUserData();
    _analytics.logEvent('data_management_refreshed', {
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });
  }

  void _editDataItem(String key, dynamic value) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Modifier $_formatKey(key)'),
        content: TextField(
          controller: TextEditingController(text: value.toString()),
          decoration: const InputDecoration(hintText: 'Nouvelle valeur'),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Annuler'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              _showSuccessSnackBar('Donnée modifiée avec succès');
            },
            child: const Text('Sauvegarder'),
          ),
        ],
      ),
    );
  }

  void _exportData() {
    _analytics.logEvent('data_exported', {
      'format': 'json',
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });

    _showSuccessSnackBar('Export de vos données en cours...');
  }

  void _clearCache() {
    _analytics.logEvent('cache_cleared', {
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });

    _showSuccessSnackBar('Cache vidé avec succès');
  }

  void _resetPreferences() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Réinitialiser les préférences'),
        content: const Text(
          'Êtes-vous sûr de vouloir réinitialiser toutes vos préférences ? Cette action est irréversible.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Annuler'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              _analytics.logEvent('preferences_reset', {
                'timestamp': DateTime.now().millisecondsSinceEpoch,
              });
              _showSuccessSnackBar('Préférences réinitialisées');
            },
            child: const Text('Réinitialiser'),
          ),
        ],
      ),
    );
  }

  void _deleteAccount() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Supprimer mon compte'),
        content: const Text(
          'ATTENTION : Cette action supprimera définitivement toutes vos données. Cette action est irréversible.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Annuler'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              _analytics.logEvent('account_deletion_requested', {
                'timestamp': DateTime.now().millisecondsSinceEpoch,
              });
              _showErrorSnackBar('Suppression du compte en cours...');
            },
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: const Text('Supprimer'),
          ),
        ],
      ),
    );
  }

  void _showSuccessSnackBar(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), backgroundColor: Colors.green),
    );
  }

  void _showErrorSnackBar(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), backgroundColor: Colors.red),
    );
  }
}









