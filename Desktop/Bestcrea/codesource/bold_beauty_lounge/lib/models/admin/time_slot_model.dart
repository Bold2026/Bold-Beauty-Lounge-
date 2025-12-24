import 'package:cloud_firestore/cloud_firestore.dart';

class TimeSlotModel {
  final String id;
  final int startHour; // 0-23
  final int startMinute; // 0-59
  final int endHour; // 0-23
  final int endMinute; // 0-59
  final int slotDuration; // in minutes (30 or 60)
  final List<DateTime> disabledDates; // Dates where booking is disabled
  final DateTime createdAt;
  final DateTime? updatedAt;

  TimeSlotModel({
    required this.id,
    required this.startHour,
    required this.startMinute,
    required this.endHour,
    required this.endMinute,
    required this.slotDuration,
    required this.disabledDates,
    required this.createdAt,
    this.updatedAt,
  });

  factory TimeSlotModel.fromFirestore(DocumentSnapshot doc) {
    final data = doc.data() as Map<String, dynamic>;
    return TimeSlotModel(
      id: doc.id,
      startHour: data['startHour'] ?? 9,
      startMinute: data['startMinute'] ?? 0,
      endHour: data['endHour'] ?? 18,
      endMinute: data['endMinute'] ?? 0,
      slotDuration: data['slotDuration'] ?? 30,
      disabledDates: (data['disabledDates'] as List<dynamic>?)
              ?.map((e) => (e as Timestamp).toDate())
              .toList() ??
          [],
      createdAt: (data['createdAt'] as Timestamp).toDate(),
      updatedAt: data['updatedAt'] != null
          ? (data['updatedAt'] as Timestamp).toDate()
          : null,
    );
  }

  Map<String, dynamic> toFirestore() {
    return {
      'startHour': startHour,
      'startMinute': startMinute,
      'endHour': endHour,
      'endMinute': endMinute,
      'slotDuration': slotDuration,
      'disabledDates': disabledDates
          .map((date) => Timestamp.fromDate(date))
          .toList(),
      'createdAt': Timestamp.fromDate(createdAt),
      'updatedAt': updatedAt != null ? Timestamp.fromDate(updatedAt!) : null,
    };
  }

  TimeSlotModel copyWith({
    String? id,
    int? startHour,
    int? startMinute,
    int? endHour,
    int? endMinute,
    int? slotDuration,
    List<DateTime>? disabledDates,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return TimeSlotModel(
      id: id ?? this.id,
      startHour: startHour ?? this.startHour,
      startMinute: startMinute ?? this.startMinute,
      endHour: endHour ?? this.endHour,
      endMinute: endMinute ?? this.endMinute,
      slotDuration: slotDuration ?? this.slotDuration,
      disabledDates: disabledDates ?? this.disabledDates,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }

  bool isDateDisabled(DateTime date) {
    final dateOnly = DateTime(date.year, date.month, date.day);
    return disabledDates.any((disabledDate) {
      final disabledDateOnly =
          DateTime(disabledDate.year, disabledDate.month, disabledDate.day);
      return dateOnly.isAtSameMomentAs(disabledDateOnly);
    });
  }
}








