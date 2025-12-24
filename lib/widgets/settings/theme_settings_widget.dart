import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../services/theme_service.dart';
import '../../services/analytics_service.dart';

class ThemeSettingsWidget extends StatefulWidget {
  const ThemeSettingsWidget({super.key});

  @override
  State<ThemeSettingsWidget> createState() => _ThemeSettingsWidgetState();
}

class _ThemeSettingsWidgetState extends State<ThemeSettingsWidget> {
  final AnalyticsService _analytics = AnalyticsService.instance;

  // Predefined accent colors
  final List<Color> _accentColors = [
    const Color(0xFFE9D7C2), // Beige (default)
    Colors.blue,
    Colors.green,
    Colors.purple,
    Colors.pink,
    Colors.orange,
    Colors.teal,
    Colors.indigo,
    Colors.red,
    Colors.amber,
    Colors.cyan,
    Colors.deepOrange,
  ];

  final List<String> _colorNames = [
    'Beige',
    'Bleu',
    'Vert',
    'Violet',
    'Rose',
    'Orange',
    'Sarcelle',
    'Indigo',
    'Rouge',
    'Ambre',
    'Cyan',
    'Orange foncé',
  ];

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
                      Icons.palette,
                      color: Color(0xFFE9D7C2),
                      size: 24,
                    ),
                    const SizedBox(width: 12),
                    Text(
                      'Apparence',
                      style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const Spacer(),
                    IconButton(
                      onPressed: () {
                        // Navigate to full theme settings
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

              // Theme Settings
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                child: Column(
                  children: [
                    // Dark mode toggle
                    _buildThemeRow(
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
                        activeColor: const Color(0xFFE9D7C2),
                      ),
                    ),

                    const Divider(color: Colors.grey),

                    // Accent color
                    _buildThemeRow(
                      icon: Icons.color_lens,
                      title: 'Couleur d\'Accent',
                      subtitle: _getAccentColorName(themeService.accentColor),
                      trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                      onTap: () => _showAccentColorPicker(themeService),
                    ),

                    const Divider(color: Colors.grey),

                    // Font size
                    _buildThemeRow(
                      icon: Icons.text_fields,
                      title: 'Taille de Police',
                      subtitle: '${(themeService.fontSize * 100).round()}%',
                      trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                      onTap: () => _showFontSizeSlider(themeService),
                    ),

                    const Divider(color: Colors.grey),

                    // Theme preview
                    _buildThemePreview(themeService),
                  ],
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildThemeRow({
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

  Widget _buildThemePreview(ThemeService themeService) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.grey[800],
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.preview, color: Color(0xFFE9D7C2), size: 20),
              const SizedBox(width: 8),
              Text(
                'Aperçu du thème',
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 16,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),

          // Preview content
          Row(
            children: [
              // Sample card
              Expanded(
                child: Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: themeService.isDarkMode
                        ? Colors.grey[700]
                        : Colors.white,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(
                      color: themeService.accentColor.withOpacity(0.3),
                      width: 1,
                    ),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Container(
                        width: double.infinity,
                        height: 40,
                        decoration: BoxDecoration(
                          color: themeService.accentColor,
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: const Center(
                          child: Text(
                            'Service',
                            style: TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'Exemple de service',
                        style: TextStyle(
                          color: themeService.isDarkMode
                              ? Colors.white
                              : Colors.black,
                          fontSize: 14 * themeService.fontSize,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Description du service',
                        style: TextStyle(
                          color: themeService.isDarkMode
                              ? Colors.grey[300]
                              : Colors.grey[600],
                          fontSize: 12 * themeService.fontSize,
                        ),
                      ),
                    ],
                  ),
                ),
              ),

              const SizedBox(width: 12),

              // Sample button
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 8,
                ),
                decoration: BoxDecoration(
                  color: themeService.accentColor,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  'Bouton',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 12 * themeService.fontSize,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  String _getAccentColorName(Color color) {
    final index = _accentColors.indexOf(color);
    return index >= 0 ? _colorNames[index] : 'Personnalisé';
  }

  void _showAccentColorPicker(ThemeService themeService) {
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
              'Choisir la couleur d\'accent',
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                color: Colors.white,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 20),

            // Color grid
            GridView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 4,
                crossAxisSpacing: 12,
                mainAxisSpacing: 12,
                childAspectRatio: 1,
              ),
              itemCount: _accentColors.length,
              itemBuilder: (context, index) {
                final color = _accentColors[index];
                final isSelected = themeService.accentColor == color;

                return GestureDetector(
                  onTap: () {
                    themeService.setAccentColor(color);
                    _analytics.logEvent('accent_color_changed', {
                      'color': _colorNames[index],
                    });
                    Navigator.pop(context);
                  },
                  child: Container(
                    decoration: BoxDecoration(
                      color: color,
                      shape: BoxShape.circle,
                      border: Border.all(
                        color: isSelected ? Colors.white : Colors.transparent,
                        width: 3,
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: color.withOpacity(0.3),
                          blurRadius: 8,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: isSelected
                        ? const Icon(Icons.check, color: Colors.white, size: 24)
                        : null,
                  ),
                );
              },
            ),

            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }

  void _showFontSizeSlider(ThemeService themeService) {
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
              'Taille de la police',
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                color: Colors.white,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 20),

            // Font size slider
            StatefulBuilder(
              builder: (context, setState) => Column(
                children: [
                  Text(
                    '${(themeService.fontSize * 100).round()}%',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 24,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 20),
                  Slider(
                    value: themeService.fontSize,
                    min: 0.8,
                    max: 1.4,
                    divisions: 6,
                    activeColor: const Color(0xFFE9D7C2),
                    onChanged: (value) {
                      setState(() {
                        themeService.setFontSize(value);
                      });
                    },
                  ),
                  const SizedBox(height: 20),

                  // Preview text
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.grey[800],
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Column(
                      children: [
                        Text(
                          'Texte de démonstration',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 16 * themeService.fontSize,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          'Ceci est un exemple de texte avec la taille de police sélectionnée.',
                          style: TextStyle(
                            color: Colors.grey[300],
                            fontSize: 14 * themeService.fontSize,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }
}









