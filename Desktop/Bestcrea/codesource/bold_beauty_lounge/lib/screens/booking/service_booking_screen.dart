import 'package:flutter/material.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:intl/intl.dart';
import '../../services/booking_service.dart';
import '../../services/auth_service.dart';
import 'booking_confirmation_screen.dart';

class ServiceBookingScreen extends StatefulWidget {
  final String? serviceId;
  final String? serviceName;
  final double? servicePrice;

  const ServiceBookingScreen({
    super.key,
    this.serviceId,
    this.serviceName,
    this.servicePrice,
  });

  @override
  State<ServiceBookingScreen> createState() => _ServiceBookingScreenState();
}

class _ServiceBookingScreenState extends State<ServiceBookingScreen> {
  final BookingService _bookingService = BookingService();
  final AuthService _authService = AuthService();

  DateTime? _selectedDate;
  String? _selectedTime;
  String? _selectedServiceId;
  String? _selectedServiceName;
  double? _selectedServicePrice;
  String? _selectedSpecialistId;
  final TextEditingController _notesController = TextEditingController();

  List<Map<String, dynamic>> _services = [];
  List<String> _availableTimeSlots = [];
  bool _isLoading = false;
  bool _isLoadingServices = true;

  @override
  void initState() {
    super.initState();
    _loadServices();
    _checkAuth();
  }

  Future<void> _checkAuth() async {
    final isLoggedIn = await _authService.isLoggedIn();
    if (!isLoggedIn && mounted) {
      // Rediriger vers la page de connexion
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Vous devez être connecté pour réserver'),
          backgroundColor: Colors.orange,
        ),
      );
    }
  }

  Future<void> _loadServices() async {
    setState(() {
      _isLoadingServices = true;
    });

    try {
      final services = await _bookingService.getAvailableServices();

      setState(() {
        _services = services;
        _isLoadingServices = false;
      });

      // Si un service est pré-sélectionné
      if (widget.serviceId != null) {
        final service = _services.firstWhere(
          (s) => s['id'] == widget.serviceId,
          orElse: () => {},
        );
        if (service.isNotEmpty) {
          _selectService(
            service['id'] as String,
            service['name'] as String? ?? widget.serviceName ?? '',
            (service['price'] as num?)?.toDouble() ??
                widget.servicePrice ??
                0.0,
          );
        }
      }
    } catch (e) {
      setState(() {
        _isLoadingServices = false;
      });
    }
  }

  void _selectService(String id, String name, double price) {
    setState(() {
      _selectedServiceId = id;
      _selectedServiceName = name;
      _selectedServicePrice = price;
      _selectedDate = null;
      _selectedTime = null;
      _availableTimeSlots = [];
    });
  }

  Future<void> _selectDate() async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: DateTime.now(),
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 90)),
      locale: const Locale('fr', 'FR'),
    );

    if (picked != null) {
      setState(() {
        _selectedDate = picked;
        _selectedTime = null;
      });
      await _loadAvailableTimeSlots(picked);
    }
  }

  Future<void> _loadAvailableTimeSlots(DateTime date) async {
    setState(() {
      _isLoading = true;
    });

    try {
      final slots = await _bookingService.getAvailableTimeSlots(date);
      setState(() {
        _availableTimeSlots = slots;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _confirmBooking() async {
    if (_selectedServiceId == null ||
        _selectedDate == null ||
        _selectedTime == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'Veuillez sélectionner un service, une date et un créneau',
          ),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    setState(() {
      _isLoading = true;
    });

    try {
      final result = await _bookingService.createBooking(
        serviceId: _selectedServiceId!,
        serviceName: _selectedServiceName!,
        servicePrice: _selectedServicePrice!,
        selectedDate: _selectedDate!,
        selectedTime: _selectedTime!,
        specialistId: _selectedSpecialistId,
        notes: _notesController.text.trim().isEmpty
            ? null
            : _notesController.text.trim(),
      );

      if (result['success'] && mounted) {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(
            builder: (context) => BookingConfirmationScreen(
              bookingId: result['bookingId'] as String,
              serviceName: _selectedServiceName!,
              date: _selectedDate!,
              time: _selectedTime!,
            ),
          ),
        );
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(
                result['message'] ?? 'Erreur lors de la réservation',
              ),
              backgroundColor: Colors.red,
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Erreur: $e'), backgroundColor: Colors.red),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
        title: const Text(
          'Réserver un service',
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Section sélection du service
            const Text(
              'Sélectionner un service',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: Colors.black,
              ),
            ),
            const SizedBox(height: 16),

            if (_isLoadingServices)
              const Center(child: CircularProgressIndicator())
            else
              SizedBox(
                height: 120,
                child: ListView.builder(
                  scrollDirection: Axis.horizontal,
                  itemCount: _services.length,
                  itemBuilder: (context, index) {
                    final service = _services[index];
                    final isSelected = _selectedServiceId == service['id'];
                    final price = (service['price'] as num?)?.toDouble() ?? 0.0;

                    return GestureDetector(
                      onTap: () => _selectService(
                        service['id'] as String,
                        service['name'] as String? ?? '',
                        price,
                      ),
                      child: Container(
                        width: 100,
                        margin: const EdgeInsets.only(right: 12),
                        decoration: BoxDecoration(
                          color: isSelected
                              ? const Color(0xFFE9D7C2)
                              : Colors.grey[200],
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(
                            color: isSelected
                                ? Colors.black
                                : Colors.transparent,
                            width: 2,
                          ),
                        ),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            if (service['img'] != null)
                              ClipRRect(
                                borderRadius: BorderRadius.circular(8),
                                child: Image.network(
                                  service['img'] as String,
                                  width: 60,
                                  height: 60,
                                  fit: BoxFit.cover,
                                ),
                              ),
                            const SizedBox(height: 8),
                            Text(
                              service['name'] as String? ?? '',
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.bold,
                                color: isSelected
                                    ? Colors.black
                                    : Colors.black87,
                              ),
                              textAlign: TextAlign.center,
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                            ),
                            const SizedBox(height: 4),
                            Text(
                              '${price.toStringAsFixed(0)} DH',
                              style: TextStyle(
                                fontSize: 11,
                                color: isSelected
                                    ? Colors.black87
                                    : Colors.grey[700],
                              ),
                            ),
                          ],
                        ),
                      ),
                    );
                  },
                ),
              ),

            const SizedBox(height: 32),

            // Section sélection de la date
            const Text(
              'Sélectionner une date',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: Colors.black,
              ),
            ),
            const SizedBox(height: 16),

            GestureDetector(
              onTap: _selectDate,
              child: Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.grey[100],
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: _selectedDate != null
                        ? const Color(0xFFE9D7C2)
                        : Colors.grey[300]!,
                    width: 2,
                  ),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.calendar_today, color: Color(0xFFE9D7C2)),
                    const SizedBox(width: 12),
                    Text(
                      _selectedDate != null
                          ? DateFormat(
                              'EEEE d MMMM yyyy',
                              'fr_FR',
                            ).format(_selectedDate!)
                          : 'Choisir une date',
                      style: TextStyle(
                        fontSize: 16,
                        color: _selectedDate != null
                            ? Colors.black
                            : Colors.grey[600],
                        fontWeight: _selectedDate != null
                            ? FontWeight.bold
                            : FontWeight.normal,
                      ),
                    ),
                    const Spacer(),
                    const Icon(Icons.arrow_forward_ios, size: 16),
                  ],
                ),
              ),
            ),

            if (_selectedDate != null) ...[
              const SizedBox(height: 32),

              // Section sélection du créneau
              const Text(
                'Sélectionner un créneau',
                style: TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                  color: Colors.black,
                ),
              ),
              const SizedBox(height: 16),

              if (_isLoading)
                const Center(child: CircularProgressIndicator())
              else if (_availableTimeSlots.isEmpty)
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.orange[50],
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Text(
                    'Aucun créneau disponible pour cette date',
                    style: TextStyle(color: Colors.orange),
                  ),
                )
              else
                Wrap(
                  spacing: 12,
                  runSpacing: 12,
                  children: _availableTimeSlots.map((time) {
                    final isSelected = _selectedTime == time;
                    return GestureDetector(
                      onTap: () {
                        setState(() {
                          _selectedTime = time;
                        });
                      },
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 20,
                          vertical: 12,
                        ),
                        decoration: BoxDecoration(
                          color: isSelected
                              ? const Color(0xFFE9D7C2)
                              : Colors.white,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(
                            color: isSelected
                                ? Colors.black
                                : Colors.grey[300]!,
                            width: isSelected ? 2 : 1,
                          ),
                        ),
                        child: Text(
                          time,
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: isSelected
                                ? FontWeight.bold
                                : FontWeight.normal,
                            color: isSelected ? Colors.black : Colors.black87,
                          ),
                        ),
                      ),
                    );
                  }).toList(),
                ),
            ],

            const SizedBox(height: 32),

            // Notes
            const Text(
              'Notes (optionnel)',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: Colors.black,
              ),
            ),
            const SizedBox(height: 16),

            TextField(
              controller: _notesController,
              maxLines: 3,
              decoration: InputDecoration(
                hintText: 'Ajoutez des notes ou des préférences...',
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(
                    color: Color(0xFFE9D7C2),
                    width: 2,
                  ),
                ),
              ),
            ),

            const SizedBox(height: 40),

            // Bouton de confirmation
            SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton(
                onPressed: _isLoading ? null : _confirmBooking,
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFFE9D7C2),
                  foregroundColor: Colors.black,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                child: _isLoading
                    ? const CircularProgressIndicator(color: Colors.black)
                    : const Text(
                        'Confirmer la réservation',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
              ),
            ),

            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }

  @override
  void dispose() {
    _notesController.dispose();
    super.dispose();
  }
}
