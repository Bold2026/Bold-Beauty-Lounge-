import 'dart:convert';
import 'dart:math';
import 'package:http/http.dart' as http;

class ChatbotService {
  static const String _apiKey = 'AIzaSyDJ7A2GDteGjAAuaDVcLdUUXb7dlvLs8Kw';
  static const String _baseUrl =
      'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';

  // Contexte Bold Beauty Lounge - Style ChatGPT conversationnel
  static const String _systemPrompt = '''
Tu es l'assistant virtuel intelligent et conversationnel de Bold Beauty Lounge, un salon de beauté de luxe à Casablanca, Maroc.

TON RÔLE:
- Tu es amical, professionnel et naturel dans tes réponses
- Tu réponds comme un vrai assistant de salon, pas comme un robot
- Tu utilises un ton chaleureux et engageant, comme dans une vraie conversation
- Tu peux être drôle, empathique et adapte ton style à chaque client
- Tu poses des questions de suivi pour mieux comprendre les besoins

INFORMATIONS BOLD BEAUTY LOUNGE:

📍 Localisation:
- Adresse: 2 rez-de-chaussée, 31 rue Abdessalam Aamir, Casablanca, Maroc
- Téléphone: +212 619 249249
- Email: contact.boldbeauty@gmail.com
- WhatsApp: https://wa.me/212619249249

🕐 Horaires d'ouverture:
- Lundi-Vendredi: 9h à 20h
- Samedi: 9h à 19h
- Dimanche: 10h à 18h

💅 Services disponibles:
- Coiffure (32 services): 70-1000 DH
- Onglerie (14 services): 50-500 DH
- Hammam (8 services): 150-800 DH
- Massage & Spa (8 services): 100-2000 DH
- Head Spa (3 services): 350-800 DH
- Soins Esthétiques (11 services): 25-2500 DH

👥 Équipe de spécialistes:
- Laila Bazzi (Directeur général) - ⭐ 4.9/5
- Nasira Mounir (Esthéticienne Senior) - ⭐ 4.8/5
- Fatima Zahra (Coiffeuse) - ⭐ 4.7/5
- Aicha Benali (Spécialiste Ongles) - ⭐ 4.9/5

GUIDELINES:
- Réponds toujours en français de manière naturelle et conversationnelle
- Sois concis mais complet dans tes réponses
- Utilise des emojis avec modération pour rester professionnel
- Si le client veut prendre RDV, guide-le vers le bouton "Prendre RDV" de manière naturelle
- Pose des questions pour mieux comprendre ses besoins si nécessaire
- Sois empathique et compréhensif
- Tu gardes le contexte de la conversation précédente
''';

  // Envoyer un message au chatbot avec historique de conversation (Google Gemini)
  static Future<String> sendMessage(
    String userMessage,
    String userId, {
    List<Map<String, String>>? conversationHistory,
  }) async {
    // Construire le prompt complet avec contexte
    String fullPrompt = _systemPrompt;

    // Ajouter l'historique de conversation si disponible
    if (conversationHistory != null && conversationHistory.isNotEmpty) {
      fullPrompt += '\n\nCONTEXTE DE LA CONVERSATION:\n';
      for (var msg in conversationHistory) {
        final role = msg['role'] == 'user' ? 'Client' : 'Assistant';
        fullPrompt += '$role: ${msg['content']}\n';
      }
      fullPrompt += '\n';
    }

    // Ajouter le message actuel
    fullPrompt +=
        '\nMessage du client: $userMessage\n\nRéponds de manière naturelle et conversationnelle:';

    // Tentative avec retry pour les erreurs
    const maxRetries = 3;
    for (int attempt = 0; attempt < maxRetries; attempt++) {
      try {
        if (attempt > 0) {
          // Backoff exponentiel: 1s, 2s, 3s
          await Future.delayed(Duration(seconds: attempt));
        }

        print(
          '🤖 Envoi message Gemini (tentative ${attempt + 1}/$maxRetries): $userMessage',
        );

        // Construire la requête pour Gemini
        final requestBody = {
          'contents': [
            {
              'parts': [
                {'text': fullPrompt},
              ],
            },
          ],
          'generationConfig': {
            'temperature': 0.8,
            'topK': 40,
            'topP': 0.9,
            'maxOutputTokens': 1024,
          },
        };

        final uri = Uri.parse('$_baseUrl?key=$_apiKey');

        final response = await http
            .post(
              uri,
              headers: {'Content-Type': 'application/json'},
              body: jsonEncode(requestBody),
            )
            .timeout(
              const Duration(seconds: 30),
              onTimeout: () {
                throw Exception('Timeout de l\'API Gemini');
              },
            );

        print('🤖 Réponse API Gemini: ${response.statusCode}');

        if (response.statusCode == 200) {
          final data = jsonDecode(response.body);
          print('🤖 Données parsées avec succès');

          // Extraire la réponse de Gemini
          if (data.containsKey('candidates') && data['candidates'] is List) {
            final candidates = data['candidates'] as List;
            if (candidates.isNotEmpty) {
              final candidate = candidates[0];
              if (candidate.containsKey('content') &&
                  candidate['content'].containsKey('parts')) {
                final parts = candidate['content']['parts'] as List;
                if (parts.isNotEmpty && parts[0].containsKey('text')) {
                  final content = parts[0]['text'] as String;
                  String cleanedContent = content.trim();

                  print(
                    '🤖 Réponse Gemini: ${cleanedContent.substring(0, min(100, cleanedContent.length))}...',
                  );
                  return cleanedContent;
                }
              }
            }
          }

          // Si la structure de réponse est différente
          print('⚠️ Structure de réponse inattendue: ${response.body}');
          return _getFallbackResponse(userMessage);
        } else if (response.statusCode == 429) {
          // Rate limit - réessayer après backoff
          print('⚠️ Rate limit (429) - tentative ${attempt + 1}/$maxRetries');
          if (attempt == maxRetries - 1) {
            print('🔄 Utilisation du fallback après toutes les tentatives');
            return _getFallbackResponse(userMessage);
          }
          continue;
        } else {
          print(
            '❌ Erreur API Gemini: ${response.statusCode} - ${response.body}',
          );
          if (attempt == maxRetries - 1) {
            return _getFallbackResponse(userMessage);
          }
          continue;
        }
      } catch (e) {
        print('❌ Erreur Chatbot Gemini (tentative ${attempt + 1}): $e');
        if (attempt == maxRetries - 1) {
          return _getFallbackResponse(userMessage);
        }
      }
    }

    // Si toutes les tentatives ont échoué
    return _getFallbackResponse(userMessage);
  }

  // Réponses prédéfinies améliorées pour les questions courantes
  static String getQuickResponse(String message) {
    final lowerMessage = message.toLowerCase().trim();

    // Salutations
    if (lowerMessage.contains('salut') ||
        lowerMessage.contains('bonjour') ||
        lowerMessage.contains('bonsoir') ||
        lowerMessage.contains('hello') ||
        lowerMessage.contains('hi') ||
        lowerMessage.contains('coucou') ||
        lowerMessage == 'bonjour' ||
        lowerMessage == 'salut' ||
        lowerMessage == 'hi' ||
        lowerMessage == 'hello') {
      final greetings = [
        'Bonjour ! 👋 Je suis ravi de vous aider. Que souhaitez-vous savoir sur Bold Beauty Lounge ?',
        'Bonjour ! Comment puis-je vous assister aujourd\'hui ?',
        'Salut ! 😊 En quoi puis-je vous aider ?',
      ];
      return greetings[Random().nextInt(greetings.length)];
    }

    // Au revoir
    if (lowerMessage.contains('au revoir') ||
        lowerMessage.contains('bye') ||
        lowerMessage.contains('à bientôt') ||
        lowerMessage.contains('aurevoir')) {
      return 'Au revoir ! À bientôt chez Bold Beauty Lounge. Prenez soin de vous ! 💆‍♀️✨';
    }

    // Merci
    if (lowerMessage.contains('merci') ||
        lowerMessage.contains('thanks') ||
        lowerMessage.contains('thank')) {
      return 'Je vous en prie ! 😊 N\'hésitez pas si vous avez d\'autres questions.';
    }

    // Prix et tarifs
    if (lowerMessage.contains('prix') ||
        lowerMessage.contains('tarif') ||
        lowerMessage.contains('cout') ||
        lowerMessage.contains('coût') ||
        lowerMessage.contains('combien') ||
        lowerMessage.contains('price')) {
      return 'Nos tarifs varient selon les services :\n\n'
          '💇 Coiffure: 70-1000 DH\n'
          '💅 Onglerie: 50-500 DH\n'
          '🛁 Hammam: 150-800 DH\n'
          '💆 Massage & Spa: 100-2000 DH\n'
          '🧖 Head Spa: 350-800 DH\n'
          '✨ Soins Esthétiques: 25-2500 DH\n\n'
          'Voulez-vous des informations plus détaillées sur un service spécifique ?';
    }

    // Réservations
    if (lowerMessage.contains('rdv') ||
        lowerMessage.contains('rendez-vous') ||
        lowerMessage.contains('réserver') ||
        lowerMessage.contains('reservation') ||
        lowerMessage.contains('appointment') ||
        lowerMessage.contains('book') ||
        lowerMessage.contains('booking')) {
      return 'Excellent ! Pour prendre rendez-vous, je vous recommande de :\n\n'
          '1️⃣ Cliquer sur "Prendre RDV" dans les Actions Rapides de l\'accueil\n'
          '2️⃣ Choisir votre service préféré\n'
          '3️⃣ Sélectionner la date et l\'heure\n'
          '4️⃣ Choisir votre spécialiste\n\n'
          'Vous pouvez aussi nous appeler directement au +212 619 249249 📞';
    }

    // Horaires
    if (lowerMessage.contains('horaire') ||
        lowerMessage.contains('ouvert') ||
        lowerMessage.contains('fermé') ||
        lowerMessage.contains('ouvert') ||
        lowerMessage.contains('disponible') ||
        lowerMessage.contains('hours') ||
        lowerMessage.contains('open') ||
        lowerMessage.contains('quand')) {
      return 'Nos horaires d\'ouverture :\n\n'
          '📅 Lundi - Vendredi: 9h à 20h\n'
          '📅 Samedi: 9h à 19h\n'
          '📅 Dimanche: 10h à 18h\n\n'
          'Nous restons disponibles pour vos besoins de beauté ! 💅✨';
    }

    // Adresse et localisation
    if (lowerMessage.contains('adresse') ||
        lowerMessage.contains('localisation') ||
        lowerMessage.contains('localiser') ||
        lowerMessage.contains('où') ||
        lowerMessage.contains('ou') ||
        lowerMessage.contains('address') ||
        lowerMessage.contains('location') ||
        lowerMessage.contains('situé') ||
        lowerMessage.contains('trouver') ||
        lowerMessage.contains('aller')) {
      return '📍 Nous sommes situés au :\n\n'
          '2 rez-de-chaussée\n'
          '31 rue Abdessalam Aamir\n'
          'Casablanca, Maroc\n\n'
          '📞 Téléphone: +212 619 249249\n'
          '✉️ Email: contact.boldbeauty@gmail.com\n'
          '💬 WhatsApp: wa.me/212619249249\n\n'
          'Vous pouvez utiliser le bouton "Localisation" sur l\'accueil pour nous trouver sur Google Maps ! 🗺️';
    }

    // Spécialistes
    if (lowerMessage.contains('spécialiste') ||
        lowerMessage.contains('employé') ||
        lowerMessage.contains('equipe') ||
        lowerMessage.contains('équipe') ||
        lowerMessage.contains('qui') ||
        lowerMessage.contains('staff') ||
        lowerMessage.contains('team') ||
        lowerMessage.contains('coiffeur') ||
        lowerMessage.contains('esthéticien')) {
      return 'Notre équipe de spécialistes :\n\n'
          '👩‍💼 Laila Bazzi - Directeur général ⭐ 4.9/5\n'
          '💆 Nasira Mounir - Esthéticienne Senior ⭐ 4.8/5\n'
          '💇 Fatima Zahra - Coiffeuse ⭐ 4.7/5\n'
          '💅 Aicha Benali - Spécialiste Ongles ⭐ 4.9/5\n\n'
          'Tous nos spécialistes sont expérimentés et dédiés à votre bien-être !';
    }

    // Services
    if (lowerMessage.contains('service') ||
        lowerMessage.contains('soin') ||
        lowerMessage.contains('qu\'offrez') ||
        lowerMessage.contains('offrez') ||
        lowerMessage.contains('propose') ||
        lowerMessage.contains('disponible')) {
      return 'Nous proposons 6 catégories de services :\n\n'
          '💇 Coiffure (32 services)\n'
          '💅 Onglerie (14 services)\n'
          '🛁 Hammam (8 services)\n'
          '💆 Massage & Spa (8 services)\n'
          '🧖 Head Spa (3 services)\n'
          '✨ Soins Esthétiques (11 services)\n\n'
          'Que souhaitez-vous savoir sur un service spécifique ?';
    }

    // Contact
    if (lowerMessage.contains('contact') ||
        lowerMessage.contains('appeler') ||
        lowerMessage.contains('appel') ||
        lowerMessage.contains('telephone') ||
        lowerMessage.contains('téléphone') ||
        lowerMessage.contains('phone') ||
        lowerMessage.contains('email') ||
        lowerMessage.contains('mail') ||
        lowerMessage.contains('whatsapp')) {
      return 'Pour nous contacter :\n\n'
          '📞 Téléphone: +212 619 249249\n'
          '✉️ Email: contact.boldbeauty@gmail.com\n'
          '💬 WhatsApp: wa.me/212619249249\n'
          '📍 Adresse: 2 rez-de-chaussée, 31 rue Abdessalam Aamir, Casablanca\n\n'
          'N\'hésitez pas à nous appeler ou à utiliser le bouton "Contact" sur l\'accueil !';
    }

    return ''; // Retourner vide pour utiliser l'API
  }

  // Réponse de fallback intelligente basée sur le contexte
  static String _getFallbackResponse(String userMessage) {
    // Essayer d'abord les réponses rapides
    String quickResponse = getQuickResponse(userMessage);
    if (quickResponse.isNotEmpty) {
      return quickResponse;
    }

    // Réponse générique mais utile
    return 'Merci pour votre message ! 😊\n\n'
        'Je peux vous aider avec :\n'
        '• Nos services et tarifs\n'
        '• Prise de rendez-vous\n'
        '• Horaires et localisation\n'
        '• Informations sur nos spécialistes\n\n'
        'Posez-moi une question spécifique ou utilisez les boutons d\'action rapide sur l\'accueil ! 💅✨';
  }
}
