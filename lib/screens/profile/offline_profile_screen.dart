import 'package:flutter/material.dart';
import 'package:lucide_icons/lucide_icons.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../services/auth_service.dart';
import '../auth/new_login_screen.dart';
import '../auth/new_signup_screen.dart';
import '../home/pour_bold_details_screen.dart';

class OfflineProfileScreen extends StatefulWidget {
  const OfflineProfileScreen({super.key});

  @override
  State<OfflineProfileScreen> createState() => _OfflineProfileScreenState();
}

class _OfflineProfileScreenState extends State<OfflineProfileScreen> {
  static const Color _accentColor = Color(0xFFE9D7C2);

  Map<String, dynamic>? _currentUser;
  bool _isLoading = true;

  final GlobalKey _faqSectionKey = GlobalKey();
  final GlobalKey _aboutSectionKey = GlobalKey();
  final GlobalKey _termsSectionKey = GlobalKey();
  final GlobalKey _privacySectionKey = GlobalKey();

  final List<Map<String, String>> _faqItems = const [
    {
      'question': 'Comment réserver un rendez-vous ?',
      'answer':
          'Sélectionnez votre soin, choisissez la date souhaitée puis confirmez. Une notification vous sera envoyée pour valider votre réservation.',
    },
    {
      'question': 'Puis-je payer avec mes points fidélité ?',
      'answer':
          'Oui. Lors du paiement, sélectionnez “Utiliser mes points Bold”. Le montant disponible s’affichera automatiquement.',
    },
    {
      'question': 'Quels sont vos horaires ?',
      'answer':
          'Du lundi au dimanche de 10h00 à 20h00. Les horaires spéciaux (veille de fêtes) sont communiqués par notification.',
    },
    {
      'question': 'Proposez-vous des soins pour hommes ?',
      'answer':
          'Oui, des rituels dédiés sont disponibles sur la page Tarifs. Nos spécialistes vous conseilleront le soin idéal.',
    },
    {
      'question': 'Comment fonctionne le programme de fidélité ?',
      'answer':
          '1 DH dépensé = 1 point. Dès 200 points, bénéficiez de réductions, offres duo et invitations à nos événements privés.',
    },
  ];

  final String _assistanceShareContent =
      'Assistance Bold Beauty Lounge\ncontact@boldbeautylounge.com\n+212 619-249249\n\nPrenez rendez-vous et profitez d’une expérience beauté premium à Casablanca.';

  final String _aboutShareContent =
      'Bold Beauty Lounge — salon premium à Casablanca.\nExpérience sur-mesure : coiffure, head spa, hammam, soins esthétiques.\nMission : révéler votre beauté naturelle avec des rituels haut de gamme.';

  final String _termsShareContent =
      'Conditions d’utilisation Bold Beauty Lounge\nRendez-vous, politiques d’annulation, responsabilités et droits des utilisateurs.\nConsultez boldbeautylounge.com pour la version complète.';

  final String _privacyShareContent =
      'Politique de confidentialité Bold Beauty Lounge\nSécurité des données personnelles, transparence, suppression de compte.\nConsultez boldbeautylounge.com/confidentialite pour les détails.';

  final List<Map<String, String>> _countryCodes = const [
    {'label': '🇲🇦 +212', 'code': '+212'},
    {'label': '🇦🇪 +971', 'code': '+971'},
    {'label': '🇸🇦 +966', 'code': '+966'},
    {'label': '🇶🇦 +974', 'code': '+974'},
    {'label': '🇧🇭 +973', 'code': '+973'},
    {'label': '🇨🇦 +1', 'code': '+1-CA'},
    {'label': '🇺🇸 +1', 'code': '+1-US'},
  ];

  String _selectedDialCode = '+212';

  @override
  void initState() {
    super.initState();
    _loadUserData();
  }

  Future<void> _loadUserData() async {
    final user = await AuthService.getCurrentUserStatic();
    if (!mounted) return;
    setState(() {
      _currentUser = user;
      _isLoading = false;
    });
  }

  Future<void> _launchUri(Uri uri) async {
    if (!await launchUrl(uri, mode: LaunchMode.externalApplication)) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Impossible d’ouvrir le lien demandé')),
      );
    }
  }

  void _scrollToSection(GlobalKey key) {
    final context = key.currentContext;
    if (context != null) {
      Scrollable.ensureVisible(
        context,
        alignment: 0.1,
        duration: const Duration(milliseconds: 400),
        curve: Curves.easeInOut,
      );
    }
  }

  final List<_ShareOption> _shareOptions = const [
    _ShareOption(
      label: 'Partager sur WhatsApp',
      icon: LucideIcons.messageCircle,
      urlBuilder: _ShareOption.whatsAppUrl,
    ),
    _ShareOption(
      label: 'Partager sur Facebook',
      icon: LucideIcons.facebook,
      urlBuilder: _ShareOption.facebookUrl,
    ),
    _ShareOption(
      label: 'Partager sur Instagram',
      icon: LucideIcons.instagram,
      urlBuilder: _ShareOption.instagramUrl,
    ),
    _ShareOption(
      label: 'Partager sur Telegram',
      icon: LucideIcons.send,
      urlBuilder: _ShareOption.telegramUrl,
    ),
    _ShareOption(
      label: 'Partager sur Snapchat',
      icon: LucideIcons.ghost,
      urlBuilder: _ShareOption.snapchatUrl,
    ),
  ];

  Future<void> _showShareOptions(String content) async {
    if (!mounted) return;
    await showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
          ),
          padding: const EdgeInsets.fromLTRB(20, 24, 20, 32),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  const Text(
                    'Partager',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.w700,
                      color: Colors.black,
                    ),
                  ),
                  const Spacer(),
                  IconButton(
                    icon: const Icon(Icons.close, color: Colors.black54),
                    onPressed: () => Navigator.pop(context),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              ..._shareOptions.map(
                (option) => ListTile(
                  leading: Icon(option.icon, color: Colors.black87),
                  title: Text(
                    option.label,
                    style: const TextStyle(
                      fontWeight: FontWeight.w600,
                      color: Colors.black87,
                    ),
                  ),
                  onTap: () async {
                    Navigator.pop(context);
                    final uri = option.urlBuilder(content);
                    if (uri != null) await _launchUri(uri);
                  },
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  void _showAssistanceSheet() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return SafeArea(
          top: false,
          child: Container(
            decoration: const BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
            ),
            padding: const EdgeInsets.fromLTRB(20, 18, 20, 20),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Container(
                  width: 36,
                  height: 4,
                  margin: const EdgeInsets.only(bottom: 20),
                  decoration: BoxDecoration(
                    color: Colors.black.withOpacity(0.08),
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
                const Text(
                  'Contactez-nous',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 17,
                    fontWeight: FontWeight.w600,
                    color: Colors.black,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  'Comment souhaitez-vous nous contacter ?',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 14,
                    color: Colors.black.withOpacity(0.6),
                  ),
                ),
                const SizedBox(height: 20),
                _SupportActionButton(
                  label: 'Email',
                  icon: LucideIcons.mail,
                  onTap: () {
                    Navigator.pop(context);
                    _launchUri(
                      Uri.parse('mailto:contact@boldbeautylounge.com'),
                    );
                  },
                ),
                const SizedBox(height: 12),
                _SupportActionButton(
                  label: 'Téléphone',
                  icon: LucideIcons.phoneCall,
                  onTap: () {
                    Navigator.pop(context);
                    _launchUri(Uri.parse('tel:+212619249249'));
                  },
                ),
                const SizedBox(height: 12),
                _SupportActionButton(
                  label: 'WhatsApp',
                  icon: LucideIcons.messageCircle,
                  onTap: () {
                    Navigator.pop(context);
                    _launchUri(Uri.parse('https://wa.me/212619249249'));
                  },
                ),
                const SizedBox(height: 16),
                TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text(
                    'Annuler',
                    style: TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.w600,
                      color: Colors.black,
                    ),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  void _showAboutSheet() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (context) => _buildSupportSheet(
        title: 'À propos de Bold Beauty Lounge',
        subtitle: 'Mis à jour le 11 Nov 2025',
        content: _aboutShareContent,
        body: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: const [
            _SectionParagraph(
              title: 'Notre mission',
              body:
                  'Révéler la beauté naturelle de chacun à travers des rituels haut de gamme, une expertise sur-mesure et une expérience sensorielle unique.',
            ),
            SizedBox(height: 16),
            _SectionParagraph(
              title: 'Comment ça marche pour vous',
              body:
                  'Réservez vos soins depuis l’application, profitez d’une consultation personnalisée et vivez une parenthèse de bien-être dans un espace élégant au cœur de Casablanca.',
            ),
            SizedBox(height: 16),
            _SectionParagraph(
              title: 'Nos avantages',
              body:
                  '• Expertise coiffure, head spa, hammam, esthétique et massages\n• Programme fidélité généreux\n• Offres duo, cartes cadeaux, diagnostics personnalisés\n• Equipe certifiée et soins premium',
            ),
            SizedBox(height: 16),
            _SectionParagraph(
              title: 'Notre ambition',
              body:
                  'Devenir la référence beauté & bien-être au Maroc en combinant innovation, excellence et hospitalité pour chaque invité Bold.',
            ),
          ],
        ),
        shareContent: _aboutShareContent,
      ),
    );
  }

  void _showTermsSheet() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (context) => _buildSupportSheet(
        title: 'Conditions d’utilisation',
        subtitle: 'CGU / CGV & mentions légales',
        content: _termsShareContent,
        body: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: const [
            _SectionParagraph(
              title: 'Réservations & annulations',
              body:
                  'Les rendez-vous peuvent être modifiés ou annulés jusqu’à 12 heures à l’avance depuis l’application. Passé ce délai, des frais peuvent s’appliquer.',
            ),
            SizedBox(height: 14),
            _SectionParagraph(
              title: 'Paiements',
              body:
                  'Paiement en salon (cash, carte) ou via l’application. Les cartes cadeaux et points fidélité sont utilisables sur tous les soins hors promotions limitées.',
            ),
            SizedBox(height: 14),
            _SectionParagraph(
              title: 'Responsabilité',
              body:
                  'Bold Beauty Lounge s’engage à assurer la sécurité, la confidentialité et la conformité des soins. Toute réaction doit être signalée sous 48h.',
            ),
          ],
        ),
        shareContent: _termsShareContent,
      ),
    );
  }

  void _showPrivacySheet() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (context) => _buildSupportSheet(
        title: 'Politique de confidentialité',
        subtitle: 'Protection de vos données personnelles',
        content: _privacyShareContent,
        body: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: const [
            _SectionParagraph(
              title: 'Données collectées',
              body:
                  'Informations de contact, préférences de soins, historique de réservation. Elles servent à personnaliser l’expérience et optimiser nos services.',
            ),
            SizedBox(height: 14),
            _SectionParagraph(
              title: 'Sécurité & stockage',
              body:
                  'Les données sont hébergées sur des serveurs sécurisés (UE). Aucune information bancaire n’est stockée par Bold Beauty Lounge.',
            ),
            SizedBox(height: 14),
            _SectionParagraph(
              title: 'Vos droits',
              body:
                  'Accès, modification et suppression sur simple demande : privacy@boldbeautylounge.com. Suppression immédiate en cas de clôture de compte.',
            ),
          ],
        ),
        shareContent: _privacyShareContent,
      ),
    );
  }

  void _showFaqSheet() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (context) => _buildSupportSheet(
        title: 'FAQ',
        subtitle: 'Questions fréquentes',
        content: '',
        shareContent: _assistanceShareContent,
        body: Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.light(),
            dividerColor: Colors.transparent,
            splashColor: Colors.transparent,
            highlightColor: Colors.transparent,
            canvasColor: Colors.white,
            scaffoldBackgroundColor: Colors.white,
            cardColor: Colors.white,
          ),
          child: Material(
            color: Colors.white,
            child: ExpansionPanelList.radio(
              expandedHeaderPadding: EdgeInsets.zero,
              animationDuration: const Duration(milliseconds: 250),
              dividerColor: Colors.transparent,
              elevation: 0,
              children: _faqItems.map((item) {
                final question = item['question'] ?? '';
                final answer = item['answer'] ?? '';
                return ExpansionPanelRadio(
                  value: question,
                  canTapOnHeader: true,
                  headerBuilder: (context, isExpanded) {
                    return ListTile(
                      contentPadding: const EdgeInsets.symmetric(
                        horizontal: 0,
                        vertical: 4,
                      ),
                      title: Text(
                        question,
                        style: const TextStyle(
                          color: Colors.black,
                          fontWeight: FontWeight.w700,
                          fontSize: 15,
                        ),
                      ),
                    );
                  },
                  body: Padding(
                    padding: const EdgeInsets.only(bottom: 12),
                    child: Text(
                      answer,
                      style: TextStyle(
                        color: Colors.black.withValues(alpha: 0.65),
                        fontSize: 14,
                        height: 1.4,
                      ),
                    ),
                  ),
                );
              }).toList(),
            ),
          ),
        ),
      ),
    );
  }

  void _showConnectSheet() {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => const NewLoginScreen(),
      ),
    );
  }

  Widget _buildPhoneAuthSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Connexion rapide par téléphone',
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        const SizedBox(height: 16),
        TextField(
          style: const TextStyle(color: Colors.white),
          decoration: InputDecoration(
            labelText: 'Prénom',
            hintText: 'Ex: Sara',
            labelStyle: const TextStyle(color: Colors.white70),
            hintStyle: const TextStyle(color: Colors.white38),
            filled: true,
            fillColor: const Color(0xFF1A1A1A),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: const BorderSide(color: Colors.white24),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: const BorderSide(color: Colors.white24),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: const BorderSide(color: Colors.white70),
            ),
            prefixIcon: const Icon(LucideIcons.user, color: Colors.white60),
            contentPadding: const EdgeInsets.symmetric(
              horizontal: 16,
              vertical: 14,
            ),
          ),
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              flex: 4,
              child: DropdownButtonFormField<String>(
                value: _selectedDialCode,
                decoration: InputDecoration(
                  labelText: 'Indicatif',
                  labelStyle: const TextStyle(color: Colors.white70),
                  filled: true,
                  fillColor: const Color(0xFF1A1A1A),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide: const BorderSide(color: Colors.white24),
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide: const BorderSide(color: Colors.white24),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide: const BorderSide(color: Colors.white70),
                  ),
                  prefixIcon: const Icon(
                    LucideIcons.globe,
                    color: Colors.white60,
                  ),
                  contentPadding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 14,
                  ),
                ),
                isExpanded: true,
                dropdownColor: const Color(0xFF1A1A1A),
                iconEnabledColor: Colors.white70,
                style: const TextStyle(color: Colors.white),
                items: _countryCodes
                    .map(
                      (item) => DropdownMenuItem<String>(
                        value: item['code'],
                        child: Text(
                          item['label']!,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(color: Colors.white),
                        ),
                      ),
                    )
                    .toList(),
                onChanged: (value) {
                  if (value == null) return;
                  setState(() {
                    _selectedDialCode = value;
                  });
                },
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              flex: 6,
              child: TextField(
                keyboardType: TextInputType.phone,
                style: const TextStyle(color: Colors.white),
                decoration: InputDecoration(
                  labelText: 'Numéro de téléphone',
                  hintText: '6 12 34 56 78',
                  labelStyle: const TextStyle(color: Colors.white70),
                  hintStyle: const TextStyle(color: Colors.white38),
                  filled: true,
                  fillColor: const Color(0xFF1A1A1A),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide: const BorderSide(color: Colors.white24),
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide: const BorderSide(color: Colors.white24),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide: const BorderSide(color: Colors.white70),
                  ),
                  prefixIcon: const Icon(
                    LucideIcons.phone,
                    color: Colors.white60,
                  ),
                  contentPadding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 14,
                  ),
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        TextField(
          keyboardType: TextInputType.emailAddress,
          style: const TextStyle(color: Colors.white),
          decoration: InputDecoration(
            labelText: 'Adresse e-mail',
            hintText: 'vous@exemple.com',
            labelStyle: const TextStyle(color: Colors.white70),
            hintStyle: const TextStyle(color: Colors.white38),
            filled: true,
            fillColor: const Color(0xFF1A1A1A),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: const BorderSide(color: Colors.white24),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: const BorderSide(color: Colors.white24),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: const BorderSide(color: Colors.white70),
            ),
            prefixIcon: const Icon(LucideIcons.mail, color: Colors.white60),
            contentPadding: const EdgeInsets.symmetric(
              horizontal: 16,
              vertical: 14,
            ),
          ),
        ),
        const SizedBox(height: 20),
        ElevatedButton.icon(
          onPressed: () {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('OTP envoyé sur votre Gmail')),
            );
          },
          icon: const Icon(LucideIcons.mail),
          label: const Text('Confirmer via Gmail'),
          style: ElevatedButton.styleFrom(
            minimumSize: const Size.fromHeight(52),
            backgroundColor: Colors.redAccent,
            foregroundColor: Colors.white,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(16),
            ),
          ),
        ),
        const SizedBox(height: 12),
        ElevatedButton.icon(
          onPressed: () {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('OTP envoyé sur WhatsApp')),
            );
          },
          icon: const Icon(LucideIcons.messageCircle),
          label: const Text('Confirmer via WhatsApp'),
          style: ElevatedButton.styleFrom(
            minimumSize: const Size.fromHeight(52),
            backgroundColor: Colors.green,
            foregroundColor: Colors.white,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(16),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildGoogleAuthButton() {
    return OutlinedButton.icon(
      onPressed: () {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Connexion Google à venir')),
        );
      },
      icon: const Icon(LucideIcons.chrome, color: Colors.white),
      label: const Text('Continuer avec Google'),
      style: OutlinedButton.styleFrom(
        minimumSize: const Size.fromHeight(52),
        foregroundColor: Colors.white,
        side: const BorderSide(color: Colors.white54),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8F3EA),
      appBar: AppBar(
        title: const Text('Mon profil'),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black87,
        elevation: 0,
        centerTitle: true,
        actions: [
          IconButton(
            tooltip: 'Connecter',
            onPressed: _showConnectSheet,
            icon: const Icon(Icons.person_outline, color: Colors.black87),
          ),
          const SizedBox(width: 4),
        ],
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.only(bottom: 32),
          child: Column(
            children: [
              _buildProfileHeader(),
              _buildStatistics(),
              _buildMenuItems(),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildProfileHeader() {
    if (_isLoading) {
      return Container(
        margin: const EdgeInsets.fromLTRB(20, 24, 20, 16),
        padding: const EdgeInsets.all(24),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(28),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.08),
              blurRadius: 18,
              offset: const Offset(0, 10),
            ),
          ],
        ),
        child: const Center(
          child: CircularProgressIndicator(color: _accentColor),
        ),
      );
    }

    if (_currentUser == null) {
      return Padding(
        padding: const EdgeInsets.fromLTRB(20, 24, 20, 8),
        child: Column(
          children: [
            Container(
              width: 100,
              height: 100,
              decoration: BoxDecoration(
                color: const Color(0xFFE9D7C2),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.person_outline,
                color: Colors.black54,
                size: 48,
              ),
            ),
            const SizedBox(height: 24),
            Container(
              padding: const EdgeInsets.fromLTRB(20, 28, 20, 24),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(28),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.08),
                    blurRadius: 20,
                    offset: const Offset(0, 12),
                  ),
                ],
              ),
              child: Column(
                children: [
                  const Text(
                    'Connectez-vous pour profiter de toutes les fonctionnalités',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      color: Colors.black,
                      fontSize: 18,
                      fontWeight: FontWeight.w700,
                      height: 1.4,
                    ),
                  ),
                  const SizedBox(height: 24),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: _showConnectSheet,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.black,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 16),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(20),
                        ),
                      ),
                      child: const Text(
                        'Se connecter',
                        style: TextStyle(
                          fontWeight: FontWeight.w700,
                          fontSize: 15,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 14),
                  GestureDetector(
                    onTap: _showConnectSheet,
                    child: RichText(
                      textAlign: TextAlign.center,
                      text: TextSpan(
                        text: 'Pas encore de compte ? ',
                        style: TextStyle(
                          color: Colors.black.withValues(alpha: 0.6),
                          fontSize: 14,
                          fontWeight: FontWeight.w600,
                        ),
                        children: const [
                          TextSpan(
                            text: 'S’inscrire',
                            style: TextStyle(
                              color: Colors.black,
                              decoration: TextDecoration.underline,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      );
    }

    final initials =
        '${_currentUser!['firstName'][0]}${_currentUser!['lastName'][0]}';

    return Container(
      margin: const EdgeInsets.fromLTRB(20, 24, 20, 16),
      padding: const EdgeInsets.all(26),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(32),
        gradient: const LinearGradient(
          colors: [Color(0xFF131212), Color(0xFF1F1A17), Color(0xFF2E2318)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF1A140F).withValues(alpha: 0.35),
            blurRadius: 28,
            offset: const Offset(0, 18),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              CircleAvatar(
                radius: 42,
                backgroundColor: Colors.white.withValues(alpha: 0.18),
                child: Text(
                  initials.toUpperCase(),
                  style: const TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
                  ),
                ),
              ),
              const SizedBox(width: 18),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      '${_currentUser!['firstName']} ${_currentUser!['lastName']}',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 22,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      _currentUser!['email'] ?? 'client@boldbeautylounge.com',
                      style: TextStyle(
                        color: Colors.white.withValues(alpha: 0.7),
                        fontSize: 13,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      _currentUser!['phone'] ?? '+212 000-000000',
                      style: TextStyle(
                        color: Colors.white.withValues(alpha: 0.6),
                        fontSize: 12,
                      ),
                    ),
                  ],
                ),
              ),
              IconButton(
                onPressed: () async {
                  await AuthService.signOutStatic();
                  if (!mounted) return;
                  _loadUserData();
                },
                icon: const Icon(Icons.logout, color: Colors.white),
              ),
            ],
          ),
          const SizedBox(height: 24),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 16),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.08),
              borderRadius: BorderRadius.circular(22),
            ),
            child: Row(
              children: const [
                _ProfileStatChip(
                  icon: LucideIcons.badgeCheck,
                  label: 'Points Bold',
                  value: '0 pts',
                ),
                SizedBox(width: 14),
                _ProfileStatChip(
                  icon: LucideIcons.calendarCheck2,
                  label: 'Réservations',
                  value: '0',
                ),
                SizedBox(width: 14),
                _ProfileStatChip(
                  icon: LucideIcons.heart,
                  label: 'Favoris',
                  value: '0',
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatistics() {
    final stats = [
      {
        'icon': LucideIcons.badgeCheck,
        'value': '0 pts',
        'label': 'Points fidélité',
      },
      {
        'icon': LucideIcons.calendarCheck2,
        'value': '0',
        'label': 'Réservations',
      },
      {'icon': LucideIcons.gift, 'value': '0', 'label': 'Offres actives'},
    ];

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(26),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.06),
            blurRadius: 20,
            offset: const Offset(0, 12),
          ),
        ],
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: stats
            .map(
              (stat) => _buildStatItem(
                icon: stat['icon'] as IconData,
                value: stat['value'] as String,
                label: stat['label'] as String,
              ),
            )
            .toList(),
      ),
    );
  }

  Widget _buildStatItem({
    required IconData icon,
    required String value,
    required String label,
  }) {
    return Expanded(
      child: Column(
        children: [
          Container(
            width: 48,
            height: 48,
            decoration: BoxDecoration(
              color: const Color(0xFFE9D7C2),
              shape: BoxShape.circle,
            ),
            child: Icon(icon, color: Colors.black, size: 24),
          ),
          const SizedBox(height: 10),
          Text(
            value,
            style: const TextStyle(
              color: Colors.black,
              fontSize: 16,
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            label,
            style: TextStyle(
              color: Colors.black.withValues(alpha: 0.55),
              fontSize: 12,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildMenuItems() {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(26),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.06),
            blurRadius: 18,
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: Column(
        children: [
          _buildMenuItem(
            title: 'Informations importantes',
            subtitle: 'Informations essentielles sur Bold Beauty Lounge',
            icon: LucideIcons.info,
            onTap: () {
              _scrollToSection(_aboutSectionKey);
            },
          ),
          _buildDivider(),
          _buildMenuItem(
            title: 'FAQ',
            subtitle: 'Questions fréquentes',
            icon: LucideIcons.helpCircle,
            onTap: _showFaqSheet,
          ),
          _buildDivider(),
          _buildMenuItem(
            title: 'À propos',
            subtitle: 'Vision, mission & valeurs',
            icon: LucideIcons.sparkles,
            onTap: _showAboutSheet,
          ),
          _buildDivider(),
          _buildMenuItem(
            title: "Conditions d'utilisation",
            subtitle: 'CGU / CGV & mentions légales',
            icon: LucideIcons.fileText,
            onTap: _showTermsSheet,
          ),
          _buildDivider(),
          _buildMenuItem(
            title: 'Politique de confidentialité',
            subtitle: 'Protection des données',
            icon: LucideIcons.shield,
            onTap: _showPrivacySheet,
          ),
          _buildDivider(),
          _buildMenuItem(
            title: 'Contacter le support',
            subtitle: 'Email ou téléphone',
            icon: LucideIcons.lifeBuoy,
            onTap: _showAssistanceSheet,
          ),
          _buildDivider(),
          _buildMenuItem(
            title: 'Langues',
            subtitle: 'Choisissez votre langue préférée',
            icon: LucideIcons.globe,
            onTap: () {},
          ),
        ],
      ),
    );
  }

  ListTile _buildMenuItem({
    required String title,
    required String subtitle,
    required IconData icon,
    VoidCallback? onTap,
  }) {
    return ListTile(
      onTap: onTap,
      leading: Container(
        width: 46,
        height: 46,
        decoration: BoxDecoration(
          color: const Color(0xFFF7F1E6),
          borderRadius: BorderRadius.circular(16),
        ),
        child: Icon(icon, color: const Color(0xFF2B2115)),
      ),
      title: Text(
        title,
        style: const TextStyle(
          color: Colors.black,
          fontSize: 16,
          fontWeight: FontWeight.w600,
        ),
      ),
      subtitle: Text(
        subtitle,
        style: TextStyle(
          color: Colors.black.withValues(alpha: 0.55),
          fontSize: 12,
        ),
      ),
      trailing: Icon(
        LucideIcons.arrowUpRight,
        color: Colors.black.withValues(alpha: 0.45),
      ),
    );
  }

  Divider _buildDivider() => Divider(
    height: 1,
    color: Colors.black.withValues(alpha: 0.05),
    indent: 18,
    endIndent: 18,
  );

  Widget _buildFaqSection() {
    return Container(
      key: _faqSectionKey,
      margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
      padding: const EdgeInsets.symmetric(vertical: 8),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(26),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 18,
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: Theme(
        data: Theme.of(context).copyWith(
          dividerColor: Colors.transparent,
          splashColor: Colors.transparent,
          highlightColor: Colors.transparent,
        ),
        child: ExpansionPanelList.radio(
          animationDuration: const Duration(milliseconds: 250),
          expandedHeaderPadding: EdgeInsets.zero,
          dividerColor: Colors.transparent,
          elevation: 0,
          children: _faqItems.map((item) {
            final question = item['question'] ?? '';
            final answer = item['answer'] ?? '';
            return ExpansionPanelRadio(
              value: question,
              canTapOnHeader: true,
              headerBuilder: (context, isExpanded) {
                return ListTile(
                  contentPadding: const EdgeInsets.symmetric(
                    horizontal: 20,
                    vertical: 4,
                  ),
                  title: Text(
                    question,
                    style: const TextStyle(
                      color: Colors.black,
                      fontWeight: FontWeight.w700,
                      fontSize: 15,
                    ),
                  ),
                );
              },
              body: Padding(
                padding: const EdgeInsets.fromLTRB(20, 0, 20, 16),
                child: Text(
                  answer,
                  style: TextStyle(
                    color: Colors.black.withValues(alpha: 0.65),
                    fontSize: 14,
                    height: 1.4,
                  ),
                ),
              ),
            );
          }).toList(),
        ),
      ),
    );
  }

  Widget _buildAboutSection() {
    return Container(
      key: _aboutSectionKey,
      margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(26),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 18,
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'À propos de Bold',
            style: TextStyle(
              color: Colors.black,
              fontSize: 20,
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Mis à jour le 11 Nov 2025',
            style: TextStyle(
              color: Colors.black.withValues(alpha: 0.6),
              fontSize: 13,
            ),
          ),
          const SizedBox(height: 20),
          const _SectionParagraph(
            title: 'Bold Beauty Lounge',
            body:
                'Situé au cœur de Casablanca, Bold Beauty Lounge est un espace premium dédié à la beauté globale : head spa, coiffure signature, hammam, soins esthétiques et massages.',
          ),
          const SizedBox(height: 18),
          const _SectionParagraph(
            title: 'Notre mission',
            body:
                'Offrir une parenthèse de bien-être total. Chaque visite est pensée comme une expérience sur-mesure, combinant savoir-faire haut de gamme et accompagnement personnalisé.',
          ),
          const SizedBox(height: 18),
          const _SectionParagraph(
            title: 'Expérience client',
            body:
                'Réservation simplifiée, accueil chaleureux, diagnostic beauté, rituels experts, suivi personnalisé. Vous repartez détendu(e), confiant(e) et rayonnant(e).',
          ),
          const SizedBox(height: 18),
          const _SectionParagraph(
            title: 'Avantages Bold',
            body:
                'Programme fidélité généreux, avantages duo, cartes cadeaux, événements privés, offres pour les ambassadeurs Bold Beauty Lounge.',
          ),
          const SizedBox(height: 18),
          const _SectionParagraph(
            title: 'Ambition',
            body:
                'Devenir la référence beauté et bien-être premium au Maroc, en alliant innovation, élégance et excellence de service.',
          ),
          const SizedBox(height: 24),
          SizedBox(
            width: double.infinity,
            child: OutlinedButton.icon(
              onPressed: () => _showShareOptions(_aboutShareContent),
              icon: const Icon(LucideIcons.share2),
              label: const Text('Partager'),
              style: OutlinedButton.styleFrom(
                foregroundColor: Colors.black,
                side: BorderSide(color: Colors.black.withOpacity(0.15)),
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTermsSection() {
    return Container(
      key: _termsSectionKey,
      margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(26),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 18,
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Conditions d’utilisation',
            style: TextStyle(
              color: Colors.black,
              fontSize: 20,
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'CGU / CGV & mentions légales',
            style: TextStyle(
              color: Colors.black.withValues(alpha: 0.6),
              fontSize: 13,
            ),
          ),
          const SizedBox(height: 20),
          const _SectionParagraph(
            title: 'Réservations',
            body:
                'Toute réservation implique l’acceptation des CGU. Annulation gratuite jusqu’à 12h avant le rendez-vous. Des frais peuvent s’appliquer au-delà.',
          ),
          const SizedBox(height: 16),
          const _SectionParagraph(
            title: 'Paiements',
            body:
                'Paiement sécurisé en salon ou via l’application. Les offres promotionnelles sont soumises aux conditions indiquées lors de la réservation.',
          ),
          const SizedBox(height: 16),
          const _SectionParagraph(
            title: 'Responsabilité & hygiène',
            body:
                'Nos équipes sont certifiées et formées aux protocoles Bold Beauty Lounge. Informez-nous de toute sensibilité ou contre-indication avant votre soin.',
          ),
          const SizedBox(height: 24),
          SizedBox(
            width: double.infinity,
            child: OutlinedButton.icon(
              onPressed: () => _showShareOptions(_termsShareContent),
              icon: const Icon(LucideIcons.share2),
              label: const Text('Partager'),
              style: OutlinedButton.styleFrom(
                foregroundColor: Colors.black,
                side: BorderSide(color: Colors.black.withOpacity(0.15)),
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPrivacySection() {
    return Container(
      key: _privacySectionKey,
      margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(26),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 18,
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Politique de confidentialité',
            style: TextStyle(
              color: Colors.black,
              fontSize: 20,
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Protection de vos données personnelles',
            style: TextStyle(
              color: Colors.black.withValues(alpha: 0.6),
              fontSize: 13,
            ),
          ),
          const SizedBox(height: 20),
          const _SectionParagraph(
            title: 'Données collectées',
            body:
                'Nom, prénom, coordonnées, préférences de soins, historique de rendez-vous. Pas de stockage de données bancaires par Bold.',
          ),
          const SizedBox(height: 16),
          const _SectionParagraph(
            title: 'Utilisation',
            body:
                'Personnalisation des soins, communication des offres, suivi de votre satisfaction. Aucune revente ou location de données.',
          ),
          const SizedBox(height: 16),
          const _SectionParagraph(
            title: 'Vos droits',
            body:
                'Accès, rectification, portabilité, suppression sur simple demande à privacy@boldbeautylounge.com ou via l’application.',
          ),
          const SizedBox(height: 24),
          SizedBox(
            width: double.infinity,
            child: OutlinedButton.icon(
              onPressed: () => _showShareOptions(_privacyShareContent),
              icon: const Icon(LucideIcons.share2),
              label: const Text('Partager'),
              style: OutlinedButton.styleFrom(
                foregroundColor: Colors.black,
                side: BorderSide(color: Colors.black.withOpacity(0.15)),
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSupportSheet({
    required String title,
    required String subtitle,
    required Widget body,
    required String content,
    required String shareContent,
  }) {
    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
      ),
      padding: const EdgeInsets.fromLTRB(24, 24, 24, 32),
      child: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        title,
                        style: const TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        subtitle,
                        style: TextStyle(
                          color: Colors.black.withValues(alpha: 0.6),
                          fontSize: 13,
                        ),
                      ),
                    ],
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.close),
                  onPressed: () => Navigator.pop(context),
                ),
              ],
            ),
            const SizedBox(height: 24),
            body,
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              child: OutlinedButton.icon(
                onPressed: () => _showShareOptions(shareContent),
                icon: const Icon(LucideIcons.share2),
                label: const Text('Partager'),
                style: OutlinedButton.styleFrom(
                  foregroundColor: Colors.black,
                  side: BorderSide(color: Colors.black.withOpacity(0.15)),
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSupportTile({
    required IconData icon,
    required String label,
    VoidCallback? onTap,
  }) {
    return ListTile(
      contentPadding: const EdgeInsets.symmetric(horizontal: 0, vertical: 4),
      leading: Container(
        width: 44,
        height: 44,
        decoration: BoxDecoration(
          color: const Color(0xFFF7F1E6),
          borderRadius: BorderRadius.circular(16),
        ),
        child: Icon(icon, color: const Color(0xFF2B2115)),
      ),
      title: Text(label, style: const TextStyle(fontWeight: FontWeight.w600)),
      trailing: const Icon(LucideIcons.arrowUpRight),
      onTap: onTap,
    );
  }
}

class _ProfileStatChip extends StatelessWidget {
  const _ProfileStatChip({
    required this.icon,
    required this.label,
    required this.value,
  });

  final IconData icon;
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Column(
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(18),
            ),
            child: Icon(icon, color: const Color(0xFFE9D7C2)),
          ),
          const SizedBox(height: 8),
          Text(
            value,
            style: const TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            label,
            style: TextStyle(
              color: Colors.white.withValues(alpha: 0.7),
              fontSize: 11,
            ),
          ),
        ],
      ),
    );
  }
}

class _ShareOption {
  final String label;
  final IconData icon;
  final Uri? Function(String content) urlBuilder;

  const _ShareOption({
    required this.label,
    required this.icon,
    required this.urlBuilder,
  });

  static Uri? whatsAppUrl(String content) {
    return Uri.tryParse('https://wa.me/?text=${Uri.encodeComponent(content)}');
  }

  static Uri? facebookUrl(String content) {
    return Uri.tryParse(
      'https://www.facebook.com/sharer/sharer.php?u=${Uri.encodeComponent('https://boldbeautylounge.com')}&quote=${Uri.encodeComponent(content)}',
    );
  }

  static Uri? instagramUrl(String content) {
    return Uri.tryParse(
      'https://www.instagram.com/?url=${Uri.encodeComponent(content)}',
    );
  }

  static Uri? telegramUrl(String content) {
    return Uri.tryParse(
      'https://t.me/share/url?url=${Uri.encodeComponent('https://boldbeautylounge.com')}&text=${Uri.encodeComponent(content)}',
    );
  }

  static Uri? snapchatUrl(String content) {
    return Uri.tryParse(
      'https://www.snapchat.com/share?text=${Uri.encodeComponent(content)}',
    );
  }
}

class _SectionParagraph extends StatelessWidget {
  const _SectionParagraph({required this.title, required this.body});

  final String title;
  final String body;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: const TextStyle(
            color: Colors.black,
            fontSize: 16,
            fontWeight: FontWeight.w600,
            height: 1.4,
          ),
        ),
        const SizedBox(height: 8),
        Text(
          body,
          style: TextStyle(
            color: Colors.black.withValues(alpha: 0.7),
            fontSize: 14,
            height: 1.5,
          ),
        ),
      ],
    );
  }
}

class _SupportActionButton extends StatelessWidget {
  const _SupportActionButton({
    required this.label,
    required this.icon,
    required this.onTap,
  });

  final String label;
  final IconData icon;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 16),
        decoration: BoxDecoration(
          color: const Color(0xFFF7F1E6),
          borderRadius: BorderRadius.circular(18),
          border: Border.all(color: Colors.black.withOpacity(0.06)),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, color: const Color(0xFF2B2115), size: 20),
            const SizedBox(width: 10),
            Text(
              label,
              style: const TextStyle(
                color: Colors.black,
                fontSize: 15,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
