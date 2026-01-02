import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../services/theme_service.dart';
import '../../services/localization_service.dart';
import '../../services/translation_service.dart';
import '../../services/analytics_service.dart';
import 'privacy_policy_screen.dart';
import 'data_management_screen.dart';
import 'faq_screen.dart';

class AdvancedSettingsScreen extends StatefulWidget {
  const AdvancedSettingsScreen({super.key});

  @override
  State<AdvancedSettingsScreen> createState() => _AdvancedSettingsScreenState();
}

class _AdvancedSettingsScreenState extends State<AdvancedSettingsScreen> {
  final TranslationService _translation = TranslationService.instance;
  final LocalizationService _localization = LocalizationService.instance;
  final AnalyticsService _analytics = AnalyticsService.instance;

  // Settings state
  bool _notificationsEnabled = true;
  bool _appointmentReminders = true;
  bool _promotionNotifications = true;
  bool _loyaltyNotifications = true;
  bool _offlineMode = false;
  bool _autoSync = true;
  bool _highContrast = false;
  bool _analyticsEnabled = true;
  String _selectedLanguage = 'fr';
  String _selectedTheme = 'system';

  @override
  void initState() {
    super.initState();
    _loadSettings();
  }

  Future<void> _loadSettings() async {
    // Load saved settings from SharedPreferences
    // This would be implemented with actual data loading
    setState(() {
      _selectedLanguage = _localization.currentLocale.languageCode;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(_translation.translate('settings_title')),
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: Consumer<ThemeService>(
        builder: (context, themeService, child) {
          return Container(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [Colors.black, Colors.grey[900]!],
              ),
            ),
            child: ListView(
              padding: const EdgeInsets.all(16.0),
              children: [
                // Profile Section
                _buildProfileSection(),
                const SizedBox(height: 24),

                // Appearance Section
                _buildAppearanceSection(themeService),
                const SizedBox(height: 24),

                // Language Section
                _buildLanguageSection(),
                const SizedBox(height: 24),

                // Notifications Section
                _buildNotificationsSection(),
                const SizedBox(height: 24),

                // Loyalty Program Section
                _buildLoyaltySection(),
                const SizedBox(height: 24),

                // Privacy & Security Section
                _buildPrivacySection(),
                const SizedBox(height: 24),

                // Accessibility Section
                _buildAccessibilitySection(),
                const SizedBox(height: 24),

                // Data & Analytics Section
                _buildDataSection(),
                const SizedBox(height: 24),

                // Support Section
                _buildSupportSection(),
                const SizedBox(height: 24),

                // About Section
                _buildAboutSection(),
                const SizedBox(height: 32),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildProfileSection() {
    return _buildSectionCard(
      title: 'Profil',
      icon: Icons.person,
      children: [
        ListTile(
          leading: CircleAvatar(
            backgroundColor: const Color(0xFFE9D7C2),
            child: const Icon(Icons.person, color: Colors.white),
          ),
          title: const Text('Nom Utilisateur'),
          subtitle: const Text('utilisateur@email.com'),
          trailing: const Icon(Icons.arrow_forward_ios, size: 16),
          onTap: () {
            // Navigate to profile edit
          },
        ),
        const Divider(),
        ListTile(
          leading: const Icon(Icons.phone, color: Color(0xFFE9D7C2)),
          title: const Text('Téléphone'),
          subtitle: const Text('+212 6XX XXX XXX'),
          trailing: const Icon(Icons.arrow_forward_ios, size: 16),
          onTap: () {
            // Navigate to phone edit
          },
        ),
        ListTile(
          leading: const Icon(Icons.location_on, color: Color(0xFFE9D7C2)),
          title: const Text('Adresse'),
          subtitle: const Text('Casablanca, Maroc'),
          trailing: const Icon(Icons.arrow_forward_ios, size: 16),
          onTap: () {
            // Navigate to address edit
          },
        ),
      ],
    );
  }

  Widget _buildAppearanceSection(ThemeService themeService) {
    return _buildSectionCard(
      title: 'Apparence',
      icon: Icons.palette,
      children: [
        ListTile(
          leading: const Icon(Icons.dark_mode, color: Color(0xFFE9D7C2)),
          title: const Text('Mode Sombre'),
          trailing: Switch(
            value: themeService.isDarkMode,
            onChanged: (value) {
              themeService.toggleTheme();
              _analytics.logThemeChanged(themeMode: value ? 'dark' : 'light');
            },
          ),
        ),
        ListTile(
          leading: const Icon(Icons.color_lens, color: Color(0xFFE9D7C2)),
          title: const Text('Couleur d\'Accent'),
          subtitle: Text(_getAccentColorName(themeService.accentColor)),
          trailing: const Icon(Icons.arrow_forward_ios, size: 16),
          onTap: () {
            _showAccentColorPicker(themeService);
          },
        ),
        ListTile(
          leading: const Icon(Icons.text_fields, color: Color(0xFFE9D7C2)),
          title: const Text('Taille de Police'),
          subtitle: Text('${(themeService.fontSize * 100).round()}%'),
          trailing: const Icon(Icons.arrow_forward_ios, size: 16),
          onTap: () {
            _showFontSizeSlider(themeService);
          },
        ),
      ],
    );
  }

  Widget _buildLanguageSection() {
    return _buildSectionCard(
      title: 'Langue',
      icon: Icons.language,
      children: [
        ListTile(
          leading: const Icon(Icons.flag, color: Color(0xFFE9D7C2)),
          title: const Text('Langue de l\'application'),
          subtitle: Text(_getLanguageName(_selectedLanguage)),
          trailing: const Icon(Icons.arrow_forward_ios, size: 16),
          onTap: () {
            _showLanguagePicker();
          },
        ),
        ListTile(
          leading: const Icon(Icons.translate, color: Color(0xFFE9D7C2)),
          title: const Text('Détection automatique'),
          trailing: Switch(
            value: _selectedLanguage == 'auto',
            onChanged: (value) {
              setState(() {
                _selectedLanguage = value ? 'auto' : 'fr';
              });
            },
          ),
        ),
      ],
    );
  }

  Widget _buildNotificationsSection() {
    return _buildSectionCard(
      title: 'Notifications',
      icon: Icons.notifications,
      children: [
        ListTile(
          leading: const Icon(
            Icons.notifications_active,
            color: Color(0xFFE9D7C2),
          ),
          title: const Text('Notifications Push'),
          trailing: Switch(
            value: _notificationsEnabled,
            onChanged: (value) {
              setState(() {
                _notificationsEnabled = value;
              });
            },
          ),
        ),
        ListTile(
          leading: const Icon(Icons.schedule, color: Color(0xFFE9D7C2)),
          title: const Text('Rappels de RDV'),
          trailing: Switch(
            value: _appointmentReminders,
            onChanged: (value) {
              setState(() {
                _appointmentReminders = value;
              });
            },
          ),
        ),
        ListTile(
          leading: const Icon(Icons.local_offer, color: Color(0xFFE9D7C2)),
          title: const Text('Promotions'),
          trailing: Switch(
            value: _promotionNotifications,
            onChanged: (value) {
              setState(() {
                _promotionNotifications = value;
              });
            },
          ),
        ),
        ListTile(
          leading: const Icon(Icons.stars, color: Color(0xFFE9D7C2)),
          title: const Text('Programme de fidélité'),
          trailing: Switch(
            value: _loyaltyNotifications,
            onChanged: (value) {
              setState(() {
                _loyaltyNotifications = value;
              });
            },
          ),
        ),
      ],
    );
  }

  Widget _buildLoyaltySection() {
    return _buildSectionCard(
      title: 'Programme de Fidélité',
      icon: Icons.card_giftcard,
      children: [
        ListTile(
          leading: const Icon(Icons.diamond, color: Color(0xFFE9D7C2)),
          title: const Text('Niveau actuel'),
          subtitle: const Text('Gold - 2 500 points'),
          trailing: const Icon(Icons.arrow_forward_ios, size: 16),
          onTap: () {
            // Navigate to loyalty details
          },
        ),
        ListTile(
          leading: const Icon(Icons.redeem, color: Color(0xFFE9D7C2)),
          title: const Text('Récompenses disponibles'),
          subtitle: const Text('3 récompenses en attente'),
          trailing: const Icon(Icons.arrow_forward_ios, size: 16),
          onTap: () {
            // Navigate to rewards
          },
        ),
        ListTile(
          leading: const Icon(Icons.share, color: Color(0xFFE9D7C2)),
          title: const Text('Code de parrainage'),
          subtitle: const Text('BOLD2024'),
          trailing: const Icon(Icons.copy, size: 20),
          onTap: () {
            // Copy referral code
          },
        ),
      ],
    );
  }

  Widget _buildPrivacySection() {
    return _buildSectionCard(
      title: 'Confidentialité et Sécurité',
      icon: Icons.security,
      children: [
        ListTile(
          leading: const Icon(Icons.fingerprint, color: Color(0xFFE9D7C2)),
          title: const Text('Authentification biométrique'),
          trailing: Switch(
            value: false,
            onChanged: (value) {
              // Implement biometric auth
            },
          ),
        ),
        ListTile(
          leading: const Icon(Icons.data_usage, color: Color(0xFFE9D7C2)),
          title: const Text('Gestion des données'),
          subtitle: const Text('Consulter, exporter, supprimer'),
          trailing: const Icon(Icons.arrow_forward_ios, size: 16),
          onTap: () {
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (context) => const DataManagementScreen(),
              ),
            );
          },
        ),
        ListTile(
          leading: const Icon(Icons.delete_forever, color: Colors.red),
          title: const Text('Supprimer le compte'),
          trailing: const Icon(Icons.arrow_forward_ios, size: 16),
          onTap: () {
            _showDeleteAccountDialog();
          },
        ),
      ],
    );
  }

  Widget _buildAccessibilitySection() {
    return _buildSectionCard(
      title: 'Accessibilité',
      icon: Icons.accessibility,
      children: [
        ListTile(
          leading: const Icon(Icons.contrast, color: Color(0xFFE9D7C2)),
          title: const Text('Contraste élevé'),
          trailing: Switch(
            value: _highContrast,
            onChanged: (value) {
              setState(() {
                _highContrast = value;
              });
            },
          ),
        ),
        ListTile(
          leading: const Icon(Icons.volume_up, color: Color(0xFFE9D7C2)),
          title: const Text('Lecteur d\'écran'),
          trailing: Switch(
            value: false,
            onChanged: (value) {
              // Implement screen reader support
            },
          ),
        ),
        ListTile(
          leading: const Icon(Icons.gesture, color: Color(0xFFE9D7C2)),
          title: const Text('Raccourcis gestuels'),
          trailing: const Icon(Icons.arrow_forward_ios, size: 16),
          onTap: () {
            // Navigate to gesture settings
          },
        ),
      ],
    );
  }

  Widget _buildDataSection() {
    return _buildSectionCard(
      title: 'Données et Analytics',
      icon: Icons.analytics,
      children: [
        ListTile(
          leading: const Icon(Icons.offline_bolt, color: Color(0xFFE9D7C2)),
          title: const Text('Mode hors ligne'),
          trailing: Switch(
            value: _offlineMode,
            onChanged: (value) {
              setState(() {
                _offlineMode = value;
              });
            },
          ),
        ),
        ListTile(
          leading: const Icon(Icons.sync, color: Color(0xFFE9D7C2)),
          title: const Text('Synchronisation automatique'),
          trailing: Switch(
            value: _autoSync,
            onChanged: (value) {
              setState(() {
                _autoSync = value;
              });
            },
          ),
        ),
        ListTile(
          leading: const Icon(Icons.analytics, color: Color(0xFFE9D7C2)),
          title: const Text('Partage d\'analytics'),
          trailing: Switch(
            value: _analyticsEnabled,
            onChanged: (value) {
              setState(() {
                _analyticsEnabled = value;
              });
            },
          ),
        ),
        ListTile(
          leading: const Icon(Icons.storage, color: Color(0xFFE9D7C2)),
          title: const Text('Gestion du cache'),
          subtitle: const Text('2.3 MB utilisés'),
          trailing: const Icon(Icons.arrow_forward_ios, size: 16),
          onTap: () {
            _clearCache();
          },
        ),
      ],
    );
  }

  Widget _buildSupportSection() {
    return _buildSectionCard(
      title: 'Support et Aide',
      icon: Icons.help,
      children: [
        ListTile(
          leading: const Icon(Icons.help_center, color: Color(0xFFE9D7C2)),
          title: const Text('Centre d\'aide'),
          subtitle: const Text('FAQ et support'),
          trailing: const Icon(Icons.arrow_forward_ios, size: 16),
          onTap: () {
            Navigator.push(
              context,
              MaterialPageRoute(builder: (context) => const FAQScreen()),
            );
          },
        ),
        ListTile(
          leading: const Icon(Icons.chat, color: Color(0xFFE9D7C2)),
          title: const Text('Contact support'),
          trailing: const Icon(Icons.arrow_forward_ios, size: 16),
          onTap: () {
            // Navigate to support chat
          },
        ),
        ListTile(
          leading: const Icon(Icons.bug_report, color: Color(0xFFE9D7C2)),
          title: const Text('Signaler un problème'),
          trailing: const Icon(Icons.arrow_forward_ios, size: 16),
          onTap: () {
            // Navigate to bug report
          },
        ),
        ListTile(
          leading: const Icon(Icons.star, color: Color(0xFFE9D7C2)),
          title: const Text('Évaluer l\'application'),
          trailing: const Icon(Icons.arrow_forward_ios, size: 16),
          onTap: () {
            // Open app store rating
          },
        ),
      ],
    );
  }

  Widget _buildAboutSection() {
    return _buildSectionCard(
      title: 'À propos',
      icon: Icons.info,
      children: [
        ListTile(
          leading: const Icon(Icons.info_outline, color: Color(0xFFE9D7C2)),
          title: const Text('Version de l\'application'),
          subtitle: const Text('Bold Beauty Beta 1.0.0'),
        ),
        ListTile(
          leading: const Icon(Icons.update, color: Color(0xFFE9D7C2)),
          title: const Text('Mise à jour disponible'),
          subtitle: const Text('Version 1.0.1 disponible'),
          trailing: const Icon(Icons.arrow_forward_ios, size: 16),
          onTap: () {
            // Navigate to update
          },
        ),
        ListTile(
          leading: const Icon(Icons.privacy_tip, color: Color(0xFFE9D7C2)),
          title: const Text('Politique de confidentialité'),
          subtitle: const Text('Lire notre politique complète'),
          trailing: const Icon(Icons.arrow_forward_ios, size: 16),
          onTap: () {
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (context) => const PrivacyPolicyScreen(),
              ),
            );
          },
        ),
        ListTile(
          leading: const Icon(Icons.description, color: Color(0xFFE9D7C2)),
          title: const Text('Conditions d\'utilisation'),
          trailing: const Icon(Icons.arrow_forward_ios, size: 16),
          onTap: () {
            // Open terms of service
          },
        ),
      ],
    );
  }

  Widget _buildSectionCard({
    required String title,
    required IconData icon,
    required List<Widget> children,
  }) {
    return Card(
      color: Colors.grey[900],
      elevation: 4,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.all(16.0),
            child: Row(
              children: [
                Icon(icon, color: const Color(0xFFE9D7C2), size: 24),
                const SizedBox(width: 12),
                Text(
                  title,
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
              ],
            ),
          ),
          ...children,
        ],
      ),
    );
  }

  String _getAccentColorName(Color color) {
    if (color == const Color(0xFFE9D7C2)) return 'Beige (défaut)';
    if (color == Colors.blue) return 'Bleu';
    if (color == Colors.green) return 'Vert';
    if (color == Colors.purple) return 'Violet';
    return 'Personnalisé';
  }

  String _getLanguageName(String code) {
    switch (code) {
      case 'fr':
        return 'Français';
      case 'ar':
        return 'العربية';
      case 'en':
        return 'English';
      case 'auto':
        return 'Automatique';
      default:
        return 'Français';
    }
  }

  void _showAccentColorPicker(ThemeService themeService) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Couleur d\'Accent'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            _buildColorOption(themeService, const Color(0xFFE9D7C2), 'Beige'),
            _buildColorOption(themeService, Colors.blue, 'Bleu'),
            _buildColorOption(themeService, Colors.green, 'Vert'),
            _buildColorOption(themeService, Colors.purple, 'Violet'),
          ],
        ),
      ),
    );
  }

  Widget _buildColorOption(
    ThemeService themeService,
    Color color,
    String name,
  ) {
    return ListTile(
      leading: CircleAvatar(backgroundColor: color),
      title: Text(name),
      trailing: themeService.accentColor == color
          ? const Icon(Icons.check, color: Color(0xFFE9D7C2))
          : null,
      onTap: () {
        themeService.setAccentColor(color);
        Navigator.pop(context);
      },
    );
  }

  void _showFontSizeSlider(ThemeService themeService) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Taille de Police'),
        content: StatefulBuilder(
          builder: (context, setState) => Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text('${(themeService.fontSize * 100).round()}%'),
              Slider(
                value: themeService.fontSize,
                min: 0.8,
                max: 1.4,
                divisions: 6,
                onChanged: (value) {
                  setState(() {
                    themeService.setFontSize(value);
                  });
                },
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('OK'),
          ),
        ],
      ),
    );
  }

  void _showLanguagePicker() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Langue'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            _buildLanguageOption('fr', 'Français', '🇫🇷'),
            _buildLanguageOption('ar', 'العربية', '🇲🇦'),
            _buildLanguageOption('en', 'English', '🇺🇸'),
            _buildLanguageOption('auto', 'Automatique', '🌍'),
          ],
        ),
      ),
    );
  }

  Widget _buildLanguageOption(String code, String name, String flag) {
    return ListTile(
      leading: Text(flag, style: const TextStyle(fontSize: 24)),
      title: Text(name),
      trailing: _selectedLanguage == code
          ? const Icon(Icons.check, color: Color(0xFFE9D7C2))
          : null,
      onTap: () {
        setState(() {
          _selectedLanguage = code;
        });
        _localization.setLocale(Locale(code));
        Navigator.pop(context);
      },
    );
  }

  void _showDeleteAccountDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Supprimer le compte'),
        content: const Text(
          'Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Annuler'),
          ),
          TextButton(
            onPressed: () {
              // Implement account deletion
              Navigator.pop(context);
            },
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: const Text('Supprimer'),
          ),
        ],
      ),
    );
  }

  void _clearCache() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Vider le cache'),
        content: const Text('Voulez-vous vider le cache de l\'application ?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Annuler'),
          ),
          TextButton(
            onPressed: () {
              // Implement cache clearing
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Cache vidé avec succès')),
              );
            },
            child: const Text('Vider'),
          ),
        ],
      ),
    );
  }
}
