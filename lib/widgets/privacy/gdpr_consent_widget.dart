import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../services/analytics_service.dart';

class GDPRConsentWidget extends StatefulWidget {
  const GDPRConsentWidget({super.key});

  @override
  State<GDPRConsentWidget> createState() => _GDPRConsentWidgetState();
}

class _GDPRConsentWidgetState extends State<GDPRConsentWidget> {
  final AnalyticsService _analytics = AnalyticsService.instance;

  bool _essentialCookies = true; // Always true, cannot be disabled
  bool _analyticsCookies = false;
  bool _marketingCookies = false;
  bool _preferencesCookies = false;
  bool _isLoading = false;

  @override
  Widget build(BuildContext context) {
    return Dialog(
      backgroundColor: Colors.transparent,
      child: Container(
        margin: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.grey[900],
          borderRadius: BorderRadius.circular(20),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.5),
              blurRadius: 20,
              offset: const Offset(0, 10),
            ),
          ],
        ),
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header
              _buildHeader(),
              const SizedBox(height: 20),

              // Description
              _buildDescription(),
              const SizedBox(height: 24),

              // Cookie categories
              _buildCookieCategories(),
              const SizedBox(height: 24),

              // Learn more
              _buildLearnMore(),
              const SizedBox(height: 24),

              // Action buttons
              _buildActionButtons(),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return Row(
      children: [
        Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: const Color(0xFFE9D7C2),
            borderRadius: BorderRadius.circular(12),
          ),
          child: const Icon(Icons.privacy_tip, color: Colors.white, size: 24),
        ),
        const SizedBox(width: 16),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Respect de votre vie privée',
                style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                  color: Colors.white,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                'Bold Beauty Lounge',
                style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                  color: const Color(0xFFE9D7C2),
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildDescription() {
    return Text(
      'Nous utilisons des cookies et technologies similaires pour améliorer votre expérience, analyser l\'utilisation de notre application et personnaliser le contenu.',
      style: Theme.of(
        context,
      ).textTheme.bodyMedium?.copyWith(color: Colors.grey[300], height: 1.5),
    );
  }

  Widget _buildCookieCategories() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Types de cookies utilisés',
          style: Theme.of(context).textTheme.titleMedium?.copyWith(
            color: Colors.white,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: 16),

        // Essential cookies
        _buildCookieCategory(
          title: 'Cookies essentiels',
          description: 'Nécessaires au fonctionnement de l\'application',
          isEnabled: _essentialCookies,
          onChanged: null, // Cannot be disabled
          isRequired: true,
        ),
        const SizedBox(height: 12),

        // Analytics cookies
        _buildCookieCategory(
          title: 'Cookies analytiques',
          description: 'Nous aident à comprendre comment vous utilisez l\'app',
          isEnabled: _analyticsCookies,
          onChanged: (value) => setState(() => _analyticsCookies = value),
          isRequired: false,
        ),
        const SizedBox(height: 12),

        // Marketing cookies
        _buildCookieCategory(
          title: 'Cookies marketing',
          description: 'Pour personnaliser les publicités et offres',
          isEnabled: _marketingCookies,
          onChanged: (value) => setState(() => _marketingCookies = value),
          isRequired: false,
        ),
        const SizedBox(height: 12),

        // Preferences cookies
        _buildCookieCategory(
          title: 'Cookies de préférences',
          description: 'Mémorisent vos choix (thème, langue, etc.)',
          isEnabled: _preferencesCookies,
          onChanged: (value) => setState(() => _preferencesCookies = value),
          isRequired: false,
        ),
      ],
    );
  }

  Widget _buildCookieCategory({
    required String title,
    required String description,
    required bool isEnabled,
    required ValueChanged<bool>? onChanged,
    required bool isRequired,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.grey[800],
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: isRequired
              ? const Color(0xFFE9D7C2).withOpacity(0.3)
              : Colors.grey[700]!,
          width: 1,
        ),
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Text(
                      title,
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w500,
                        fontSize: 16,
                      ),
                    ),
                    if (isRequired) ...[
                      const SizedBox(width: 8),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 2,
                        ),
                        decoration: BoxDecoration(
                          color: const Color(0xFFE9D7C2),
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: const Text(
                          'Requis',
                          style: TextStyle(
                            color: Colors.black,
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ],
                  ],
                ),
                const SizedBox(height: 4),
                Text(
                  description,
                  style: TextStyle(color: Colors.grey[400], fontSize: 14),
                ),
              ],
            ),
          ),
          const SizedBox(width: 16),
          Switch(
            value: isEnabled,
            onChanged: onChanged,
            activeColor: const Color(0xFFE9D7C2),
          ),
        ],
      ),
    );
  }

  Widget _buildLearnMore() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.grey[800],
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        children: [
          const Icon(Icons.info_outline, color: Color(0xFFE9D7C2), size: 20),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              'Vous pouvez modifier vos préférences à tout moment dans les paramètres de l\'application.',
              style: TextStyle(color: Colors.grey[300], fontSize: 14),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildActionButtons() {
    return Row(
      children: [
        Expanded(
          child: OutlinedButton(
            onPressed: _isLoading ? null : _rejectAll,
            style: OutlinedButton.styleFrom(
              foregroundColor: Colors.grey[400],
              side: BorderSide(color: Colors.grey[600]!),
              padding: const EdgeInsets.symmetric(vertical: 12),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(8),
              ),
            ),
            child: const Text('Refuser tout'),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: ElevatedButton(
            onPressed: _isLoading ? null : _acceptAll,
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFE9D7C2),
              foregroundColor: Colors.black,
              padding: const EdgeInsets.symmetric(vertical: 12),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(8),
              ),
            ),
            child: _isLoading
                ? const SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(
                      strokeWidth: 2,
                      valueColor: AlwaysStoppedAnimation<Color>(Colors.black),
                    ),
                  )
                : const Text('Accepter tout'),
          ),
        ),
      ],
    );
  }

  Future<void> _acceptAll() async {
    setState(() => _isLoading = true);

    try {
      await _saveConsentPreferences(true, true, true, true);
      _analytics.logEvent('gdpr_consent_accepted', {
        'essential': true,
        'analytics': true,
        'marketing': true,
        'preferences': true,
        'timestamp': DateTime.now().millisecondsSinceEpoch,
      });

      if (mounted) {
        Navigator.of(context).pop(true);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Erreur: $e'), backgroundColor: Colors.red),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  Future<void> _rejectAll() async {
    setState(() => _isLoading = true);

    try {
      await _saveConsentPreferences(true, false, false, false);
      _analytics.logEvent('gdpr_consent_rejected', {
        'essential': true,
        'analytics': false,
        'marketing': false,
        'preferences': false,
        'timestamp': DateTime.now().millisecondsSinceEpoch,
      });

      if (mounted) {
        Navigator.of(context).pop(false);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Erreur: $e'), backgroundColor: Colors.red),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  Future<void> _saveConsentPreferences(
    bool essential,
    bool analytics,
    bool marketing,
    bool preferences,
  ) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('gdpr_consent_given', true);
    await prefs.setBool('gdpr_essential_cookies', essential);
    await prefs.setBool('gdpr_analytics_cookies', analytics);
    await prefs.setBool('gdpr_marketing_cookies', marketing);
    await prefs.setBool('gdpr_preferences_cookies', preferences);
    await prefs.setInt(
      'gdpr_consent_timestamp',
      DateTime.now().millisecondsSinceEpoch,
    );
  }

  static Future<bool> shouldShowConsent() async {
    final prefs = await SharedPreferences.getInstance();
    return !prefs.getBool('gdpr_consent_given') ?? true;
  }

  static Future<void> resetConsent() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('gdpr_consent_given');
    await prefs.remove('gdpr_essential_cookies');
    await prefs.remove('gdpr_analytics_cookies');
    await prefs.remove('gdpr_marketing_cookies');
    await prefs.remove('gdpr_preferences_cookies');
    await prefs.remove('gdpr_consent_timestamp');
  }
}









