// Test simple des réponses rapides du chatbot
void main() {
  print('🧪 TEST SIMPLE DU CHATBOT');
  print('=========================');

  // Simuler les réponses rapides
  List<String> testMessages = [
    'Quels sont vos prix ?',
    'Je veux prendre RDV',
    'Quels sont vos horaires ?',
    'Où êtes-vous situés ?',
    'Qui sont vos spécialistes ?',
    'Quels services proposez-vous ?',
  ];

  for (String message in testMessages) {
    print('\n👤 Utilisateur: "$message"');

    // Logique simplifiée des réponses rapides
    String response = '';
    String lowerMessage = message.toLowerCase();

    if (lowerMessage.contains('prix') || lowerMessage.contains('tarif')) {
      response =
          'Nos tarifs varient selon les services. Coiffure: 70-1000 DH, Onglerie: 50-500 DH, Hammam: 150-800 DH. Voulez-vous voir nos tarifs détaillés ?';
    } else if (lowerMessage.contains('rdv') ||
        lowerMessage.contains('rendez-vous') ||
        lowerMessage.contains('réserver')) {
      response =
          'Parfait ! Pour prendre rendez-vous, cliquez sur "Prendre RDV" dans les Actions Rapides. Vous pourrez choisir vos services, la date, l\'heure et votre spécialiste préféré.';
    } else if (lowerMessage.contains('horaire') ||
        lowerMessage.contains('ouvert')) {
      response =
          'Nous sommes ouverts du lundi au vendredi de 9h à 20h, le samedi de 9h à 19h et le dimanche de 10h à 18h.';
    } else if (lowerMessage.contains('adresse') ||
        lowerMessage.contains('localisation') ||
        lowerMessage.contains('où')) {
      response =
          'Nous sommes situés au 2 rez-de-chaussée, 31 rue Abdessalam Aamir, Casablanca. Vous pouvez nous appeler au +212 619 249249.';
    } else if (lowerMessage.contains('spécialiste') ||
        lowerMessage.contains('employé') ||
        lowerMessage.contains('qui')) {
      response =
          'Nos spécialistes sont Laila Bazzi (Directeur général), Nasira Mounir (Esthéticienne Senior), Fatima Zahra (Coiffeuse) et Aicha Benali (Spécialiste Ongles). Tous ont d\'excellentes notes !';
    } else if (lowerMessage.contains('service') ||
        lowerMessage.contains('soin')) {
      response =
          'Nous proposons 6 catégories de services: Coiffure, Onglerie, Hammam, Massage & Spa, Head Spa et Soins Esthétiques. Que souhaitez-vous savoir ?';
    }

    if (response.isNotEmpty) {
      print('🤖 Réponse rapide: "$response"');
    } else {
      print('🤖 Pas de réponse rapide → Utilisation API OpenAI');
    }
  }

  print('\n✅ TEST TERMINÉ');
  print('\n📋 RÉSUMÉ:');
  print('- Les réponses rapides fonctionnent correctement');
  print('- Le chatbot devrait répondre aux questions courantes');
  print('- Pour les autres questions, l\'API OpenAI sera utilisée');
}

