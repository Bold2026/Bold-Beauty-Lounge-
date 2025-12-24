import 'package:cloud_firestore/cloud_firestore.dart';
import '../../models/admin/review_model.dart';

class ReviewsRepository {
  final FirebaseFirestore _firestore;

  ReviewsRepository({FirebaseFirestore? firestore})
      : _firestore = firestore ?? FirebaseFirestore.instance;

  static const String collectionPath = 'reviews';

  // Get all reviews
  Stream<List<ReviewModel>> getAllReviews() {
    return _firestore
        .collection(collectionPath)
        .orderBy('createdAt', descending: true)
        .snapshots()
        .map((snapshot) => snapshot.docs
            .map((doc) => ReviewModel.fromFirestore(doc))
            .toList());
  }

  // Get review by ID
  Future<ReviewModel?> getReviewById(String reviewId) async {
    final doc = await _firestore
        .collection(collectionPath)
        .doc(reviewId)
        .get();
    if (!doc.exists) return null;
    return ReviewModel.fromFirestore(doc);
  }

  // Add new review
  Future<String> addReview(ReviewModel review) async {
    final docRef = _firestore.collection(collectionPath).doc();
    await docRef.set(review.copyWith(id: docRef.id).toFirestore());
    return docRef.id;
  }

  // Update review
  Future<void> updateReview(ReviewModel review) async {
    await _firestore
        .collection(collectionPath)
        .doc(review.id)
        .update(review.toFirestore());
  }

  // Delete review
  Future<void> deleteReview(String reviewId) async {
    await _firestore.collection(collectionPath).doc(reviewId).delete();
  }

  // Add reply to review
  Future<void> addReply(String reviewId, ReviewReply reply) async {
    final review = await getReviewById(reviewId);
    if (review == null) return;

    final updatedReplies = [...review.replies, reply];
    await _firestore.collection(collectionPath).doc(reviewId).update({
      'replies': updatedReplies.map((r) => r.toMap()).toList(),
      'updatedAt': FieldValue.serverTimestamp(),
    });
  }

  // Delete reply from review
  Future<void> deleteReply(String reviewId, String replyId) async {
    final review = await getReviewById(reviewId);
    if (review == null) return;

    final updatedReplies = review.replies.where((r) => r.id != replyId).toList();
    await _firestore.collection(collectionPath).doc(reviewId).update({
      'replies': updatedReplies.map((r) => r.toMap()).toList(),
      'updatedAt': FieldValue.serverTimestamp(),
    });
  }
}




