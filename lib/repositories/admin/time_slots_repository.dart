import 'package:cloud_firestore/cloud_firestore.dart';
import '../../models/admin/time_slot_model.dart';

class TimeSlotsRepository {
  final FirebaseFirestore _firestore;

  TimeSlotsRepository({FirebaseFirestore? firestore})
      : _firestore = firestore ?? FirebaseFirestore.instance;

  static const String collectionPath = 'timeSlots';
  static const String defaultSlotId = 'default';

  // Get time slot configuration
  Future<TimeSlotModel?> getTimeSlot() async {
    final doc =
        await _firestore.collection(collectionPath).doc(defaultSlotId).get();
    if (!doc.exists) {
      // Create default if doesn't exist
      final defaultSlot = TimeSlotModel(
        id: defaultSlotId,
        startHour: 9,
        startMinute: 0,
        endHour: 18,
        endMinute: 0,
        slotDuration: 30,
        disabledDates: [],
        createdAt: DateTime.now(),
      );
      await _firestore
          .collection(collectionPath)
          .doc(defaultSlotId)
          .set(defaultSlot.toFirestore());
      return defaultSlot;
    }
    return TimeSlotModel.fromFirestore(doc);
  }

  // Update time slot configuration
  Future<void> updateTimeSlot(TimeSlotModel timeSlot) async {
    await _firestore
        .collection(collectionPath)
        .doc(timeSlot.id)
        .set(timeSlot.toFirestore(), SetOptions(merge: true));
  }

  // Add disabled date
  Future<void> addDisabledDate(DateTime date) async {
    final timeSlot = await getTimeSlot();
    if (timeSlot == null) return;

    final dateOnly = DateTime(date.year, date.month, date.day);
    if (!timeSlot.disabledDates.any((d) =>
        d.year == dateOnly.year &&
        d.month == dateOnly.month &&
        d.day == dateOnly.day)) {
      final updatedDates = [...timeSlot.disabledDates, dateOnly];
      await updateTimeSlot(timeSlot.copyWith(disabledDates: updatedDates));
    }
  }

  // Remove disabled date
  Future<void> removeDisabledDate(DateTime date) async {
    final timeSlot = await getTimeSlot();
    if (timeSlot == null) return;

    final dateOnly = DateTime(date.year, date.month, date.day);
    final updatedDates = timeSlot.disabledDates
        .where((d) =>
            !(d.year == dateOnly.year &&
                d.month == dateOnly.month &&
                d.day == dateOnly.day))
        .toList();
    await updateTimeSlot(timeSlot.copyWith(disabledDates: updatedDates));
  }
}








