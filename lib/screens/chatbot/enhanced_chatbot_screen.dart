import 'package:flutter/material.dart';
import 'package:lucide_icons/lucide_icons.dart';
import '../../services/chatbot_service.dart';

class EnhancedChatbotScreen extends StatefulWidget {
  const EnhancedChatbotScreen({super.key});

  @override
  State<EnhancedChatbotScreen> createState() => _EnhancedChatbotScreenState();
}

class _EnhancedChatbotScreenState extends State<EnhancedChatbotScreen> {
  final TextEditingController _messageController = TextEditingController();
  final ScrollController _scrollController = ScrollController();
  final List<ChatMessage> _messages = [];
  bool _hasText = false;
  bool _isLoading = false;

  // Raccourcis pour le slider horizontal
  final List<_QuickAction> _quickActions = const [
    _QuickAction(
      label: 'Réserver un soin',
      message: 'Je souhaite réserver un soin',
    ),
    _QuickAction(
      label: 'Mes points fidélité',
      message: 'Je veux voir mes points fidélité',
    ),
    _QuickAction(
      label: 'Mes rendez-vous',
      message: 'Je veux voir mes rendez-vous',
    ),
  ];

  @override
  void initState() {
    super.initState();
    _seedIntroMessages();
    _messageController.addListener(() {
      setState(() {
        _hasText = _messageController.text.trim().isNotEmpty;
      });
    });
  }

  @override
  void dispose() {
    _messageController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  void _seedIntroMessages() {
    // Message de salutation automatique
    setState(() {
      _messages.clear();
      _messages.add(
        ChatMessage(
          text: 'Bonjour ! Comment puis-je vous aider ?',
          isUser: false,
          timestamp: DateTime.now(),
        ),
      );
    });
  }

  @override
  Widget build(BuildContext context) {
    final messageWidgets = _messages.map(_buildMessageBubble).toList();

    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        backgroundColor: Colors.black,
        elevation: 0,
        titleSpacing: 0,
        title: Row(
          children: [
            Container(
              width: 42,
              height: 42,
              decoration: BoxDecoration(
                color: Colors.white.withOpacity(0.1),
                shape: BoxShape.circle,
              ),
              child: const Icon(LucideIcons.bot, color: Colors.white, size: 22),
            ),
            const SizedBox(width: 12),
            const Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Assistant Bold Beauty',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                SizedBox(height: 4),
                Row(
                  children: [
                    Icon(LucideIcons.circleDot, color: Colors.green, size: 12),
                    const SizedBox(width: 6),
                    const Text(
                      'En ligne',
                      style: TextStyle(color: Colors.green, fontSize: 12),
                    ),
                  ],
                ),
              ],
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.more_vert, color: Colors.white),
            onPressed: _showOptions,
          ),
        ],
      ),
      body: Column(
        children: [
          Expanded(
            child: ListView(
              controller: _scrollController,
              padding: const EdgeInsets.fromLTRB(16, 10, 16, 16),
              physics: const BouncingScrollPhysics(),
              children: [
                ...messageWidgets,
                if (_isLoading)
                  Padding(
                    padding: const EdgeInsets.only(top: 8),
                    child: _buildTypingIndicator(),
                  ),
              ],
            ),
          ),
          // Slider horizontal pour les raccourcis
          _buildQuickActionsSlider(),
          _buildInputField(),
        ],
      ),
    );
  }

  Widget _buildQuickActionsSlider() {
    return Container(
      height: 50,
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        itemCount: _quickActions.length,
        itemBuilder: (context, index) {
          final action = _quickActions[index];
          return Padding(
            padding: EdgeInsets.only(
              right: index < _quickActions.length - 1 ? 12 : 0,
            ),
            child: GestureDetector(
              onTap: () => _handleQuickAction(action),
              child: Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 10,
                ),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: Colors.grey.shade300),
                ),
                child: Center(
                  child: Text(
                    action.label,
                    style: const TextStyle(
                      color: Colors.black87,
                      fontSize: 13,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ),
              ),
            ),
          );
        },
      ),
    );
  }

  void _handleQuickAction(_QuickAction action) {
    setState(() {
      _messages.add(
        ChatMessage(
          text: action.message,
          isUser: true,
          timestamp: DateTime.now(),
        ),
      );
    });
    _scrollToBottom();
    _getBotResponse(action.message);
  }

  Widget _buildTypingIndicator() {
    return Row(
      children: [
        Container(
          width: 8,
          height: 8,
          decoration: const BoxDecoration(
            color: Color(0xFF25D366),
            shape: BoxShape.circle,
          ),
        ),
        const SizedBox(width: 6),
        Container(
          width: 8,
          height: 8,
          decoration: const BoxDecoration(
            color: Color(0xFF25D366),
            shape: BoxShape.circle,
          ),
        ),
        const SizedBox(width: 6),
        Container(
          width: 8,
          height: 8,
          decoration: const BoxDecoration(
            color: Color(0xFF25D366),
            shape: BoxShape.circle,
          ),
        ),
        const SizedBox(width: 12),
        const Text(
          'Assistant tape...',
          style: TextStyle(color: Colors.grey, fontSize: 13),
        ),
      ],
    );
  }

  Widget _buildMessageBubble(ChatMessage message) {
    if (message.isUser) {
      // Messages utilisateur à droite (beige)
      return Padding(
        padding: const EdgeInsets.symmetric(vertical: 6),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.end,
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 280),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                decoration: BoxDecoration(
                  color: const Color(0xFFE9D7C2),
                  borderRadius: BorderRadius.circular(18),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Align(
                      alignment: Alignment.centerLeft,
                      child: Text(
                        message.text,
                        style: const TextStyle(
                          fontSize: 14,
                          color: Colors.black,
                          height: 1.45,
                        ),
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      _formatTime(message.timestamp),
                      style: TextStyle(
                        color: Colors.black.withOpacity(0.6),
                        fontSize: 11,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      );
    } else {
      // Messages bot à gauche (noir)
      return Padding(
        padding: const EdgeInsets.symmetric(vertical: 6),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.start,
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 280),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                decoration: BoxDecoration(
                  color: Colors.black,
                  borderRadius: BorderRadius.circular(18),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      message.text,
                      style: const TextStyle(
                        fontSize: 14,
                        color: Colors.white,
                        height: 1.45,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      _formatTime(message.timestamp),
                      style: TextStyle(
                        color: Colors.white.withOpacity(0.7),
                        fontSize: 11,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      );
    }
  }

  TextSpan _buildFormattedText(String text, bool isUser, Color textColor) {
    final spans = <TextSpan>[];
    final regex = RegExp(r'\*\*(.+?)\*\*');
    int currentIndex = 0;

    for (final match in regex.allMatches(text)) {
      if (match.start > currentIndex) {
        spans.add(TextSpan(text: text.substring(currentIndex, match.start)));
      }
      spans.add(
        TextSpan(
          text: match.group(1),
          style: TextStyle(fontWeight: FontWeight.w700, color: textColor),
        ),
      );
      currentIndex = match.end;
    }

    if (currentIndex < text.length) {
      spans.add(TextSpan(text: text.substring(currentIndex)));
    }

    if (spans.isEmpty) {
      spans.add(TextSpan(text: text));
    }

    return TextSpan(
      style: TextStyle(fontSize: 14, color: textColor, height: 1.45),
      children: spans,
    );
  }

  Widget _buildInputField() {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.fromLTRB(12, 8, 12, 16),
      child: Row(
        children: [
          IconButton(
            icon: const Icon(LucideIcons.paperclip, color: Colors.black),
            onPressed: _attachFile,
          ),
          Expanded(
            child: Container(
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(26),
                border: Border.all(color: Colors.grey.shade300),
              ),
              child: TextField(
                controller: _messageController,
                enabled: true, // Toujours activé
                style: const TextStyle(color: Colors.black87),
                decoration: InputDecoration(
                  hintText: 'Envoyer un message...',
                  hintStyle: TextStyle(color: Colors.grey.shade500),
                  border: InputBorder.none,
                  isCollapsed: true,
                  contentPadding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 14,
                  ),
                ),
                textInputAction: TextInputAction.newline,
                maxLines: null,
                onChanged: (value) {
                  setState(() {
                    _hasText = value.trim().isNotEmpty;
                  });
                },
                onSubmitted: (_) => _sendMessage(),
              ),
            ),
          ),
          const SizedBox(width: 10),
          GestureDetector(
            onTap: _hasText ? _sendMessage : _recordVoice,
            child: Container(
              width: 50,
              height: 50,
              decoration: const BoxDecoration(
                color: Color(0xFF25D366),
                shape: BoxShape.circle,
              ),
              child: Icon(
                _hasText ? LucideIcons.send : Icons.mic,
                color: Colors.white,
                size: _hasText ? 22 : 24,
              ),
            ),
          ),
        ],
      ),
    );
  }

  void _attachFile() {
    // TODO: Implémenter la sélection de fichier
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Pièce jointe à venir'),
        duration: Duration(seconds: 1),
      ),
    );
  }

  void _recordVoice() {
    // TODO: Implémenter l'enregistrement vocal
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Enregistrement vocal à venir'),
        duration: Duration(seconds: 1),
      ),
    );
  }

  void _sendMessage() {
    final message = _messageController.text.trim();
    if (message.isEmpty) return;

    setState(() {
      _messages.add(
        ChatMessage(text: message, isUser: true, timestamp: DateTime.now()),
      );
      _messageController.clear();
      _hasText = false;
      _isLoading = true;
    });

    _scrollToBottom();
    _getBotResponse(message);
  }

  Future<void> _getBotResponse(String userMessage) async {
    try {
      final conversationHistory = _messages
          .skip(_messages.length > 10 ? _messages.length - 10 : 0)
          .map(
            (msg) => {
              'role': msg.isUser ? 'user' : 'assistant',
              'content': msg.text,
            },
          )
          .toList();

      final response = await ChatbotService.sendMessage(
        userMessage,
        'user_123',
        conversationHistory: conversationHistory,
      );

      await Future.delayed(const Duration(milliseconds: 280));

      setState(() {
        _messages.add(
          ChatMessage(text: response, isUser: false, timestamp: DateTime.now()),
        );
        _isLoading = false;
      });
    } catch (error) {
      setState(() {
        _messages.add(
          ChatMessage(
            text:
                'Merci pour votre message ! 😊\n\nJe peux vous aider avec nos services, tarifs, horaires et réservations. Posez-moi une question précise ou choisissez un raccourci ci-dessus. 💅✨',
            isUser: false,
            timestamp: DateTime.now(),
          ),
        );
        _isLoading = false;
      });
    }

    _scrollToBottom();
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 320),
          curve: Curves.easeOut,
        );
      }
    });
  }

  void _showOptions() {
    showModalBottomSheet(
      context: context,
      builder: (context) => Container(
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              leading: const Icon(LucideIcons.eraser, color: Colors.black87),
              title: const Text('Effacer la conversation'),
              onTap: () {
                Navigator.pop(context);
                _clearConversation();
              },
            ),
            ListTile(
              leading: const Icon(LucideIcons.info, color: Colors.black87),
              title: const Text('À propos'),
              onTap: () {
                Navigator.pop(context);
                _showAbout();
              },
            ),
          ],
        ),
      ),
    );
  }

  void _clearConversation() {
    setState(() {
      _messages.clear();
      _isLoading = false;
    });
    _seedIntroMessages();
  }

  void _showAbout() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Assistant Bold Beauty'),
        content: const Text(
          'Votre assistant virtuel pour toutes vos questions sur Bold Beauty Lounge. Sélectionnez un raccourci ou posez votre question librement !',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('OK'),
          ),
        ],
      ),
    );
  }

  String _formatTime(DateTime timestamp) {
    return '${timestamp.hour.toString().padLeft(2, '0')}:${timestamp.minute.toString().padLeft(2, '0')}';
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

class _QuickAction {
  const _QuickAction({required this.label, required this.message});

  final String label;
  final String message;
}
