import 'package:flutter/material.dart';
import 'lib/services/chatbot_service.dart';

void main() async {
  print('🧪 TEST DU CHATBOT BOLD BEAUTY LOUNGE');
  print('=====================================');

  // Test des réponses rapides
  print('\n📝 TEST DES RÉPONSES RAPIDES:');
  print('-------------------------------');

  List<String> testMessages = [
    'Quels sont vos prix ?',
    'Je veux prendre RDV',
    'Quels sont vos horaires ?',
    'Où êtes-vous situés ?',
    'Qui sont vos spécialistes ?',
    'Quels services proposez-vous ?',
    'Bonjour, comment allez-vous ?', // Test API
  ];

  for (String message in testMessages) {
    print('\n👤 Utilisateur: "$message"');
    String quickResponse = ChatbotService.getQuickResponse(message);

    if (quickResponse.isNotEmpty) {
      print('🤖 Réponse rapide: "$quickResponse"');
    } else {
      print('🤖 Pas de réponse rapide → Utilisation API OpenAI');
      try {
        String apiResponse = await ChatbotService.sendMessage(
          message,
          'test_user',
        );
        print('🤖 Réponse API: "$apiResponse"');
      } catch (e) {
        print('❌ Erreur API: $e');
      }
    }
  }

  print('\n✅ TEST TERMINÉ');
}

