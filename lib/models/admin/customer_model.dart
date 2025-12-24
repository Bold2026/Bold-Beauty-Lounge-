import 'package:cloud_firestore/cloud_firestore.dart';

class CustomerModel {
  final String id;
  final String name;
  final String email;
  final String phone;
  final String? gender; // 'Male', 'Female', etc.
  final DateTime? lastVisit;
  final DateTime createdAt;
  final DateTime? updatedAt;
  final Map<String, dynamic>? metadata;

  CustomerModel({
    required this.id,
    required this.name,
    required this.email,
    required this.phone,
    this.gender,
    this.lastVisit,
    required this.createdAt,
    this.updatedAt,
    this.metadata,
  });

  factory CustomerModel.fromFirestore(DocumentSnapshot doc) {
    final data = doc.data() as Map<String, dynamic>;
    return CustomerModel(
      id: doc.id,
      name: data['name'] ?? '',
      email: data['email'] ?? '',
      phone: data['phone'] ?? '',
      gender: data['gender'],
      lastVisit: data['lastVisit'] != null
          ? (data['lastVisit'] as Timestamp).toDate()
          : null,
      createdAt: (data['createdAt'] as Timestamp).toDate(),
      updatedAt: data['updatedAt'] != null
          ? (data['updatedAt'] as Timestamp).toDate()
          : null,
      metadata: data['metadata'],
    );
  }

  Map<String, dynamic> toFirestore() {
    return {
      'name': name,
      'email': email,
      'phone': phone,
      'gender': gender,
      'lastVisit': lastVisit != null ? Timestamp.fromDate(lastVisit!) : null,
      'createdAt': Timestamp.fromDate(createdAt),
      'updatedAt': updatedAt != null ? Timestamp.fromDate(updatedAt!) : null,
      'metadata': metadata,
    };
  }

  CustomerModel copyWith({
    String? id,
    String? name,
    String? email,
    String? phone,
    String? gender,
    DateTime? lastVisit,
    DateTime? createdAt,
    DateTime? updatedAt,
    Map<String, dynamic>? metadata,
  }) {
    return CustomerModel(
      id: id ?? this.id,
      name: name ?? this.name,
      email: email ?? this.email,
      phone: phone ?? this.phone,
      gender: gender ?? this.gender,
      lastVisit: lastVisit ?? this.lastVisit,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
      metadata: metadata ?? this.metadata,
    );
  }

  // Generate customer ID (CL336, CL334, etc.)
  // Using hash of Firestore ID to generate a consistent 3-digit number
  String get customerId {
    // Generate a consistent number from the ID
    final hash = id.hashCode.abs();
    final num = (hash % 999) + 1; // Number between 1 and 999
    return 'CL${num.toString().padLeft(3, '0')}';
  }
}

