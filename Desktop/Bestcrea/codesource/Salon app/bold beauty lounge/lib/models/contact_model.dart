import 'package:cloud_firestore/cloud_firestore.dart';

/// Model for user contact (for coin transfers)
class Contact {
  final String id; // contactUid
  final String contactUid;
  final String displayName;
  final String? email;
  final DateTime addedAt;

  Contact({
    required this.id,
    required this.contactUid,
    required this.displayName,
    this.email,
    required this.addedAt,
  });

  factory Contact.fromFirestore(
    DocumentSnapshot<Map<String, dynamic>> doc,
  ) {
    final data = doc.data()!;
    return Contact(
      id: doc.id,
      contactUid: data['contactUid'] ?? doc.id,
      displayName: data['displayName'] ?? '',
      email: data['email'],
      addedAt: (data['addedAt'] as Timestamp).toDate(),
    );
  }

  Map<String, dynamic> toFirestore() {
    return {
      'contactUid': contactUid,
      'displayName': displayName,
      if (email != null) 'email': email,
      'addedAt': FieldValue.serverTimestamp(),
    };
  }
}

