import 'package:flutter/material.dart';
import '../../services/theme_service.dart';

class ThemeSettingsScreen extends StatefulWidget {
  const ThemeSettingsScreen({super.key});

  @override
  State<ThemeSettingsScreen> createState() => _ThemeSettingsScreenState();
}

class _ThemeSettingsScreenState extends State<ThemeSettingsScreen> {
  final ThemeService _themeService = ThemeService();
  late ThemeMode _currentMode;
  late Color _currentAccentColor;
  late double _currentFontSize;

  final List<Color> _accentColors = [
    const Color(0xFFE9D7C2), // Beige original
    const Color(0xFFD4AF37), // Or
    const Color(0xFFE91E63), // Rose
    const Color(0xFF9C27B0), // Violet
    const Color(0xFF673AB7), // Indigo
    const Color(0xFF3F51B5), // Bleu
    const Color(0xFF2196F3), // Bleu clair
    const Color(0xFF00BCD4), // Cyan
    const Color(0xFF009688), // Teal
    const Color(0xFF4CAF50), // Vert
    const Color(0xFF8BC34A), // Vert clair
    const Color(0xFFCDDC39), // Lime
    const Color(0xFFFFEB3B), // Jaune
    const Color(0xFFFFC107), // Amber
    const Color(0xFFFF9800), // Orange
    const Color(0xFFFF5722), // Rouge-orange
  ];

  @override
  void initState() {
    super.initState();
    _currentMode = _themeService.currentThemeMode;
    _currentAccentColor = _themeService.accentColor;
    _currentFontSize = _themeService.fontSize;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Thème et Apparence'),
        backgroundColor: Theme.of(context).colorScheme.surface,
        foregroundColor: Theme.of(context).colorScheme.onSurface,
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Mode de thème
            _buildThemeModeSection(),
            const SizedBox(height: 24),

            // Couleur d'accent
            _buildAccentColorSection(),
            const SizedBox(height: 24),

            // Taille de police
            _buildFontSizeSection(),
            const SizedBox(height: 24),

            // Aperçu du thème
            _buildThemePreview(),
            const SizedBox(height: 24),

            // Actions
            _buildActionButtons(),
          ],
        ),
      ),
    );
  }

  Widget _buildThemeModeSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Mode de thème',
          style: Theme.of(
            context,
          ).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 12),
        Card(
          child: Column(
            children: [
              RadioListTile<ThemeMode>(
                title: const Text('Clair'),
                subtitle: const Text('Thème clair par défaut'),
                value: ThemeMode.light,
                groupValue: _currentMode,
                onChanged: (value) => _updateThemeMode(value!),
              ),
              RadioListTile<ThemeMode>(
                title: const Text('Sombre'),
                subtitle: const Text('Thème sombre élégant'),
                value: ThemeMode.dark,
                groupValue: _currentMode,
                onChanged: (value) => _updateThemeMode(value!),
              ),
              RadioListTile<ThemeMode>(
                title: const Text('Système'),
                subtitle: const Text('Suivre les paramètres du système'),
                value: ThemeMode.system,
                groupValue: _currentMode,
                onChanged: (value) => _updateThemeMode(value!),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildAccentColorSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Couleur d\'accent',
          style: Theme.of(
            context,
          ).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 12),
        Card(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Wrap(
              spacing: 12,
              runSpacing: 12,
              children: _accentColors.map((color) {
                final isSelected = _currentAccentColor.value == color.value;
                return GestureDetector(
                  onTap: () => _updateAccentColor(color),
                  child: Container(
                    width: 48,
                    height: 48,
                    decoration: BoxDecoration(
                      color: color,
                      shape: BoxShape.circle,
                      border: Border.all(
                        color: isSelected ? Colors.white : Colors.transparent,
                        width: 3,
                      ),
                      boxShadow: isSelected
                          ? [
                              BoxShadow(
                                color: color.withOpacity(0.5),
                                blurRadius: 8,
                                spreadRadius: 2,
                              ),
                            ]
                          : null,
                    ),
                    child: isSelected
                        ? const Icon(Icons.check, color: Colors.white, size: 24)
                        : null,
                  ),
                );
              }).toList(),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildFontSizeSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Taille de police',
          style: Theme.of(
            context,
          ).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 12),
        Card(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                Row(
                  children: [
                    const Text('Petite'),
                    Expanded(
                      child: Slider(
                        value: _currentFontSize,
                        min: 0.8,
                        max: 1.4,
                        divisions: 12,
                        label: '${(_currentFontSize * 100).round()}%',
                        onChanged: _updateFontSize,
                      ),
                    ),
                    const Text('Grande'),
                  ],
                ),
                const SizedBox(height: 8),
                Text(
                  'Aperçu: ${(_currentFontSize * 100).round()}%',
                  style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                    fontSize: 14 * _currentFontSize,
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildThemePreview() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Aperçu du thème',
          style: Theme.of(
            context,
          ).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 12),
        Card(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                // AppBar preview
                Container(
                  height: 56,
                  decoration: BoxDecoration(
                    color: Theme.of(context).colorScheme.primary,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Center(
                    child: Text(
                      'Bold Beauty Lounge',
                      style: TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 12),

                // Button preview
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () {},
                    child: const Text('Bouton d\'exemple'),
                  ),
                ),
                const SizedBox(height: 12),

                // Text preview
                Text(
                  'Texte d\'exemple',
                  style: Theme.of(context).textTheme.bodyLarge,
                ),
                Text(
                  'Description d\'exemple',
                  style: Theme.of(context).textTheme.bodyMedium,
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildActionButtons() {
    return Row(
      children: [
        Expanded(
          child: OutlinedButton(
            onPressed: _resetTheme,
            child: const Text('Réinitialiser'),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: ElevatedButton(
            onPressed: _saveTheme,
            child: const Text('Appliquer'),
          ),
        ),
      ],
    );
  }

  void _updateThemeMode(ThemeMode mode) {
    setState(() {
      _currentMode = mode;
    });
  }

  void _updateAccentColor(Color color) {
    setState(() {
      _currentAccentColor = color;
    });
  }

  void _updateFontSize(double size) {
    setState(() {
      _currentFontSize = size;
    });
  }

  Future<void> _saveTheme() async {
    await _themeService.setThemeMode(_currentMode);
    await _themeService.setAccentColor(_currentAccentColor);
    await _themeService.setFontSize(_currentFontSize);

    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Thème appliqué avec succès'),
          backgroundColor: Colors.green,
        ),
      );
    }
  }

  Future<void> _resetTheme() async {
    await _themeService.resetTheme();
    setState(() {
      _currentMode = ThemeMode.light;
      _currentAccentColor = const Color(0xFFE9D7C2);
      _currentFontSize = 1.0;
    });

    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Thème réinitialisé'),
          backgroundColor: Colors.orange,
        ),
      );
    }
  }
}





