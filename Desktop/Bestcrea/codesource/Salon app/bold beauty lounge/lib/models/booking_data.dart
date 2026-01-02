/// Modèle temporaire pour stocker les données de réservation avant la création
class BookingData {
  final String serviceId;
  final String serviceName;
  final String? serviceImage;
  final double? servicePrice;
  final DateTime selectedDate;
  final String selectedTime;
  final String? employeeId;
  final String? employeeName;
  final String? notes;
  final int coinsRedeemed; // Bold Coins redeemed for discount

  BookingData({
    required this.serviceId,
    required this.serviceName,
    this.serviceImage,
    this.servicePrice,
    required this.selectedDate,
    required this.selectedTime,
    this.employeeId,
    this.employeeName,
    this.notes,
    this.coinsRedeemed = 0,
  });

  /// Get final price after coin discount (1 coin = 1 MAD)
  double? get finalPrice {
    if (servicePrice == null) return null;
    final discount = coinsRedeemed.toDouble();
    final finalPrice = servicePrice! - discount;
    return finalPrice < 0 ? 0 : finalPrice;
  }

  /// Combine la date et l'heure en un DateTime
  DateTime get dateTime {
    final timeParts = selectedTime.split(':');
    final hour = int.parse(timeParts[0]);
    final minute = timeParts.length > 1 ? int.parse(timeParts[1].split(' ')[0]) : 0;
    
    // Gérer AM/PM si présent
    final isPM = selectedTime.toUpperCase().contains('PM');
    final adjustedHour = isPM && hour != 12 ? hour + 12 : (hour == 12 && !isPM ? 0 : hour);
    
    return DateTime(
      selectedDate.year,
      selectedDate.month,
      selectedDate.day,
      adjustedHour,
      minute,
    );
  }
}

