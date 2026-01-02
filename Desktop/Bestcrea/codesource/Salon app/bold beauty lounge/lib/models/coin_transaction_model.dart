import 'package:cloud_firestore/cloud_firestore.dart';

/// Model for Bold Coins transaction
class CoinTransaction {
  final String id;
  final String type; // 'earn' | 'pay' | 'transfer_in' | 'transfer_out' | 'admin_adjustment'
  final int amount; // Positive integer
  final String direction; // 'in' | 'out'
  final DateTime createdAt;
  final String? bookingId; // For earn/pay transactions
  final String? fromUserId; // For transfers
  final String? toUserId; // For transfers
  final String? adminId; // For admin adjustments
  final String? reason; // Optional reason/description
  final String status; // 'success' | 'failed' | 'pending'
  final Map<String, dynamic>? metadata; // Additional data

  CoinTransaction({
    required this.id,
    required this.type,
    required this.amount,
    required this.direction,
    required this.createdAt,
    this.bookingId,
    this.fromUserId,
    this.toUserId,
    this.adminId,
    this.reason,
    this.status = 'success',
    this.metadata,
  });

  factory CoinTransaction.fromFirestore(
    DocumentSnapshot<Map<String, dynamic>> doc,
  ) {
    final data = doc.data()!;
    return CoinTransaction(
      id: doc.id,
      type: data['type'] ?? '',
      amount: (data['amount'] ?? 0).toInt(),
      direction: data['direction'] ?? '',
      createdAt: (data['createdAt'] as Timestamp).toDate(),
      bookingId: data['bookingId'],
      fromUserId: data['fromUserId'],
      toUserId: data['toUserId'],
      adminId: data['adminId'],
      reason: data['reason'],
      status: data['status'] ?? 'success',
      metadata: data['metadata'] != null
          ? Map<String, dynamic>.from(data['metadata'])
          : null,
    );
  }

  Map<String, dynamic> toFirestore() {
    return {
      'type': type,
      'amount': amount,
      'direction': direction,
      'createdAt': FieldValue.serverTimestamp(),
      if (bookingId != null) 'bookingId': bookingId,
      if (fromUserId != null) 'fromUserId': fromUserId,
      if (toUserId != null) 'toUserId': toUserId,
      if (adminId != null) 'adminId': adminId,
      if (reason != null) 'reason': reason,
      'status': status,
      if (metadata != null) 'metadata': metadata,
    };
  }
}

