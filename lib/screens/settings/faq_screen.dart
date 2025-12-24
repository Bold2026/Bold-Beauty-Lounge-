import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../services/analytics_service.dart';

class FAQScreen extends StatefulWidget {
  const FAQScreen({super.key});

  @override
  State<FAQScreen> createState() => _FAQScreenState();
}

class _FAQScreenState extends State<FAQScreen> {
  final AnalyticsService _analytics = AnalyticsService.instance;
  final TextEditingController _searchController = TextEditingController();

  List<FAQItem> _allFAQs = [];
  List<FAQItem> _filteredFAQs = [];
  String _selectedCategory = 'Toutes';

  final List<String> _categories = [
    'Toutes',
    'Réservations',
    'Paiements',
    'Hygiène & Produits',
    'Localisation',
    'Général',
  ];

  @override
  void initState() {
    super.initState();
    _initializeFAQs();
    _analytics.logEvent('faq_viewed', {
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });
  }

  void _initializeFAQs() {
    _allFAQs = [
      // Réservations
      FAQItem(
        question: 'Puis-je annuler mon rendez-vous ?',
        answer:
            'Bien sûr ! Vous pouvez annuler sans frais jusqu\'à 24h avant votre rendez-vous. Pour annuler, utilisez l\'application ou contactez-nous directement.',
        category: 'Réservations',
        icon: Icons.calendar_today,
        isExpanded: false,
      ),
      FAQItem(
        question: 'Et si j\'ai un imprévu de dernière minute ?',
        answer:
            'Nous comprenons que cela peut arriver. De petits frais peuvent s\'appliquer, mais nous faisons toujours notre maximum pour trouver une solution. Contactez-nous au +212 619 249249 pour discuter de votre situation.',
        category: 'Réservations',
        icon: Icons.emergency,
        isExpanded: false,
      ),
      FAQItem(
        question: 'Êtes-vous ouverts les jours fériés ?',
        answer:
            'La plupart du temps oui ! Si nous prenons un jour spécial, nous vous informons toujours sur nos réseaux sociaux. Consultez notre page Facebook et Instagram pour les dernières informations.',
        category: 'Réservations',
        icon: Icons.holiday_village,
        isExpanded: false,
      ),
      FAQItem(
        question: 'Comment modifier mon rendez-vous ?',
        answer:
            'Vous pouvez modifier votre rendez-vous jusqu\'à 24h avant l\'heure prévue. Utilisez l\'application ou contactez-nous. Des frais de modification peuvent s\'appliquer selon le délai.',
        category: 'Réservations',
        icon: Icons.edit_calendar,
        isExpanded: false,
      ),
      FAQItem(
        question: 'Puis-je réserver pour quelqu\'un d\'autre ?',
        answer:
            'Oui, vous pouvez réserver pour un proche. Assurez-vous de fournir ses coordonnées exactes et de l\'informer de l\'heure et du service réservé.',
        category: 'Réservations',
        icon: Icons.person_add,
        isExpanded: false,
      ),

      // Paiements
      FAQItem(
        question: 'Quels moyens de paiement acceptez-vous ?',
        answer:
            'Nous acceptons les cartes bancaires (Visa, Mastercard), les paiements mobiles (Orange Money, Inwi Money, Cash Plus), les virements bancaires et les espèces. Tous nos paiements sont sécurisés.',
        category: 'Paiements',
        icon: Icons.payment,
        isExpanded: false,
      ),
      FAQItem(
        question: 'Puis-je régler via WhatsApp ?',
        answer:
            'Absolument ! Nous vous envoyons un lien sécurisé pour un paiement simple et rapide. Contactez-nous au +212 619 249249 sur WhatsApp pour recevoir votre lien de paiement.',
        category: 'Paiements',
        icon: Icons.chat,
        isExpanded: false,
      ),
      FAQItem(
        question: 'Proposez-vous des facilités de paiement ?',
        answer:
            'Oui, nous proposons des facilités de paiement pour nos forfaits et services premium. Contactez-nous pour discuter des options disponibles selon votre situation.',
        category: 'Paiements',
        icon: Icons.credit_card,
        isExpanded: false,
      ),
      FAQItem(
        question: 'Puis-je payer un acompte ?',
        answer:
            'Oui, pour certains services ou forfaits, nous acceptons un acompte de 30% à la réservation et le solde le jour du rendez-vous.',
        category: 'Paiements',
        icon: Icons.account_balance_wallet,
        isExpanded: false,
      ),

      // Hygiène & Produits
      FAQItem(
        question: 'Quelles sont vos mesures d\'hygiène ?',
        answer:
            'Chaque cabine et chaque outil est soigneusement désinfecté après chaque client. Nous utilisons des produits désinfectants hospitaliers et respectons tous les protocoles sanitaires en vigueur.',
        category: 'Hygiène & Produits',
        icon: Icons.cleaning_services,
        isExpanded: false,
      ),
      FAQItem(
        question: 'Utilisez-vous des produits certifiés ?',
        answer:
            'Oui, uniquement des produits haut de gamme, testés et approuvés pour leur efficacité et leur douceur. Tous nos produits sont certifiés et proviennent de marques reconnues dans le domaine de la beauté.',
        category: 'Hygiène & Produits',
        icon: Icons.verified,
        isExpanded: false,
      ),
      FAQItem(
        question: 'Proposez-vous des produits à la vente ?',
        answer:
            'Oui, nous vendons une sélection de produits professionnels que nous utilisons dans notre salon. Demandez à votre spécialiste pour plus d\'informations.',
        category: 'Hygiène & Produits',
        icon: Icons.shopping_bag,
        isExpanded: false,
      ),
      FAQItem(
        question: 'Avez-vous des produits pour peaux sensibles ?',
        answer:
            'Absolument ! Nous avons une gamme spéciale pour les peaux sensibles et allergiques. Informez-nous de vos allergies lors de la réservation.',
        category: 'Hygiène & Produits',
        icon: Icons.healing,
        isExpanded: false,
      ),

      // Localisation
      FAQItem(
        question: 'Où vous trouvez-vous exactement ?',
        answer:
            'Nous sommes situés au n°2, rez-de-chaussée, 31 Rue Abdessalam Aamir, Casablanca. Notre salon est facilement accessible en voiture et en transport public.',
        category: 'Localisation',
        icon: Icons.location_on,
        isExpanded: false,
      ),
      FAQItem(
        question: 'Puis-je vous trouver facilement sur Google Maps ?',
        answer:
            'Bien sûr, notre localisation est intégrée pour vous guider sans stress. Recherchez "Bold Beauty Lounge" sur Google Maps ou utilisez le lien dans l\'application.',
        category: 'Localisation',
        icon: Icons.map,
        isExpanded: false,
      ),
      FAQItem(
        question: 'Y a-t-il un parking disponible ?',
        answer:
            'Oui, nous avons un parking privé pour nos clients. Des places de stationnement sont disponibles devant le salon.',
        category: 'Localisation',
        icon: Icons.local_parking,
        isExpanded: false,
      ),
      FAQItem(
        question: 'Êtes-vous accessibles en transport public ?',
        answer:
            'Oui, nous sommes bien desservis par les bus et taxis. La station de tramway la plus proche est à 5 minutes à pied.',
        category: 'Localisation',
        icon: Icons.directions_transit,
        isExpanded: false,
      ),

      // Général
      FAQItem(
        question: 'Quels sont vos horaires d\'ouverture ?',
        answer:
            'Nous sommes ouverts du lundi au vendredi de 9h à 20h, le samedi de 9h à 19h et le dimanche de 10h à 18h. Les jours fériés, consultez nos réseaux sociaux.',
        category: 'Général',
        icon: Icons.access_time,
        isExpanded: false,
      ),
      FAQItem(
        question: 'Proposez-vous des formations ?',
        answer:
            'Oui, nous proposons des formations professionnelles en coiffure, esthétique et onglerie. Contactez-nous pour plus d\'informations sur nos programmes.',
        category: 'Général',
        icon: Icons.school,
        isExpanded: false,
      ),
      FAQItem(
        question: 'Avez-vous un programme de fidélité ?',
        answer:
            'Oui ! Notre programme de fidélité vous permet d\'accumuler des points à chaque visite et de bénéficier de réductions et d\'offres exclusives.',
        category: 'Général',
        icon: Icons.card_giftcard,
        isExpanded: false,
      ),
      FAQItem(
        question: 'Comment puis-je vous contacter ?',
        answer:
            'Vous pouvez nous contacter par téléphone au +212 619 249249, par email à contact@boldbeautylounge.com, ou via WhatsApp. Nous sommes également présents sur Facebook et Instagram.',
        category: 'Général',
        icon: Icons.contact_phone,
        isExpanded: false,
      ),
    ];

    _filteredFAQs = List.from(_allFAQs);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('FAQ'),
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            onPressed: () => _contactSupport(),
            icon: const Icon(Icons.support_agent),
            tooltip: 'Support',
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
        child: Column(
          children: [
            // Search and filter section
            _buildSearchAndFilter(),

            // FAQ content
            Expanded(
              child: _filteredFAQs.isEmpty
                  ? _buildEmptyState()
                  : ListView.builder(
                      padding: const EdgeInsets.all(16),
                      itemCount: _filteredFAQs.length,
                      itemBuilder: (context, index) {
                        return _buildFAQItem(_filteredFAQs[index], index);
                      },
                    ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSearchAndFilter() {
    return Container(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          // Search bar
          TextField(
            controller: _searchController,
            onChanged: _filterFAQs,
            decoration: InputDecoration(
              hintText: 'Rechercher dans la FAQ...',
              hintStyle: TextStyle(color: Colors.grey[400]),
              prefixIcon: const Icon(Icons.search, color: Color(0xFFE9D7C2)),
              suffixIcon: _searchController.text.isNotEmpty
                  ? IconButton(
                      onPressed: () {
                        _searchController.clear();
                        _filterFAQs('');
                      },
                      icon: const Icon(Icons.clear, color: Colors.grey),
                    )
                  : null,
              filled: true,
              fillColor: Colors.grey[800],
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide.none,
              ),
              contentPadding: const EdgeInsets.symmetric(
                horizontal: 16,
                vertical: 12,
              ),
            ),
            style: const TextStyle(color: Colors.white),
          ),

          const SizedBox(height: 16),

          // Category filter
          SizedBox(
            height: 40,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              itemCount: _categories.length,
              itemBuilder: (context, index) {
                final category = _categories[index];
                final isSelected = _selectedCategory == category;

                return Container(
                  margin: const EdgeInsets.only(right: 8),
                  child: FilterChip(
                    label: Text(category),
                    selected: isSelected,
                    onSelected: (selected) {
                      setState(() {
                        _selectedCategory = category;
                        _filterFAQs(_searchController.text);
                      });
                    },
                    selectedColor: const Color(0xFFE9D7C2),
                    checkmarkColor: Colors.black,
                    labelStyle: TextStyle(
                      color: isSelected ? Colors.black : Colors.white,
                      fontWeight: isSelected
                          ? FontWeight.bold
                          : FontWeight.normal,
                    ),
                    backgroundColor: Colors.grey[700],
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFAQItem(FAQItem faq, int index) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.grey[900],
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey[700]!, width: 1),
      ),
      child: Theme(
        data: Theme.of(context).copyWith(dividerColor: Colors.transparent),
        child: ExpansionTile(
          tilePadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          childrenPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
          leading: Icon(faq.icon, color: const Color(0xFFE9D7C2), size: 24),
          title: Text(
            faq.question,
            style: const TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.w500,
              fontSize: 16,
            ),
          ),
          subtitle: Text(
            faq.category,
            style: TextStyle(color: Colors.grey[400], fontSize: 12),
          ),
          trailing: Icon(
            faq.isExpanded ? Icons.expand_less : Icons.expand_more,
            color: const Color(0xFFE9D7C2),
          ),
          onExpansionChanged: (expanded) {
            setState(() {
              faq.isExpanded = expanded;
            });

            _analytics.logEvent('faq_expanded', {
              'question': faq.question,
              'category': faq.category,
              'expanded': expanded,
            });
          },
          children: [
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.grey[800],
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                faq.answer,
                style: TextStyle(
                  color: Colors.grey[300],
                  fontSize: 14,
                  height: 1.5,
                ),
              ),
            ),

            // Action buttons
            if (faq.category == 'Localisation') ...[
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: () => _openMaps(),
                      icon: const Icon(Icons.map, size: 16),
                      label: const Text('Ouvrir Maps'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFFE9D7C2),
                        foregroundColor: Colors.black,
                        padding: const EdgeInsets.symmetric(vertical: 8),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: () => _openWhatsApp(),
                      icon: const Icon(Icons.chat, size: 16),
                      label: const Text('WhatsApp'),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: const Color(0xFFE9D7C2),
                        side: const BorderSide(color: Color(0xFFE9D7C2)),
                        padding: const EdgeInsets.symmetric(vertical: 8),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.search_off, color: Colors.grey[400], size: 64),
          const SizedBox(height: 16),
          Text(
            'Aucune question trouvée',
            style: Theme.of(
              context,
            ).textTheme.titleLarge?.copyWith(color: Colors.grey[400]),
          ),
          const SizedBox(height: 8),
          Text(
            'Essayez avec d\'autres mots-clés ou contactez notre support',
            style: Theme.of(
              context,
            ).textTheme.bodyMedium?.copyWith(color: Colors.grey[500]),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 24),
          ElevatedButton.icon(
            onPressed: _contactSupport,
            icon: const Icon(Icons.support_agent),
            label: const Text('Contacter le support'),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFE9D7C2),
              foregroundColor: Colors.black,
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(8),
              ),
            ),
          ),
        ],
      ),
    );
  }

  void _filterFAQs(String query) {
    setState(() {
      _filteredFAQs = _allFAQs.where((faq) {
        final matchesCategory =
            _selectedCategory == 'Toutes' || faq.category == _selectedCategory;
        final matchesQuery =
            query.isEmpty ||
            faq.question.toLowerCase().contains(query.toLowerCase()) ||
            faq.answer.toLowerCase().contains(query.toLowerCase());
        return matchesCategory && matchesQuery;
      }).toList();
    });
  }

  void _contactSupport() {
    _analytics.logEvent('faq_contact_support', {
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });

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

            Text(
              'Contacter le support',
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                color: Colors.white,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 20),

            // Contact options
            _buildContactOption(
              icon: Icons.phone,
              title: 'Appeler',
              subtitle: '+212 619 249249',
              onTap: () => _makePhoneCall(),
            ),
            _buildContactOption(
              icon: Icons.chat,
              title: 'WhatsApp',
              subtitle: 'Chat direct',
              onTap: () => _openWhatsApp(),
            ),
            _buildContactOption(
              icon: Icons.email,
              title: 'Email',
              subtitle: 'contact@boldbeautylounge.com',
              onTap: () => _sendEmail(),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildContactOption({
    required IconData icon,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
  }) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      child: ListTile(
        leading: Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: const Color(0xFFE9D7C2).withOpacity(0.2),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(icon, color: const Color(0xFFE9D7C2)),
        ),
        title: Text(
          title,
          style: const TextStyle(
            color: Colors.white,
            fontWeight: FontWeight.w500,
          ),
        ),
        subtitle: Text(subtitle, style: TextStyle(color: Colors.grey[400])),
        trailing: const Icon(
          Icons.arrow_forward_ios,
          color: Color(0xFFE9D7C2),
          size: 16,
        ),
        onTap: onTap,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
      ),
    );
  }

  Future<void> _makePhoneCall() async {
    final Uri phoneUri = Uri(scheme: 'tel', path: '+212619249249');
    if (await canLaunchUrl(phoneUri)) {
      await launchUrl(phoneUri);
    }
  }

  Future<void> _openWhatsApp() async {
    final Uri whatsappUri = Uri.parse('https://wa.me/212619249249');
    if (await canLaunchUrl(whatsappUri)) {
      await launchUrl(whatsappUri, mode: LaunchMode.externalApplication);
    }
  }

  Future<void> _sendEmail() async {
    final Uri emailUri = Uri(
      scheme: 'mailto',
      path: 'contact@boldbeautylounge.com',
      query: 'subject=Question FAQ',
    );
    if (await canLaunchUrl(emailUri)) {
      await launchUrl(emailUri);
    }
  }

  Future<void> _openMaps() async {
    final Uri mapsUri = Uri.parse('https://maps.app.goo.gl/BW1d99JkP1QK29W17');
    if (await canLaunchUrl(mapsUri)) {
      await launchUrl(mapsUri, mode: LaunchMode.externalApplication);
    }
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }
}

class FAQItem {
  final String question;
  final String answer;
  final String category;
  final IconData icon;
  bool isExpanded;

  FAQItem({
    required this.question,
    required this.answer,
    required this.category,
    required this.icon,
    this.isExpanded = false,
  });
}









