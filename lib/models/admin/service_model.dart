import 'package:cloud_firestore/cloud_firestore.dart';

class ServiceModel {
  final String id;
  final String name;
  final String description;
  final double price;
  final int duration; // in minutes
  final String category;
  final bool isActive;
  final String? imageUrl;
  final List<String>? roles; // Employee roles that can perform this service
  final DateTime createdAt;
  final DateTime? updatedAt;

  ServiceModel({
    required this.id,
    required this.name,
    required this.description,
    required this.price,
    required this.duration,
    required this.category,
    required this.isActive,
    this.imageUrl,
    this.roles,
    required this.createdAt,
    this.updatedAt,
  });

  factory ServiceModel.fromFirestore(DocumentSnapshot doc) {
    final data = doc.data() as Map<String, dynamic>;
    return ServiceModel(
      id: doc.id,
      name: data['name'] ?? '',
      description: data['description'] ?? '',
      price: (data['price'] ?? 0).toDouble(),
      duration: data['duration'] ?? 30,
      category: data['category'] ?? '',
      isActive: data['isActive'] ?? true,
      imageUrl: data['imageUrl'],
      roles: data['roles'] != null ? List<String>.from(data['roles']) : null,
      createdAt: (data['createdAt'] as Timestamp).toDate(),
      updatedAt: data['updatedAt'] != null
          ? (data['updatedAt'] as Timestamp).toDate()
          : null,
    );
  }

  Map<String, dynamic> toFirestore() {
    return {
      'name': name,
      'description': description,
      'price': price,
      'duration': duration,
      'category': category,
      'isActive': isActive,
      'imageUrl': imageUrl,
      'roles': roles,
      'createdAt': Timestamp.fromDate(createdAt),
      'updatedAt': updatedAt != null ? Timestamp.fromDate(updatedAt!) : null,
    };
  }

  ServiceModel copyWith({
    String? id,
    String? name,
    String? description,
    double? price,
    int? duration,
    String? category,
    bool? isActive,
    String? imageUrl,
    List<String>? roles,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return ServiceModel(
      id: id ?? this.id,
      name: name ?? this.name,
      description: description ?? this.description,
      price: price ?? this.price,
      duration: duration ?? this.duration,
      category: category ?? this.category,
      isActive: isActive ?? this.isActive,
      imageUrl: imageUrl ?? this.imageUrl,
      roles: roles ?? this.roles,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }

  // Generate service ID (ST008, ST007, etc.)
  String get serviceId {
    final hash = id.hashCode.abs();
    final num = (hash % 999) + 1;
    return 'ST${num.toString().padLeft(3, '0')}';
  }

  // Format duration for display
  String get durationDisplay {
    if (duration < 60) {
      return '$duration Minutes';
    } else if (duration == 60) {
      return '1 Hour';
    } else {
      final hours = duration ~/ 60;
      final minutes = duration % 60;
      if (minutes == 0) {
        return '$hours ${hours == 1 ? 'Hour' : 'Hours'}';
      } else {
        return '$hours.${minutes ~/ 10} Hours';
      }
    }
  }
}





