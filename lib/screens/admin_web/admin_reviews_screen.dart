import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/admin/reviews_provider.dart';
import '../../models/admin/review_model.dart';
import '../../repositories/admin/reviews_repository.dart';

class AdminReviewsScreen extends StatelessWidget {
  const AdminReviewsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider(
      create: (_) => ReviewsProvider(
        repository: ReviewsRepository(),
      )..loadReviews(),
      child: const _ReviewsContent(),
    );
  }
}

class _ReviewsContent extends StatelessWidget {
  const _ReviewsContent();

  @override
  Widget build(BuildContext context) {
    final reviewsProvider = context.watch<ReviewsProvider>();

    return Scaffold(
      backgroundColor: Colors.grey[50],
      body: Column(
        children: [
          // Header
          Container(
            padding: const EdgeInsets.all(24),
            color: Colors.white,
            child: Row(
              children: [
                const Text(
                  'Customer Reviews',
                  style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ],
            ),
          ),
          // Content
          Expanded(
            child: reviewsProvider.isLoading
                ? const Center(child: CircularProgressIndicator())
                : reviewsProvider.error != null
                    ? Center(
                        child: Text(
                          'Erreur: ${reviewsProvider.error}',
                          style: const TextStyle(color: Colors.red),
                        ),
                      )
                    : reviewsProvider.reviews.isEmpty
                        ? const Center(
                            child: Text('Aucun avis trouvé'),
                          )
                        : _buildReviewsGrid(context, reviewsProvider.reviews),
          ),
        ],
      ),
    );
  }

  Widget _buildReviewsGrid(BuildContext context, List<ReviewModel> reviews) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(24),
      child: GridView.builder(
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: 3,
          crossAxisSpacing: 16,
          mainAxisSpacing: 16,
          childAspectRatio: 0.85,
        ),
        itemCount: reviews.length,
        itemBuilder: (context, index) {
          return _ReviewCard(review: reviews[index]);
        },
      ),
    );
  }
}

class _ReviewCard extends StatefulWidget {
  final ReviewModel review;

  const _ReviewCard({required this.review});

  @override
  State<_ReviewCard> createState() => _ReviewCardState();
}

class _ReviewCardState extends State<_ReviewCard> {
  final TextEditingController _replyController = TextEditingController();
  bool _isReplying = false;

  @override
  void dispose() {
    _replyController.dispose();
    super.dispose();
  }

  Future<void> _sendReply() async {
    if (_replyController.text.trim().isEmpty) return;

    final reviewsProvider = context.read<ReviewsProvider>();

    final reply = ReviewReply(
      id: DateTime.now().millisecondsSinceEpoch.toString(),
      replyText: _replyController.text.trim(),
      createdAt: DateTime.now(),
      authorName: 'Bold Beauty Lounge',
    );

    final success = await reviewsProvider.addReply(widget.review.id, reply);

    if (success && mounted) {
      setState(() {
        _replyController.clear();
        _isReplying = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Réponse envoyée avec succès'),
          backgroundColor: Colors.green,
        ),
      );
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(reviewsProvider.error ?? 'Erreur'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 2,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Customer info
            Row(
              children: [
                const CircleAvatar(
                  radius: 20,
                  backgroundColor: Colors.grey,
                  child: Icon(Icons.person, color: Colors.white),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        widget.review.customerName,
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 16,
                        ),
                      ),
                      Text(
                        widget.review.timeAgo,
                        style: TextStyle(
                          color: Colors.grey[600],
                          fontSize: 12,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            // Rating stars
            Row(
              children: List.generate(5, (index) {
                return Icon(
                  index < widget.review.rating
                      ? Icons.star
                      : Icons.star_border,
                  color: Colors.amber,
                  size: 20,
                );
              }),
            ),
            const SizedBox(height: 12),
            // Comment
            Expanded(
              child: Text(
                widget.review.comment,
                style: const TextStyle(fontSize: 14),
                maxLines: 4,
                overflow: TextOverflow.ellipsis,
              ),
            ),
            const SizedBox(height: 12),
            // Previous replies
            if (widget.review.replies.isNotEmpty) ...[
              ...widget.review.replies.map((reply) {
                return Container(
                  margin: const EdgeInsets.only(bottom: 8),
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: Colors.grey[100],
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Row(
                    children: [
                      Expanded(
                        child: Text(
                          reply.replyText,
                          style: const TextStyle(fontSize: 12),
                        ),
                      ),
                      Icon(
                        Icons.link,
                        size: 14,
                        color: Colors.grey[600],
                      ),
                    ],
                  ),
                );
              }),
              const SizedBox(height: 8),
            ],
            // Reply input
            if (_isReplying) ...[
              TextField(
                controller: _replyController,
                decoration: InputDecoration(
                  hintText: 'Write a reply...',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(20),
                  ),
                  contentPadding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 8,
                  ),
                  suffixIcon: IconButton(
                    icon: const Icon(Icons.send, color: Colors.orange),
                    onPressed: _sendReply,
                  ),
                ),
                maxLines: 2,
              ),
            ] else
              InkWell(
                onTap: () {
                  setState(() {
                    _isReplying = true;
                  });
                },
                child: Container(
                  padding: const EdgeInsets.symmetric(vertical: 8),
                  decoration: BoxDecoration(
                    border: Border.all(color: Colors.grey[300]!),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.reply, size: 16, color: Colors.grey[600]),
                      const SizedBox(width: 4),
                      Text(
                        'Reply',
                        style: TextStyle(color: Colors.grey[600]),
                      ),
                    ],
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}




