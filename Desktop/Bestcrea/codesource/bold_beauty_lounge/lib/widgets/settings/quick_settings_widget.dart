import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../services/theme_service.dart';
import '../../services/localization_service.dart';
import '../../services/translation_service.dart';
import '../../services/analytics_service.dart';

class QuickSettingsWidget extends StatefulWidget {
  const QuickSettingsWidget({super.key});

  @override
  State<QuickSettingsWidget> createState() => _QuickSettingsWidgetState();
}

class _QuickSettingsWidgetState extends State<QuickSettingsWidget> {
  final TranslationService _translation = TranslationService.instance;
  final LocalizationService _localization = LocalizationService.instance;
  final AnalyticsService _analytics = AnalyticsService.instance;

  @override
  Widget build(BuildContext context) {
    return Consumer<ThemeService>(
      builder: (context, themeService, child) {
        return Container(
          margin: const EdgeInsets.all(16.0),
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
              // Header
              Padding(
                padding: const EdgeInsets.all(16.0),
                child: Row(
                  children: [
                    const Icon(
                      Icons.settings,
                      color: Color(0xFFE9D7C2),
                      size: 24,
                    ),
                    const SizedBox(width: 12),
                    Text(
                      'Paramètres Rapides',
                      style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const Spacer(),
                    IconButton(
                      onPressed: () {
                        // Navigate to full settings
                        Navigator.pushNamed(context, '/settings');
                      },
                      icon: const Icon(
                        Icons.arrow_forward_ios,
                        color: Color(0xFFE9D7C2),
                        size: 16,
                      ),
                    ),
                  ],
                ),
              ),

              // Quick Settings Grid
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                child: Column(
                  children: [
                    // Theme Row
                    _buildSettingRow(
                      icon: Icons.dark_mode,
                      title: 'Mode Sombre',
                      subtitle: themeService.isDarkMode
                          ? 'Activé'
                          : 'Désactivé',
                      trailing: Switch(
                        value: themeService.isDarkMode,
                        onChanged: (value) {
                          themeService.toggleTheme();
                          _analytics.logThemeChanged(
                            themeMode: value ? 'dark' : 'light',
                          );
                        },
                      ),
                    ),

                    const Divider(color: Colors.grey),

                    // Language Row
                    _buildSettingRow(
                      icon: Icons.language,
                      title: 'Langue',
                      subtitle: _getLanguageName(
                        _localization.currentLocale.languageCode,
                      ),
                      trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                      onTap: () => _showLanguageQuickPicker(),
                    ),

                    const Divider(color: Colors.grey),

                    // Notifications Row
                    _buildSettingRow(
                      icon: Icons.notifications,
                      title: 'Notifications',
                      subtitle: 'Gérer les alertes',
                      trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                      onTap: () {
                        // Navigate to notification settings
                      },
                    ),

                    const Divider(color: Colors.grey),

                    // Loyalty Row
                    _buildSettingRow(
                      icon: Icons.card_giftcard,
                      title: 'Fidélité',
                      subtitle: 'Gold - 2 500 pts',
                      trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                      onTap: () {
                        // Navigate to loyalty program
                      },
                    ),
                  ],
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildSettingRow({
    required IconData icon,
    required String title,
    required String subtitle,
    required Widget trailing,
    VoidCallback? onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 12.0),
        child: Row(
          children: [
            Icon(icon, color: const Color(0xFFE9D7C2), size: 24),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 16,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    subtitle,
                    style: TextStyle(color: Colors.grey[400], fontSize: 14),
                  ),
                ],
              ),
            ),
            trailing,
          ],
        ),
      ),
    );
  }

  String _getLanguageName(String code) {
    switch (code) {
      case 'fr':
        return 'Français';
      case 'ar':
        return 'العربية';
      case 'en':
        return 'English';
      default:
        return 'Français';
    }
  }

  void _showLanguageQuickPicker() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.grey[900],
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => Container(
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Handle bar
            Container(
              width: 40,
              height: 4,
              decoration: BoxDecoration(
                color: Colors.grey[600],
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            const SizedBox(height: 20),

            // Title
            Text(
              'Choisir la langue',
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                color: Colors.white,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 20),

            // Language options
            _buildLanguageOption('fr', 'Français', '🇫🇷'),
            _buildLanguageOption('ar', 'العربية', '🇲🇦'),
            _buildLanguageOption('en', 'English', '🇺🇸'),

            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }

  Widget _buildLanguageOption(String code, String name, String flag) {
    final isSelected = _localization.currentLocale.languageCode == code;

    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        leading: Text(flag, style: const TextStyle(fontSize: 24)),
        title: Text(
          name,
          style: TextStyle(
            color: isSelected ? const Color(0xFFE9D7C2) : Colors.white,
            fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
          ),
        ),
        trailing: isSelected
            ? const Icon(Icons.check, color: Color(0xFFE9D7C2))
            : null,
        onTap: () {
          _localization.setLocale(Locale(code));
          _analytics.logLanguageChanged(language: code);
          Navigator.pop(context);
        },
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(8),
          side: BorderSide(
            color: isSelected ? const Color(0xFFE9D7C2) : Colors.transparent,
            width: 1,
          ),
        ),
      ),
    );
  }
}









