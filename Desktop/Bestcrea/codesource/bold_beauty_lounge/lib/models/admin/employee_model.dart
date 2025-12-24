import 'package:cloud_firestore/cloud_firestore.dart';

class EmployeeModel {
  final String id;
  final String firstName;
  final String gender; // 'Male', 'Female'
  final String role; // 'Directeur Général', 'Esthéticienne Senior', etc.
  final String phone;
  final String email;
  final String? photoUrl; // Path to image in assets/specialists
  final String status; // 'active', 'inactive'
  final DateTime joiningDate;
  final DateTime createdAt;
  final DateTime? updatedAt;

  EmployeeModel({
    required this.id,
    required this.firstName,
    required this.gender,
    required this.role,
    required this.phone,
    required this.email,
    this.photoUrl,
    required this.status,
    required this.joiningDate,
    required this.createdAt,
    this.updatedAt,
  });

  factory EmployeeModel.fromFirestore(DocumentSnapshot doc) {
    final data = doc.data() as Map<String, dynamic>;
    return EmployeeModel(
      id: doc.id,
      firstName: data['firstName'] ?? '',
      gender: data['gender'] ?? 'Male',
      role: data['role'] ?? '',
      phone: data['phone'] ?? '',
      email: data['email'] ?? '',
      photoUrl: data['photoUrl'],
      status: data['status'] ?? 'active',
      joiningDate: (data['joiningDate'] as Timestamp).toDate(),
      createdAt: (data['createdAt'] as Timestamp).toDate(),
      updatedAt: data['updatedAt'] != null
          ? (data['updatedAt'] as Timestamp).toDate()
          : null,
    );
  }

  Map<String, dynamic> toFirestore() {
    return {
      'firstName': firstName,
      'gender': gender,
      'role': role,
      'phone': phone,
      'email': email,
      'photoUrl': photoUrl,
      'status': status,
      'joiningDate': Timestamp.fromDate(joiningDate),
      'createdAt': Timestamp.fromDate(createdAt),
      'updatedAt': updatedAt != null ? Timestamp.fromDate(updatedAt!) : null,
    };
  }

  EmployeeModel copyWith({
    String? id,
    String? firstName,
    String? gender,
    String? role,
    String? phone,
    String? email,
    String? photoUrl,
    String? status,
    DateTime? joiningDate,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return EmployeeModel(
      id: id ?? this.id,
      firstName: firstName ?? this.firstName,
      gender: gender ?? this.gender,
      role: role ?? this.role,
      phone: phone ?? this.phone,
      email: email ?? this.email,
      photoUrl: photoUrl ?? this.photoUrl,
      status: status ?? this.status,
      joiningDate: joiningDate ?? this.joiningDate,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }

  // Generate employee ID (EMP0001, EMP0002, etc.)
  String get employeeId => 'EMP${id.padLeft(4, '0')}';
}






