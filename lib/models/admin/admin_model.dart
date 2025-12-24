import 'package:cloud_firestore/cloud_firestore.dart';

class AdminModel {
  final String id;
  final String email;
  final String name;
  final String role; // 'admin', 'super_admin'
  final bool isActive;
  final DateTime createdAt;
  final DateTime? lastLoginAt;

  AdminModel({
    required this.id,
    required this.email,
    required this.name,
    required this.role,
    required this.isActive,
    required this.createdAt,
    this.lastLoginAt,
  });

  factory AdminModel.fromFirestore(DocumentSnapshot doc) {
    final data = doc.data() as Map<String, dynamic>;
    return AdminModel(
      id: doc.id,
      email: data['email'] ?? '',
      name: data['name'] ?? '',
      role: data['role'] ?? 'admin',
      isActive: data['isActive'] ?? true,
      createdAt: (data['createdAt'] as Timestamp).toDate(),
      lastLoginAt: data['lastLoginAt'] != null
          ? (data['lastLoginAt'] as Timestamp).toDate()
          : null,
    );
  }

  Map<String, dynamic> toFirestore() {
    return {
      'email': email,
      'name': name,
      'role': role,
      'isActive': isActive,
      'createdAt': Timestamp.fromDate(createdAt),
      'lastLoginAt': lastLoginAt != null
          ? Timestamp.fromDate(lastLoginAt!)
          : null,
    };
  }

  bool get isSuperAdmin => role == 'super_admin';
}








