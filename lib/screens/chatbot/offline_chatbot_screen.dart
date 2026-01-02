import 'package:flutter/material.dart';

class OfflineChatbotScreen extends StatefulWidget {
  const OfflineChatbotScreen({super.key});

  @override
  State<OfflineChatbotScreen> createState() => _OfflineChatbotScreenState();
}

class _OfflineChatbotScreenState extends State<OfflineChatbotScreen> {
  final TextEditingController _messageController = TextEditingController();
  final ScrollController _scrollController = ScrollController();
  final List<ChatMessage> _messages = [];

  @override
  void initState() {
    super.initState();
    // Message de bienvenue
    _messages.add(
      ChatMessage(
        text:
            'Bonjour !\n\nJe suis votre assistante virtuelle Bold Beauty Lounge. Comment puis-je vous aider aujourd\'hui ?\n\nVous pouvez me poser des questions sur :\n• Nos services et tarifs\n• Prendre rendez-vous\n• Horaires d\'ouverture\n• Localisation\n• Promotions',
        isUser: false,
        timestamp: DateTime.now(),
      ),
    );
  }

  @override
  void dispose() {
    _messageController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  void _sendMessage() {
    if (_messageController.text.trim().isEmpty) return;

    final userMessage = _messageController.text.trim();

    setState(() {
      // Add user message
      _messages.add(
        ChatMessage(text: userMessage, isUser: true, timestamp: DateTime.now()),
      );

      _messageController.clear();
    });

    // Simulate AI response
    Future.delayed(const Duration(milliseconds: 500), () {
      setState(() {
        _messages.add(
          ChatMessage(
            text: _generateResponse(userMessage),
            isUser: false,
            timestamp: DateTime.now(),
          ),
        );
      });

      // Scroll to bottom
      _scrollToBottom();
    });
  }

  String _generateResponse(String userMessage) {
    final message = userMessage.toLowerCase();

    // Services
    if (message.contains('service') || message.contains('soin')) {
      return 'Nous proposons plusieurs services :\n\n• Coiffure (à partir de 70 DH)\n• Onglerie (à partir de 50 DH)\n• Hammam (à partir de 150 DH)\n• Massages & Spa (à partir de 100 DH)\n• Head Spa (à partir de 350 DH)\n• Soins Esthétiques (à partir de 25 DH)\n\nQuel service vous intéresse ?';
    }

    // Rendez-vous
    if (message.contains('rdv') ||
        message.contains('rendez-vous') ||
        message.contains('réserv')) {
      return 'Pour prendre rendez-vous, vous pouvez :\n\n📱 WhatsApp : +212 619 249249\n📞 Téléphone : +212 619 249249\n📧 Email : contact.boldbeauty@gmail.com\n\nOu utilisez l\'onglet "RDV" dans l\'application !';
    }

    // Horaires
    if (message.contains('horaire') ||
        message.contains('heure') ||
        message.contains('ouvert')) {
      return 'Nos horaires d\'ouverture :\n\n🕐 Lundi : Fermé\n🕐 Mardi : 14h00 - 20h00\n🕐 Mercredi - Samedi : 10h00 - 20h00\n🕐 Dimanche : 11h00 - 20h00';
    }

    // Localisation
    if (message.contains('adresse') ||
        message.contains('où') ||
        message.contains('local')) {
      return '📍 Notre adresse :\n\n2 rez-de-chaussée\n31 rue Abdessalam Aamir\nCasablanca, Maroc\n\nVous pouvez consulter la carte dans l\'application !';
    }

    // Prix/Tarifs
    if (message.contains('prix') ||
        message.contains('tarif') ||
        message.contains('coût')) {
      return 'Voici quelques exemples de tarifs :\n\n💇‍♀️ Coiffure : à partir de 70 DH\n💅 Onglerie : à partir de 50 DH\n🛁 Hammam : à partir de 150 DH\n💆 Massages : à partir de 100 DH\n✨ Head Spa : à partir de 350 DH\n🌸 Soins : à partir de 25 DH\n\nContactez-nous pour plus de détails !';
    }

    // Contact
    if (message.contains('contact') ||
        message.contains('téléphone') ||
        message.contains('appel')) {
      return '📞 Contactez-nous :\n\n📱 WhatsApp : +212 619 249249\n☎️ Téléphone : +212 619 249249\n📧 Email : contact.boldbeauty@gmail.com\n\nNous sommes à votre écoute !';
    }

    // Promotion
    if (message.contains('promo') ||
        message.contains('offre') ||
        message.contains('réduction')) {
      return '🎁 Programme de fidélité :\n\nGagnez 1 point pour chaque dirham dépensé !\nAccumulez des points pour bénéficier de réductions exclusives.\n\nContactez-nous pour en savoir plus sur nos offres en cours !';
    }

    // Default response
    return 'Merci pour votre message ! 😊\n\nJe peux vous aider avec :\n• Informations sur nos services\n• Prise de rendez-vous\n• Horaires et localisation\n• Tarifs et promotions\n\nQue souhaitez-vous savoir ?';
  }

  void _scrollToBottom() {
    Future.delayed(const Duration(milliseconds: 100), () {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                color: const Color(0xFFE9D7C2).withOpacity(0.3),
                borderRadius: BorderRadius.circular(20),
              ),
              child: const Icon(
                Icons.smart_toy,
                color: Color(0xFFE9D7C2),
                size: 24,
              ),
            ),
            const SizedBox(width: 12),
            const Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Assistant Bold Beauty',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
                Text(
                  'En ligne',
                  style: TextStyle(fontSize: 12, color: Colors.green),
                ),
              ],
            ),
          ],
        ),
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [Colors.black, Colors.grey],
          ),
        ),
        child: Column(
          children: [
            // Messages list
            Expanded(
              child: ListView.builder(
                controller: _scrollController,
                padding: const EdgeInsets.all(16),
                itemCount: _messages.length,
                itemBuilder: (context, index) {
                  return _buildMessageBubble(_messages[index]);
                },
              ),
            ),

            // Input area
            _buildInputArea(),
          ],
        ),
      ),
    );
  }

  Widget _buildMessageBubble(ChatMessage message) {
    return Align(
      alignment: message.isUser ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        margin: const EdgeInsets.only(bottom: 12),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        constraints: BoxConstraints(
          maxWidth: MediaQuery.of(context).size.width * 0.75,
        ),
        decoration: BoxDecoration(
          color: message.isUser
              ? const Color(0xFFE9D7C2)
              : Colors.white.withOpacity(0.1),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: message.isUser
                ? const Color(0xFFE9D7C2)
                : Colors.white.withOpacity(0.3),
          ),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              message.text,
              style: TextStyle(
                color: message.isUser ? Colors.black : Colors.white,
                fontSize: 15,
                height: 1.4,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              _formatTime(message.timestamp),
              style: TextStyle(
                color: message.isUser
                    ? Colors.black.withOpacity(0.6)
                    : Colors.white.withOpacity(0.6),
                fontSize: 11,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInputArea() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.black.withOpacity(0.5),
        border: Border(top: BorderSide(color: Colors.white.withOpacity(0.2))),
      ),
      child: SafeArea(
        child: Row(
          children: [
            Expanded(
              child: Container(
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(25),
                  border: Border.all(color: Colors.white.withOpacity(0.2)),
                ),
                child: TextField(
                  controller: _messageController,
                  style: const TextStyle(color: Colors.white),
                  decoration: InputDecoration(
                    hintText: 'Écrivez votre message...',
                    hintStyle: TextStyle(color: Colors.white.withOpacity(0.5)),
                    border: InputBorder.none,
                    contentPadding: const EdgeInsets.symmetric(
                      horizontal: 20,
                      vertical: 12,
                    ),
                  ),
                  onSubmitted: (_) => _sendMessage(),
                ),
              ),
            ),
            const SizedBox(width: 12),
            GestureDetector(
              onTap: _sendMessage,
              child: Container(
                width: 50,
                height: 50,
                decoration: const BoxDecoration(
                  color: Color(0xFFE9D7C2),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.send, color: Colors.black, size: 24),
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _formatTime(DateTime time) {
    return '${time.hour.toString().padLeft(2, '0')}:${time.minute.toString().padLeft(2, '0')}';
  }
}

class ChatMessage {
  final String text;
  final bool isUser;
  final DateTime timestamp;

  ChatMessage({
    required this.text,
    required this.isUser,
    required this.timestamp,
  });
}
