import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

class PourBoldDetailsScreen extends StatelessWidget {
  const PourBoldDetailsScreen({super.key});

  static const Color _accent = Color(0xFFE9D7C2);
  static const Color _textColor = Color(0xFF1F1A17);
  static const Color _background = Color(0xFFF9F7F3);

  Future<void> _launchExternalUrl(String url) async {
    final uri = Uri.parse(url);
    if (!await launchUrl(uri, mode: LaunchMode.externalApplication)) {
      debugPrint('Impossible d\'ouvrir $url');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _background,
      appBar: AppBar(
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
        title: const Text(
          'Pour Bold',
          style: TextStyle(fontWeight: FontWeight.w700),
        ),
      ),
      body: SingleChildScrollView(
        physics: const BouncingScrollPhysics(),
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(28),
              child: Image.asset(
                'assets/boldbeautylounge.jpg',
                width: double.infinity,
                height: 220,
                fit: BoxFit.cover,
              ),
            ),
            const SizedBox(height: 24),
            _buildSectionTitle('Coordonnées essentielles'),
            const SizedBox(height: 12),
            _InfoTile(
              icon: Icons.facebook,
              label: 'Facebook',
              value: 'Bold Beauty Lounge',
              onTap: () => _launchExternalUrl(
                'https://www.facebook.com/BoldBeautyLounge',
              ),
            ),
            const SizedBox(height: 10),
            _InfoTile(
              icon: Icons.whatsapp,
              label: 'WhatsApp',
              value: '+212 619-249249',
              onTap: () => _launchExternalUrl('https://wa.me/212619249249'),
            ),
            const SizedBox(height: 10),
            _InfoTile(
              icon: Icons.phone_outlined,
              label: 'Téléphone',
              value: '+212 619-249249',
              onTap: () => _launchExternalUrl('tel:+212619249249'),
            ),
            const SizedBox(height: 10),
            _InfoTile(
              icon: Icons.public,
              label: 'Site web',
              value: 'boldbeautylounge.com',
              onTap: () => _launchExternalUrl('https://boldbeautylounge.com'),
            ),
            const SizedBox(height: 10),
            const _InfoTile(
              icon: Icons.location_on_outlined,
              label: 'Adresse',
              value: '31 Rue Abdessalam Aamir, Casablanca',
            ),
            const SizedBox(height: 28),
            _buildSectionTitle('Horaires d\'ouverture'),
            const SizedBox(height: 12),
            const _ScheduleRow(day: 'Lundi - Jeudi', hours: '10h00 - 20h00'),
            const SizedBox(height: 8),
            const _ScheduleRow(
                day: 'Vendredi - Samedi', hours: '10h00 - 21h00'),
            const SizedBox(height: 8),
            const _ScheduleRow(day: 'Dimanche', hours: '11h00 - 18h00'),
            const SizedBox(height: 28),
            _buildSectionTitle('Plan & accès'),
            const SizedBox(height: 12),
            _MapCard(onTap: () {
              _launchExternalUrl(
                'https://maps.google.com/?q=Bold+Beauty+Lounge+Casablanca',
              );
            }),
          ],
        ),
      ),
    );
  }

  static Widget _buildSectionTitle(String text) {
    return Text(
      text,
      style: const TextStyle(
        color: _textColor,
        fontSize: 20,
        fontWeight: FontWeight.w700,
      ),
    );
  }
}

class _InfoTile extends StatelessWidget {
  const _InfoTile({
    required this.icon,
    required this.label,
    required this.value,
    this.onTap,
  });

  final IconData icon;
  final String label;
  final String value;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    final bool isInteractive = onTap != null;
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(24),
      child: InkWell(
        borderRadius: BorderRadius.circular(24),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 16),
          child: Row(
            children: [
              Icon(icon, color: PourBoldDetailsScreen._accent, size: 22),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      label,
                      style: const TextStyle(
                        fontWeight: FontWeight.w700,
                        fontSize: 14,
                        color: PourBoldDetailsScreen._textColor,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      value,
                      style: TextStyle(
                        fontSize: 13,
                        color:
                            PourBoldDetailsScreen._textColor.withOpacity(0.65),
                      ),
                    ),
                  ],
                ),
              ),
              if (isInteractive)
                const Icon(Icons.arrow_forward_ios,
                    size: 16, color: Colors.black),
            ],
          ),
        ),
      ),
    );
  }
}

class _ScheduleRow extends StatelessWidget {
  const _ScheduleRow({required this.day, required this.hours});

  final String day;
  final String hours;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        const Icon(Icons.schedule,
            color: PourBoldDetailsScreen._accent, size: 18),
        const SizedBox(width: 12),
        Expanded(
          child: Text(
            '$day : $hours',
            style: const TextStyle(
              color: PourBoldDetailsScreen._textColor,
              fontSize: 14,
              fontWeight: FontWeight.w600,
            ),
          ),
        ),
      ],
    );
  }
}

class _MapCard extends StatelessWidget {
  const _MapCard({required this.onTap});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(24),
      child: InkWell(
        borderRadius: BorderRadius.circular(24),
        onTap: onTap,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            ClipRRect(
              borderRadius: const BorderRadius.only(
                topLeft: Radius.circular(24),
                topRight: Radius.circular(24),
              ),
              child: Image.asset(
                'assets/maps/bold_map_preview.jpg',
                height: 160,
                width: double.infinity,
                fit: BoxFit.cover,
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(18, 16, 18, 18),
              child: Row(
                children: [
                  const Icon(Icons.map_outlined,
                      color: PourBoldDetailsScreen._accent),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      'Voir le plan détaillé sur Google Maps',
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w600,
                        color: PourBoldDetailsScreen._textColor,
                      ),
                    ),
                  ),
                  const Icon(Icons.arrow_forward_ios,
                      size: 16, color: Colors.black),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
