import 'package:cloud_firestore/cloud_firestore.dart';

class ReviewModel {
  final String id;
  final String customerName;
  final String customerEmail;
  final int rating; // 1-5 stars
  final String comment;
  final List<ReviewReply> replies;
  final DateTime createdAt;
  final DateTime? updatedAt;
  final String? source; // 'google', 'internal', etc.
  final String? sourceId; // ID from Google Reviews if applicable

  ReviewModel({
    required this.id,
    required this.customerName,
    required this.customerEmail,
    required this.rating,
    required this.comment,
    this.replies = const [],
    required this.createdAt,
    this.updatedAt,
    this.source,
    this.sourceId,
  });

  factory ReviewModel.fromFirestore(DocumentSnapshot doc) {
    final data = doc.data() as Map<String, dynamic>;
    return ReviewModel(
      id: doc.id,
      customerName: data['customerName'] ?? '',
      customerEmail: data['customerEmail'] ?? '',
      rating: data['rating'] ?? 5,
      comment: data['comment'] ?? '',
      replies: (data['replies'] as List<dynamic>?)
              ?.map((reply) => ReviewReply.fromMap(reply as Map<String, dynamic>))
              .toList() ??
          [],
      createdAt: (data['createdAt'] as Timestamp).toDate(),
      updatedAt: data['updatedAt'] != null
          ? (data['updatedAt'] as Timestamp).toDate()
          : null,
      source: data['source'],
      sourceId: data['sourceId'],
    );
  }

  Map<String, dynamic> toFirestore() {
    return {
      'customerName': customerName,
      'customerEmail': customerEmail,
      'rating': rating,
      'comment': comment,
      'replies': replies.map((reply) => reply.toMap()).toList(),
      'createdAt': Timestamp.fromDate(createdAt),
      'updatedAt': updatedAt != null ? Timestamp.fromDate(updatedAt!) : null,
      'source': source,
      'sourceId': sourceId,
    };
  }

  ReviewModel copyWith({
    String? id,
    String? customerName,
    String? customerEmail,
    int? rating,
    String? comment,
    List<ReviewReply>? replies,
    DateTime? createdAt,
    DateTime? updatedAt,
    String? source,
    String? sourceId,
  }) {
    return ReviewModel(
      id: id ?? this.id,
      customerName: customerName ?? this.customerName,
      customerEmail: customerEmail ?? this.customerEmail,
      rating: rating ?? this.rating,
      comment: comment ?? this.comment,
      replies: replies ?? this.replies,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
      source: source ?? this.source,
      sourceId: sourceId ?? this.sourceId,
    );
  }

  // Format time ago (e.g., "5 months ago")
  String get timeAgo {
    final now = DateTime.now();
    final difference = now.difference(createdAt);

    if (difference.inDays < 1) {
      return 'Today';
    } else if (difference.inDays < 30) {
      return '${difference.inDays} ${difference.inDays == 1 ? 'day' : 'days'} ago';
    } else if (difference.inDays < 365) {
      final months = (difference.inDays / 30).floor();
      return '$months ${months == 1 ? 'month' : 'months'} ago';
    } else {
      final years = (difference.inDays / 365).floor();
      return '$years ${years == 1 ? 'year' : 'years'} ago';
    }
  }
}

class ReviewReply {
  final String id;
  final String replyText;
  final DateTime createdAt;
  final String? authorName;

  ReviewReply({
    required this.id,
    required this.replyText,
    required this.createdAt,
    this.authorName,
  });

  factory ReviewReply.fromMap(Map<String, dynamic> map) {
    return ReviewReply(
      id: map['id'] ?? '',
      replyText: map['replyText'] ?? '',
      createdAt: map['createdAt'] != null
          ? (map['createdAt'] is Timestamp
              ? (map['createdAt'] as Timestamp).toDate()
              : DateTime.now())
          : DateTime.now(),
      authorName: map['authorName'],
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'replyText': replyText,
      'createdAt': Timestamp.fromDate(createdAt),
      'authorName': authorName,
    };
  }
}

