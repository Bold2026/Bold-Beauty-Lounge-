import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../services/analytics_service.dart';

class PrivacyPolicyScreen extends StatefulWidget {
  const PrivacyPolicyScreen({super.key});

  @override
  State<PrivacyPolicyScreen> createState() => _PrivacyPolicyScreenState();
}

class _PrivacyPolicyScreenState extends State<PrivacyPolicyScreen> {
  final AnalyticsService _analytics = AnalyticsService.instance;

  @override
  void initState() {
    super.initState();
    _analytics.logEvent('privacy_policy_viewed', {
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Politique de Confidentialité'),
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            onPressed: () => _sharePrivacyPolicy(),
            icon: const Icon(Icons.share),
            tooltip: 'Partager',
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
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header
              _buildHeader(),
              const SizedBox(height: 24),

              // Last updated
              _buildLastUpdated(),
              const SizedBox(height: 24),

              // Table of contents
              _buildTableOfContents(),
              const SizedBox(height: 24),

              // Privacy policy content
              _buildPrivacyContent(),
              const SizedBox(height: 24),

              // Contact section
              _buildContactSection(),
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
                  Icons.privacy_tip,
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
                      'Politique de Confidentialité',
                      style: Theme.of(context).textTheme.headlineSmall
                          ?.copyWith(
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
          ),
          const SizedBox(height: 16),
          Text(
            'Votre confidentialité est notre priorité. Cette politique explique comment nous collectons, utilisons et protégeons vos informations personnelles.',
            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
              color: Colors.grey[300],
              height: 1.5,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLastUpdated() {
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
      child: Row(
        children: [
          const Icon(Icons.update, color: Color(0xFFE9D7C2), size: 20),
          const SizedBox(width: 12),
          Text(
            'Dernière mise à jour : 15 Décembre 2024',
            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
              color: Colors.white,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTableOfContents() {
    final sections = [
      '1. Introduction',
      '2. Données personnelles collectées',
      '3. Finalité du traitement des données',
      '4. Conservation des données',
      '5. Partage des données',
      '6. Sécurité des données',
      '7. Cookies et outils de suivi',
      '8. Vos droits',
      '9. Modifications de la politique',
      '10. Contact',
    ];

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.grey[900],
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Table des matières',
            style: Theme.of(context).textTheme.titleLarge?.copyWith(
              color: Colors.white,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 16),
          ...sections.map(
            (section) => Padding(
              padding: const EdgeInsets.only(bottom: 8),
              child: InkWell(
                onTap: () => _scrollToSection(section),
                borderRadius: BorderRadius.circular(8),
                child: Padding(
                  padding: const EdgeInsets.symmetric(vertical: 4),
                  child: Row(
                    children: [
                      const Icon(
                        Icons.arrow_forward_ios,
                        color: Color(0xFFE9D7C2),
                        size: 16,
                      ),
                      const SizedBox(width: 8),
                      Text(
                        section,
                        style: Theme.of(
                          context,
                        ).textTheme.bodyMedium?.copyWith(color: Colors.white),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPrivacyContent() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.grey[900],
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Section 1: Introduction
          _buildSection(
            title: '1. Introduction',
            content: '''
La présente Politique de Confidentialité décrit la manière dont Bold Beauty Lounge collecte, utilise, protège et partage les informations personnelles que vous nous fournissez lors de l'utilisation de notre application mobile, de nos services de réservation en ligne ou de nos formulaires de contact.

En accédant à notre application ou en utilisant nos services, vous acceptez les pratiques décrites dans cette politique.
''',
          ),

          const SizedBox(height: 24),

          // Section 2: Données collectées
          _buildSection(
            title: '2. Données personnelles collectées',
            content: '''
Nous collectons uniquement les informations nécessaires au bon fonctionnement de nos services, notamment :

• Informations d'identité : nom, prénom
• Coordonnées : numéro de téléphone, adresse e-mail
• Informations de réservation : date, heure, service choisi, message optionnel
• Données techniques : adresse IP, type d'appareil, navigateur, cookies
• Préférences : thème, langue, notifications
• Données d'utilisation : pages visitées, temps passé, actions effectuées
''',
          ),

          const SizedBox(height: 24),

          // Section 3: Finalité
          _buildSection(
            title: '3. Finalité du traitement des données',
            content: '''
Vos données sont collectées dans le but de :

• Gérer vos réservations et commandes (cartes cadeaux, forfaits, etc.)
• Vous contacter en cas de modification ou de confirmation d'un rendez-vous
• Vous informer sur nos offres promotionnelles ou nouveautés (si vous y avez consenti)
• Améliorer la qualité de nos services et l'expérience utilisateur
• Personnaliser votre expérience dans l'application
• Analyser l'utilisation de nos services pour les optimiser
''',
          ),

          const SizedBox(height: 24),

          // Section 4: Conservation
          _buildSection(
            title: '4. Conservation des données',
            content: '''
Vos données personnelles sont conservées uniquement pendant la durée nécessaire à la réalisation des finalités pour lesquelles elles ont été collectées :

• Données de réservation : 3 ans après la dernière visite
• Données de contact : jusqu'à votre demande de suppression
• Données techniques : 12 mois maximum
• Données de marketing : jusqu'à votre désabonnement

Sauf obligation légale contraire, vos données sont automatiquement supprimées à l'expiration de ces délais.
''',
          ),

          const SizedBox(height: 24),

          // Section 5: Partage
          _buildSection(
            title: '5. Partage des données',
            content: '''
Nous ne vendons, louons ou ne partageons aucune donnée personnelle avec des tiers à des fins commerciales.

Cependant, certaines données peuvent être partagées avec :

• Nos prestataires techniques (hébergement, maintenance, email marketing, paiements sécurisés) dans le cadre strict du fonctionnement de l'application
• Les autorités légales en cas d'obligation légale ou judiciaire
• Nos partenaires de confiance uniquement avec votre consentement explicite
''',
          ),

          const SizedBox(height: 24),

          // Section 6: Sécurité
          _buildSection(
            title: '6. Sécurité des données',
            content: '''
Nous mettons en œuvre toutes les mesures techniques et organisationnelles nécessaires pour protéger vos données contre :

• L'accès non autorisé
• La perte
• L'altération ou la divulgation accidentelle

Notre application utilise notamment :
• Le protocole HTTPS pour toutes les communications
• Le chiffrement des données sensibles
• L'authentification sécurisée
• La surveillance continue des accès
''',
          ),

          const SizedBox(height: 24),

          // Section 7: Cookies
          _buildSection(
            title: '7. Cookies et outils de suivi',
            content: '''
Notre application peut utiliser des cookies et technologies similaires afin d'améliorer la navigation et de mesurer l'audience :

• Cookies essentiels : nécessaires au fonctionnement de l'application
• Cookies analytiques : pour comprendre l'utilisation de l'application
• Cookies de préférences : pour mémoriser vos choix (thème, langue)

Vous pouvez refuser ou supprimer les cookies via les paramètres de votre appareil.
''',
          ),

          const SizedBox(height: 24),

          // Section 8: Droits
          _buildSection(
            title: '8. Vos droits',
            content: '''
Conformément à la législation en vigueur (notamment le RGPD), vous disposez des droits suivants :

• Droit d'accès et de rectification de vos données
• Droit d'opposition ou de suppression
• Droit à la portabilité de vos données
• Droit de retirer votre consentement à tout moment
• Droit de limitation du traitement
• Droit d'opposition au traitement

Pour exercer vos droits, vous pouvez nous contacter via l'application ou aux coordonnées mentionnées ci-dessous.
''',
          ),

          const SizedBox(height: 24),

          // Section 9: Modifications
          _buildSection(
            title: '9. Modifications de la politique',
            content: '''
Nous nous réservons le droit de modifier cette Politique de Confidentialité à tout moment. Toute mise à jour sera :

• Publiée dans l'application avec la nouvelle date d'application
• Communiquée par notification push si les changements sont significatifs
• Disponible dans les paramètres de l'application

Nous vous encourageons à consulter régulièrement cette politique.
''',
          ),
        ],
      ),
    );
  }

  Widget _buildSection({required String title, required String content}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: Theme.of(context).textTheme.titleMedium?.copyWith(
            color: const Color(0xFFE9D7C2),
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: 12),
        Text(
          content,
          style: Theme.of(context).textTheme.bodyMedium?.copyWith(
            color: Colors.grey[300],
            height: 1.6,
          ),
        ),
      ],
    );
  }

  Widget _buildContactSection() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.grey[900],
        borderRadius: BorderRadius.circular(16),
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
              const Icon(
                Icons.contact_support,
                color: Color(0xFFE9D7C2),
                size: 24,
              ),
              const SizedBox(width: 12),
              Text(
                '10. Contact',
                style: Theme.of(context).textTheme.titleLarge?.copyWith(
                  color: Colors.white,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Text(
            'Pour toute question relative à cette Politique de Confidentialité ou au traitement de vos données personnelles :',
            style: Theme.of(
              context,
            ).textTheme.bodyMedium?.copyWith(color: Colors.grey[300]),
          ),
          const SizedBox(height: 16),

          // Contact methods
          _buildContactMethod(
            icon: Icons.email,
            title: 'Email',
            value: 'contact@boldbeautylounge.com',
            onTap: () => _launchEmail(),
          ),
          const SizedBox(height: 12),

          _buildContactMethod(
            icon: Icons.phone,
            title: 'Téléphone',
            value: '+212 619-249249',
            onTap: () => _launchPhone(),
          ),
          const SizedBox(height: 12),

          _buildContactMethod(
            icon: Icons.location_on,
            title: 'Adresse',
            value: '2 Rez-de-chaussée, 31 Rue Abdessalam Aamir, Casablanca',
            onTap: () => _launchMaps(),
          ),
        ],
      ),
    );
  }

  Widget _buildContactMethod({
    required IconData icon,
    required String title,
    required String value,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 8),
        child: Row(
          children: [
            Icon(icon, color: const Color(0xFFE9D7C2), size: 20),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: Colors.grey[400],
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    value,
                    style: Theme.of(
                      context,
                    ).textTheme.bodyMedium?.copyWith(color: Colors.white),
                  ),
                ],
              ),
            ),
            const Icon(
              Icons.arrow_forward_ios,
              color: Color(0xFFE9D7C2),
              size: 16,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildActionButtons() {
    return Row(
      children: [
        Expanded(
          child: ElevatedButton.icon(
            onPressed: () => _downloadPrivacyPolicy(),
            icon: const Icon(Icons.download),
            label: const Text('Télécharger PDF'),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFE9D7C2),
              foregroundColor: Colors.black,
              padding: const EdgeInsets.symmetric(vertical: 12),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(8),
              ),
            ),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: OutlinedButton.icon(
            onPressed: () => _sharePrivacyPolicy(),
            icon: const Icon(Icons.share),
            label: const Text('Partager'),
            style: OutlinedButton.styleFrom(
              foregroundColor: const Color(0xFFE9D7C2),
              side: const BorderSide(color: Color(0xFFE9D7C2)),
              padding: const EdgeInsets.symmetric(vertical: 12),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(8),
              ),
            ),
          ),
        ),
      ],
    );
  }

  void _scrollToSection(String section) {
    // Implementation for scrolling to specific section
    // This would require ScrollController and GlobalKey for each section
  }

  Future<void> _launchEmail() async {
    final Uri emailUri = Uri(
      scheme: 'mailto',
      path: 'contact@boldbeautylounge.com',
      query: 'subject=Question sur la Politique de Confidentialité',
    );

    if (await canLaunchUrl(emailUri)) {
      await launchUrl(emailUri);
      _analytics.logEvent('privacy_contact_email', {'method': 'email'});
    }
  }

  Future<void> _launchPhone() async {
    final Uri phoneUri = Uri(scheme: 'tel', path: '+212619249249');

    if (await canLaunchUrl(phoneUri)) {
      await launchUrl(phoneUri);
      _analytics.logEvent('privacy_contact_phone', {'method': 'phone'});
    }
  }

  Future<void> _launchMaps() async {
    final Uri mapsUri = Uri.parse('https://maps.app.goo.gl/BW1d99JkP1QK29W17');

    if (await canLaunchUrl(mapsUri)) {
      await launchUrl(mapsUri, mode: LaunchMode.externalApplication);
      _analytics.logEvent('privacy_contact_maps', {'method': 'maps'});
    }
  }

  void _downloadPrivacyPolicy() {
    // Implementation for downloading PDF
    _analytics.logEvent('privacy_policy_downloaded', {'format': 'pdf'});

    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Téléchargement du PDF en cours...'),
        backgroundColor: Color(0xFFE9D7C2),
      ),
    );
  }

  void _sharePrivacyPolicy() {
    // Implementation for sharing
    _analytics.logEvent('privacy_policy_shared', {'method': 'share'});

    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Partage de la politique de confidentialité...'),
        backgroundColor: Color(0xFFE9D7C2),
      ),
    );
  }
}









