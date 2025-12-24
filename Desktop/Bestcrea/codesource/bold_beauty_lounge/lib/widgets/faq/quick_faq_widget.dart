import 'package:flutter/material.dart';
import '../../services/analytics_service.dart';

class QuickFAQWidget extends StatefulWidget {
  const QuickFAQWidget({super.key});

  @override
  State<QuickFAQWidget> createState() => _QuickFAQWidgetState();
}

class _QuickFAQWidgetState extends State<QuickFAQWidget> {
  final AnalyticsService _analytics = AnalyticsService.instance;

  // Top 5 most common questions
  final List<QuickFAQItem> _quickFAQs = [
    QuickFAQItem(
      question: 'Puis-je annuler mon rendez-vous ?',
      answer: 'Oui, sans frais jusqu\'à 24h avant votre RDV.',
      icon: Icons.calendar_today,
    ),
    QuickFAQItem(
      question: 'Quels moyens de paiement ?',
      answer: 'Cartes, mobile money, espèces et WhatsApp.',
      icon: Icons.payment,
    ),
    QuickFAQItem(
      question: 'Où vous trouvez-vous ?',
      answer: '31 Rue Abdessalam Aamir, Casablanca.',
      icon: Icons.location_on,
    ),
    QuickFAQItem(
      question: 'Mesures d\'hygiène ?',
      answer: 'Désinfection complète après chaque client.',
      icon: Icons.cleaning_services,
    ),
    QuickFAQItem(
      question: 'Produits certifiés ?',
      answer: 'Oui, uniquement des produits haut de gamme.',
      icon: Icons.verified,
    ),
  ];

  @override
  Widget build(BuildContext context) {
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
                  Icons.help_outline,
                  color: Color(0xFFE9D7C2),
                  size: 24,
                ),
                const SizedBox(width: 12),
                Text(
                  'Questions Fréquentes',
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    color: Colors.white,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const Spacer(),
                TextButton(
                  onPressed: () => _openFullFAQ(),
                  child: const Text(
                    'Voir tout',
                    style: TextStyle(
                      color: Color(0xFFE9D7C2),
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ),
              ],
            ),
          ),

          // Quick FAQs
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
            child: Column(
              children: _quickFAQs.asMap().entries.map((entry) {
                final index = entry.key;
                final faq = entry.value;
                return _buildQuickFAQItem(faq, index);
              }).toList(),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildQuickFAQItem(QuickFAQItem faq, int index) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      decoration: BoxDecoration(
        color: Colors.grey[800],
        borderRadius: BorderRadius.circular(8),
      ),
      child: ExpansionTile(
        tilePadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
        childrenPadding: const EdgeInsets.fromLTRB(12, 0, 12, 12),
        leading: Icon(faq.icon, color: const Color(0xFFE9D7C2), size: 20),
        title: Text(
          faq.question,
          style: const TextStyle(
            color: Colors.white,
            fontWeight: FontWeight.w500,
            fontSize: 14,
          ),
        ),
        trailing: const Icon(
          Icons.expand_more,
          color: Color(0xFFE9D7C2),
          size: 16,
        ),
        onExpansionChanged: (expanded) {
          _analytics.logEvent('quick_faq_expanded', {
            'question': faq.question,
            'expanded': expanded,
          });
        },
        children: [
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.grey[700],
              borderRadius: BorderRadius.circular(6),
            ),
            child: Text(
              faq.answer,
              style: TextStyle(
                color: Colors.grey[300],
                fontSize: 13,
                height: 1.4,
              ),
            ),
          ),
        ],
      ),
    );
  }

  void _openFullFAQ() {
    _analytics.logEvent('quick_faq_view_all', {
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });

    Navigator.pushNamed(context, '/faq');
  }
}

class QuickFAQItem {
  final String question;
  final String answer;
  final IconData icon;

  QuickFAQItem({
    required this.question,
    required this.answer,
    required this.icon,
  });
}









