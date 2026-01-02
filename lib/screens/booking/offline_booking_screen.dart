import 'dart:io';
import 'dart:math';
import 'dart:typed_data';

import 'package:barcode_widget/barcode_widget.dart';
import 'package:flutter/material.dart';
import 'package:lucide_icons/lucide_icons.dart';
import 'package:path_provider/path_provider.dart';
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';

class OfflineBookingScreen extends StatefulWidget {
  const OfflineBookingScreen({super.key});

  @override
  State<OfflineBookingScreen> createState() => _OfflineBookingScreenState();
}

class _OfflineBookingScreenState extends State<OfflineBookingScreen> {
  final Color _accentColor = const Color(0xFFE9D7C2);
  final Color _backgroundColor = const Color(0xFFFDF7F0);
  final Color _cardColor = Colors.white;

  final List<Map<String, dynamic>> _services = [
    {'name': 'Coiffure Prestige', 'duration': '60 min', 'price': 450.0},
    {'name': 'Rituel Hammam Royal', 'duration': '90 min', 'price': 520.0},
    {'name': 'Massage Signature', 'duration': '75 min', 'price': 480.0},
    {'name': 'Glow Facial Couture', 'duration': '50 min', 'price': 420.0},
  ];

  final List<Map<String, dynamic>> _specialists = [
    {
      'name': 'Laila Bazzi',
      'role': 'Senior Stylist',
      'rating': 4.9,
      'image': 'assets/specialists/leila bazi.jpg',
    },
    {
      'name': 'Nasira Mounir',
      'role': 'Esthéticienne Expert',
      'rating': 4.8,
      'image': 'assets/specialists/nasira mounir.jpg',
    },
    {
      'name': 'Zineb Zineddine',
      'role': 'Massage Master',
      'rating': 4.9,
      'image': 'assets/specialists/zineb zineddine.jpg',
    },
    {
      'name': 'Rajaa Jouani',
      'role': 'Glow Therapy',
      'rating': 4.7,
      'image': 'assets/specialists/raja jouani.jpeg',
    },
  ];

  final List<String> _timeSlots = [
    '09:00',
    '09:30',
    '10:00',
    '10:30',
    '11:00',
    '11:30',
    '12:00',
    '14:00',
    '14:30',
    '15:00',
    '15:30',
    '16:00',
    '16:30',
    '17:00',
    '17:30',
    '18:00',
  ];

  final List<Map<String, dynamic>> _paymentMethods = [
    {
      'id': 'direct',
      'title': 'Paiement direct',
      'subtitle': 'Règlement sur place (espèces ou TPE)',
      'icon': LucideIcons.wallet,
    },
    {
      'id': 'stripe',
      'title': 'Stripe - Carte bancaire',
      'subtitle': 'Paiement sécurisé par carte de crédit',
      'icon': LucideIcons.creditCard,
    },
  ];

  final TextEditingController _nameController = TextEditingController(
    text: 'Client Bold',
  );
  final TextEditingController _phoneController = TextEditingController(
    text: '+212 6 12 34 56 78',
  );
  final TextEditingController _emailController = TextEditingController(
    text: 'client@boldbeauty.com',
  );

  int _currentStep = 0;
  int _selectedServiceIndex = 0;
  int _selectedSpecialistIndex = 0;
  String _selectedTime = '10:00';
  String? _selectedPaymentMethod;
  String? _bookingReference;
  bool _hasChosenService = false;

  DateTime _focusedMonth = DateTime(DateTime.now().year, DateTime.now().month);
  DateTime _selectedDate = DateTime.now();

  final List<String> _monthLabels = const [
    'janvier',
    'février',
    'mars',
    'avril',
    'mai',
    'juin',
    'juillet',
    'août',
    'septembre',
    'octobre',
    'novembre',
    'décembre',
  ];

  final List<String> _weekDays = const [
    'Lu',
    'Ma',
    'Me',
    'Je',
    'Ve',
    'Sa',
    'Di',
  ];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted && !_hasChosenService) {
        _showServicePicker(initial: true);
      }
    });
  }

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _emailController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _backgroundColor,
      appBar: AppBar(
        backgroundColor: _backgroundColor,
        elevation: 0,
        centerTitle: false,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new, color: Colors.black87),
          onPressed: _handleBack,
        ),
        title: Text(
          _currentStep == 0
              ? 'Réserver un rendez-vous'
              : _currentStep == 1
                  ? 'Paiement'
                  : 'Confirmation',
          style: const TextStyle(
            color: Colors.black87,
            fontSize: 20,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
      body: SafeArea(
        child: Column(
          children: [
            Expanded(
              child: AnimatedSwitcher(
                duration: const Duration(milliseconds: 300),
                switchInCurve: Curves.easeIn,
                switchOutCurve: Curves.easeOut,
                child: _buildStepContent(),
              ),
            ),
            _buildBottomBar(),
          ],
        ),
      ),
    );
  }

  Widget _buildStepHeader() {
    final steps = [
      {'title': 'Réservation', 'icon': LucideIcons.calendarDays},
      {'title': 'Paiement', 'icon': LucideIcons.badgeDollarSign},
      {'title': 'Confirmation', 'icon': LucideIcons.badgeCheck},
    ];

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          for (int index = 0; index < steps.length; index++) ...[
            Expanded(
              child: Column(
                children: [
                  Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      color: index <= _currentStep
                          ? _accentColor
                          : Colors.grey.shade300,
                      shape: BoxShape.circle,
                    ),
                    alignment: Alignment.center,
                    child: Icon(
                      steps[index]['icon'] as IconData,
                      size: 20,
                      color: index <= _currentStep
                          ? Colors.black
                          : Colors.grey.shade600,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    steps[index]['title'] as String,
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      color: index == _currentStep
                          ? Colors.black
                          : Colors.black.withOpacity(
                              index < _currentStep ? 0.6 : 0.4,
                            ),
                    ),
                  ),
                ],
              ),
            ),
            if (index < steps.length - 1)
              SizedBox(
                width: 36,
                child: Center(
                  child: Container(
                    height: 2,
                    color: index < _currentStep
                        ? _accentColor
                        : Colors.grey.shade300,
                  ),
                ),
              ),
          ],
        ],
      ),
    );
  }

  Widget _buildStepContent() {
    switch (_currentStep) {
      case 0:
        return _buildBookingStep();
      case 1:
        return _buildPaymentStep();
      case 2:
        return _buildReceiptStep();
      default:
        return const SizedBox.shrink();
    }
  }

  Widget _buildBookingStep() {
    return SingleChildScrollView(
      key: const ValueKey('bookingStep'),
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildServiceSelector(),
          const SizedBox(height: 24),
          if (_hasChosenService) ...[
            _buildStepHeader(),
            const SizedBox(height: 24),
            _buildCalendarSection(),
            const SizedBox(height: 24),
            _buildTimeSection(),
            const SizedBox(height: 24),
            _buildSpecialistSection(),
            const SizedBox(height: 12),
          ] else ...[
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: _cardColor,
                borderRadius: BorderRadius.circular(22),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.04),
                    blurRadius: 16,
                    offset: const Offset(0, 6),
                  ),
                ],
              ),
              child: const Text(
                'Sélectionnez un service pour continuer la réservation.',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: Colors.black87,
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildServiceSelector() {
    final service = _services[_selectedServiceIndex];
    final hasSelection = _hasChosenService;
    return GestureDetector(
      onTap: () => _showServicePicker(),
      child: Container(
        decoration: BoxDecoration(
          color: _cardColor,
          borderRadius: BorderRadius.circular(22),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.04),
              blurRadius: 16,
              offset: const Offset(0, 6),
            ),
          ],
        ),
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: _accentColor.withOpacity(0.25),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                LucideIcons.sparkles,
                color: Colors.black,
                size: 20,
              ),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    hasSelection
                        ? service['name'] as String
                        : 'Choisir un service',
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w700,
                      color: Colors.black87,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    hasSelection
                        ? '${service['duration']} • ${_formatPrice(service['price'] as double)}'
                        : 'Sélectionnez une prestation pour continuer',
                    style: TextStyle(
                      fontSize: 13,
                      color: Colors.black.withOpacity(0.6),
                    ),
                  ),
                ],
              ),
            ),
            const Icon(
              Icons.keyboard_arrow_down_rounded,
              color: Colors.black54,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCalendarSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Sélectionnez la date',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.w700,
            color: Colors.black87,
          ),
        ),
        const SizedBox(height: 12),
        Container(
          decoration: BoxDecoration(
            color: _cardColor,
            borderRadius: BorderRadius.circular(26),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.05),
                blurRadius: 18,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
          child: Column(
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    '${_monthLabels[_focusedMonth.month - 1].capitalize()} ${_focusedMonth.year}',
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w700,
                      color: Colors.black87,
                    ),
                  ),
                  Row(
                    children: [
                      _buildMonthButton(
                        Icons.chevron_left,
                        () => _changeMonth(-1),
                      ),
                      const SizedBox(width: 8),
                      _buildMonthButton(
                        Icons.chevron_right,
                        () => _changeMonth(1),
                      ),
                    ],
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: _weekDays
                    .map(
                      (day) => Expanded(
                        child: Center(
                          child: Text(
                            day,
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                              color: Colors.black.withOpacity(0.4),
                            ),
                          ),
                        ),
                      ),
                    )
                    .toList(),
              ),
              const SizedBox(height: 8),
              GridView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: 7,
                  childAspectRatio: 1.15,
                ),
                itemCount: _buildCalendarDays().length,
                itemBuilder: (context, index) {
                  final date = _buildCalendarDays()[index];
                  final isSelected = _isSameDay(date, _selectedDate);
                  final isToday = _isSameDay(date, DateTime.now());
                  final isCurrentMonth =
                      date != null && date.month == _focusedMonth.month;

                  return GestureDetector(
                    onTap: date == null
                        ? null
                        : () {
                            setState(() {
                              _selectedDate = date;
                            });
                          },
                    child: AnimatedContainer(
                      duration: const Duration(milliseconds: 200),
                      margin: const EdgeInsets.all(4),
                      decoration: BoxDecoration(
                        color: isSelected
                            ? _accentColor
                            : isToday
                                ? _accentColor.withOpacity(0.15)
                                : Colors.transparent,
                        shape: BoxShape.circle,
                      ),
                      child: Center(
                        child: Text(
                          date?.day.toString() ?? '',
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w600,
                            color: !isCurrentMonth
                                ? Colors.black26
                                : isSelected
                                    ? Colors.black
                                    : Colors.black87,
                          ),
                        ),
                      ),
                    ),
                  );
                },
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildTimeSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'Sélectionnez l\'heure',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w700,
                color: Colors.black87,
              ),
            ),
            TextButton(
              onPressed: () {},
              child: const Text(
                'Voir tout',
                style: TextStyle(color: Colors.black54),
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        Wrap(
          spacing: 12,
          runSpacing: 12,
          children: _timeSlots.map((slot) {
            final isSelected = _selectedTime == slot;
            return GestureDetector(
              onTap: () {
                setState(() {
                  _selectedTime = slot;
                });
              },
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                padding: const EdgeInsets.symmetric(
                  horizontal: 18,
                  vertical: 12,
                ),
                decoration: BoxDecoration(
                  color: isSelected ? _accentColor : _cardColor,
                  borderRadius: BorderRadius.circular(24),
                  boxShadow: [
                    if (isSelected)
                      BoxShadow(
                        color: _accentColor.withOpacity(0.45),
                        blurRadius: 18,
                        offset: const Offset(0, 6),
                      ),
                  ],
                  border: Border.all(
                    color: isSelected
                        ? Colors.transparent
                        : Colors.black.withOpacity(0.08),
                  ),
                ),
                child: Text(
                  slot,
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: isSelected ? Colors.black : Colors.black87,
                  ),
                ),
              ),
            );
          }).toList(),
        ),
      ],
    );
  }

  Widget _buildSpecialistSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'Sélectionnez la spécialiste',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w700,
                color: Colors.black87,
              ),
            ),
            TextButton(
              onPressed: () {},
              child: const Text(
                'Voir tout',
                style: TextStyle(color: Colors.black54),
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        SizedBox(
          height: 150,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            itemBuilder: (context, index) {
              final specialist = _specialists[index];
              final isSelected = _selectedSpecialistIndex == index;

              return GestureDetector(
                onTap: () {
                  setState(() {
                    _selectedSpecialistIndex = index;
                  });
                },
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  width: 120,
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: isSelected ? _accentColor : _cardColor,
                    borderRadius: BorderRadius.circular(24),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(
                          isSelected ? 0.15 : 0.05,
                        ),
                        blurRadius: isSelected ? 18 : 12,
                        offset: const Offset(0, 8),
                      ),
                    ],
                    border: Border.all(
                      color: isSelected
                          ? Colors.transparent
                          : Colors.black.withValues(alpha: 0.05),
                    ),
                  ),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      CircleAvatar(
                        radius: 30,
                        backgroundImage: AssetImage(
                          specialist['image'] as String,
                        ),
                        onBackgroundImageError: (_, __) {},
                      ),
                      const SizedBox(height: 12),
                      Text(
                        specialist['name'] as String,
                        textAlign: TextAlign.center,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w700,
                          color: isSelected ? Colors.black : Colors.black87,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        specialist['role'] as String,
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          fontSize: 11,
                          color: isSelected
                              ? Colors.black.withOpacity(0.7)
                              : Colors.black54,
                        ),
                      ),
                      const SizedBox(height: 6),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(
                            Icons.star_rounded,
                            size: 14,
                            color: Colors.black87,
                          ),
                          const SizedBox(width: 4),
                          Text(
                            (specialist['rating'] as double).toStringAsFixed(1),
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              color: isSelected ? Colors.black : Colors.black87,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              );
            },
            separatorBuilder: (_, __) => const SizedBox(width: 12),
            itemCount: _specialists.length,
          ),
        ),
      ],
    );
  }

  Widget _buildPaymentStep() {
    return SingleChildScrollView(
      key: const ValueKey('paymentStep'),
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildStepHeader(),
          const SizedBox(height: 24),
          _buildAppointmentSummary(),
          const SizedBox(height: 20),
          const Text(
            'Choisir le mode de paiement',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w700,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 12),
          ..._paymentMethods.map((method) => _buildPaymentOption(method)),
          const SizedBox(height: 24),
          const Text(
            'Informations client',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w700,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 12),
          _buildTextField(
            controller: _nameController,
            label: 'Nom complet',
            icon: LucideIcons.userCircle2,
          ),
          const SizedBox(height: 12),
          _buildTextField(
            controller: _phoneController,
            label: 'Numéro de téléphone',
            icon: LucideIcons.phone,
            keyboardType: TextInputType.phone,
          ),
          const SizedBox(height: 12),
          _buildTextField(
            controller: _emailController,
            label: 'Adresse e-mail (optionnel)',
            icon: LucideIcons.mail,
            keyboardType: TextInputType.emailAddress,
          ),
          const SizedBox(height: 16),
        ],
      ),
    );
  }

  Widget _buildReceiptStep() {
    final service = _services[_selectedServiceIndex];
    final specialist = _specialists[_selectedSpecialistIndex];

    return SingleChildScrollView(
      key: const ValueKey('receiptStep'),
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildStepHeader(),
          const SizedBox(height: 24),
          Container(
            decoration: BoxDecoration(
              color: _cardColor,
              borderRadius: BorderRadius.circular(26),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.05),
                  blurRadius: 18,
                  offset: const Offset(0, 8),
                ),
              ],
            ),
            padding: const EdgeInsets.all(20),
            child: Column(
              children: [
                Center(
                  child: Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(18),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.08),
                          blurRadius: 16,
                          offset: const Offset(0, 8),
                        ),
                      ],
                    ),
                    child: BarcodeWidget(
                      data: _bookingReference ?? 'BBL-2024-0000',
                      barcode: Barcode.qrCode(),
                      width: 150,
                      height: 150,
                      drawText: false,
                      color: Colors.black87,
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                _buildReceiptRow('N° réservation', _bookingReference ?? '—'),
                _buildReceiptRow('Salon', 'Bold Beauty Lounge'),
                _buildReceiptRow(
                  'Client',
                  _nameController.text.trim().isEmpty
                      ? 'Client Bold'
                      : _nameController.text.trim(),
                ),
                if (_phoneController.text.trim().isNotEmpty)
                  _buildReceiptRow('Téléphone', _phoneController.text.trim()),
                const Divider(height: 32, thickness: 1),
                _buildReceiptRow('Service', service['name'] as String),
                _buildReceiptRow('Date', _formatReadableDate(_selectedDate)),
                _buildReceiptRow('Heure', _selectedTime),
                _buildReceiptRow('Spécialiste', specialist['name'] as String),
                _buildReceiptRow('Durée', service['duration'] as String),
                const Divider(height: 32, thickness: 1),
                _buildReceiptRow(
                  'Mode de paiement',
                  _selectedPaymentMethod == 'stripe'
                      ? 'Stripe - Carte bancaire'
                      : 'Paiement direct',
                ),
                const SizedBox(height: 8),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'Total',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w700,
                        color: Colors.black,
                      ),
                    ),
                    Text(
                      _formatPrice(service['price'] as double),
                      style: const TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.w800,
                        color: Colors.black,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),
          ElevatedButton.icon(
            onPressed: () => _downloadReceipt(),
            style: ElevatedButton.styleFrom(
              backgroundColor: _accentColor,
              foregroundColor: Colors.black,
              minimumSize: const Size.fromHeight(54),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(18),
              ),
            ),
            icon: const Icon(LucideIcons.download),
            label: const Text(
              'Télécharger le reçu',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
            ),
          ),
          const SizedBox(height: 16),
          OutlinedButton.icon(
            onPressed: () => _shareReceipt(),
            style: OutlinedButton.styleFrom(
              foregroundColor: Colors.black87,
              side: BorderSide(color: Colors.black.withOpacity(0.2)),
              minimumSize: const Size.fromHeight(52),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(18),
              ),
            ),
            icon: const Icon(LucideIcons.share2),
            label: const Text(
              'Partager',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
            ),
          ),
          const SizedBox(height: 20),
        ],
      ),
    );
  }

  Widget _buildAppointmentSummary() {
    final service = _services[_selectedServiceIndex];
    final specialist = _specialists[_selectedSpecialistIndex];

    return Container(
      decoration: BoxDecoration(
        color: _cardColor,
        borderRadius: BorderRadius.circular(24),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 16,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Récapitulatif',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w700,
                  color: Colors.black87,
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 12,
                  vertical: 6,
                ),
                decoration: BoxDecoration(
                  color: _accentColor.withOpacity(0.3),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Text(
                  _formatPrice(service['price'] as double),
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w700,
                    color: Colors.black,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          _buildSummaryRow(
            icon: LucideIcons.sparkle,
            title: service['name'] as String,
            subtitle: service['duration'] as String,
          ),
          const SizedBox(height: 12),
          _buildSummaryRow(
            icon: LucideIcons.calendarCheck2,
            title: _formatReadableDate(_selectedDate),
            subtitle: 'À $_selectedTime',
          ),
          const SizedBox(height: 12),
          _buildSummaryRow(
            icon: LucideIcons.userCircle2,
            title: specialist['name'] as String,
            subtitle: specialist['role'] as String,
          ),
        ],
      ),
    );
  }

  Widget _buildPaymentOption(Map<String, dynamic> method) {
    final isSelected = _selectedPaymentMethod == method['id'];
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: _cardColor,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 12,
            offset: const Offset(0, 6),
          ),
        ],
        border: Border.all(
          color: isSelected ? _accentColor : Colors.transparent,
          width: 2,
        ),
      ),
      child: RadioListTile<String>(
        value: method['id'] as String,
        groupValue: _selectedPaymentMethod,
        onChanged: (value) {
          setState(() {
            _selectedPaymentMethod = value;
          });
        },
        activeColor: Colors.black,
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: _accentColor.withOpacity(0.25),
                borderRadius: BorderRadius.circular(16),
              ),
              child: Icon(
                method['icon'] as IconData,
                color: Colors.black,
                size: 22,
              ),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    method['title'] as String,
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w700,
                      color: Colors.black87,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    method['subtitle'] as String,
                    style: TextStyle(
                      fontSize: 13,
                      color: Colors.black.withOpacity(0.6),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String label,
    required IconData icon,
    TextInputType keyboardType = TextInputType.text,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: _cardColor,
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 12,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: TextField(
        controller: controller,
        keyboardType: keyboardType,
        decoration: InputDecoration(
          prefixIcon: Padding(
            padding: const EdgeInsets.all(12.0),
            child: Icon(icon, color: Colors.black54),
          ),
          labelText: label,
          labelStyle: TextStyle(color: Colors.black.withOpacity(0.6)),
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(18),
            borderSide: BorderSide.none,
          ),
          filled: true,
          fillColor: Colors.transparent,
        ),
      ),
    );
  }

  Widget _buildBottomBar() {
    final canContinue = _canProceed();
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 20),
      child: Row(
        children: [
          if (_currentStep > 0)
            Expanded(
              child: OutlinedButton(
                onPressed: () {
                  setState(() {
                    _currentStep--;
                  });
                },
                style: OutlinedButton.styleFrom(
                  foregroundColor: Colors.black87,
                  side: BorderSide(color: Colors.black.withOpacity(0.2)),
                  padding: const EdgeInsets.symmetric(vertical: 18),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(18),
                  ),
                ),
                child: const Text(
                  'Retour',
                  style: TextStyle(fontWeight: FontWeight.w600),
                ),
              ),
            ),
          if (_currentStep > 0) const SizedBox(width: 12),
          Expanded(
            flex: 2,
            child: ElevatedButton(
              onPressed: canContinue ? _handleNext : null,
              style: ElevatedButton.styleFrom(
                backgroundColor:
                    canContinue ? _accentColor : Colors.grey.shade400,
                foregroundColor: Colors.black,
                padding: const EdgeInsets.symmetric(vertical: 18),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(18),
                ),
              ),
              child: Text(
                _currentStep == 2 ? 'Terminer' : 'Continuer',
                style: const TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  void _handleBack() {
    if (_currentStep == 0) {
      Navigator.of(context).maybePop();
    } else {
      setState(() {
        _currentStep--;
      });
    }
  }

  void _handleNext() {
    if (!_canProceed()) return;

    if (_currentStep == 1) {
      _bookingReference =
          'BBL-${Random().nextInt(999999).toString().padLeft(6, '0')}';
    }

    if (_currentStep < 2) {
      setState(() {
        _currentStep++;
      });
    } else {
      Navigator.of(context).pop();
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Merci ! Votre rendez-vous est confirmé.'),
          backgroundColor: Colors.black,
        ),
      );
    }
  }

  bool _canProceed() {
    if (_currentStep == 0) {
      return _hasChosenService &&
          _selectedTime.isNotEmpty &&
          _selectedSpecialistIndex >= 0;
    }
    if (_currentStep == 1) {
      return _selectedPaymentMethod != null &&
          _nameController.text.trim().isNotEmpty &&
          _phoneController.text.trim().isNotEmpty;
    }
    return true;
  }

  void _changeMonth(int offset) {
    setState(() {
      _focusedMonth = DateTime(
        _focusedMonth.year,
        _focusedMonth.month + offset,
      );
      if (!(_focusedMonth.year == _selectedDate.year &&
          _focusedMonth.month == _selectedDate.month)) {
        _selectedDate = DateTime(_focusedMonth.year, _focusedMonth.month, 1);
      }
    });
  }

  List<DateTime?> _buildCalendarDays() {
    final firstDay = DateTime(_focusedMonth.year, _focusedMonth.month, 1);
    final lastDay = DateTime(_focusedMonth.year, _focusedMonth.month + 1, 0);
    final startOffset = (firstDay.weekday + 6) % 7; // Monday = 0
    final totalDays = lastDay.day;
    final totalCells = ((startOffset + totalDays) / 7).ceil() * 7;

    return List.generate(totalCells, (index) {
      final dayIndex = index - startOffset + 1;
      if (dayIndex < 1 || dayIndex > totalDays) {
        return null;
      }
      return DateTime(_focusedMonth.year, _focusedMonth.month, dayIndex);
    });
  }

  bool _isSameDay(DateTime? a, DateTime? b) {
    if (a == null || b == null) return false;
    return a.year == b.year && a.month == b.month && a.day == b.day;
  }

  Widget _buildReceiptRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6.0),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: TextStyle(
              fontSize: 14,
              color: Colors.black.withOpacity(0.6),
            ),
          ),
          Flexible(
            child: Text(
              value,
              textAlign: TextAlign.right,
              style: const TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.w600,
                color: Colors.black87,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSummaryRow({
    required IconData icon,
    required String title,
    required String subtitle,
  }) {
    return Row(
      children: [
        Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: _accentColor.withOpacity(0.25),
            borderRadius: BorderRadius.circular(16),
          ),
          child: Icon(icon, color: Colors.black, size: 20),
        ),
        const SizedBox(width: 16),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: const TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.w700,
                  color: Colors.black87,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                subtitle,
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.black.withOpacity(0.5),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildMonthButton(IconData icon, VoidCallback onTap) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Container(
        width: 36,
        height: 36,
        decoration: BoxDecoration(
          color: _accentColor.withOpacity(0.2),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Icon(icon, color: Colors.black87, size: 20),
      ),
    );
  }

  Future<void> _showServicePicker({bool initial = false}) async {
    final selectedIndex = await showModalBottomSheet<int>(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.all(20.0),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Choisir un service',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
                ),
                const SizedBox(height: 16),
                ...List.generate(_services.length, (index) {
                  final service = _services[index];
                  final isSelected =
                      _selectedServiceIndex == index && _hasChosenService;
                  return ListTile(
                    onTap: () {
                      Navigator.of(context).pop(index);
                    },
                    leading: Icon(
                      isSelected
                          ? Icons.radio_button_checked
                          : Icons.radio_button_unchecked,
                      color: isSelected ? _accentColor : Colors.black54,
                    ),
                    title: Text(
                      service['name'] as String,
                      style: TextStyle(
                        fontWeight: FontWeight.w600,
                        color: isSelected ? Colors.black : Colors.black87,
                      ),
                    ),
                    subtitle: Text(
                      '${service['duration']} • ${_formatPrice(service['price'] as double)}',
                    ),
                  );
                }),
              ],
            ),
          ),
        );
      },
    );
    if (!mounted) return;
    if (selectedIndex != null) {
      setState(() {
        _selectedServiceIndex = selectedIndex;
        _hasChosenService = true;
      });
    } else if (initial && !_hasChosenService) {
      setState(() {
        _hasChosenService = false;
      });
    }
  }

  Future<void> _downloadReceipt() async {
    try {
      final bytes = await _generateReceiptPdfBytes();
      final directory = await getApplicationDocumentsDirectory();
      final filename =
          'Recu_Bold_Beauty_${_bookingReference ?? 'BBL'}_${DateTime.now().millisecondsSinceEpoch}.pdf';
      final file = File('${directory.path}/$filename');
      await file.writeAsBytes(bytes, flush: true);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Reçu enregistré : $filename'),
          backgroundColor: Colors.black,
        ),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Erreur lors de la génération du PDF'),
          backgroundColor: Colors.redAccent,
        ),
      );
    }
  }

  Future<void> _shareReceipt() async {
    try {
      final bytes = await _generateReceiptPdfBytes();
      final filename =
          'Recu_Bold_Beauty_${_bookingReference ?? 'BBL'}_${DateTime.now().millisecondsSinceEpoch}.pdf';
      await Printing.sharePdf(bytes: bytes, filename: filename);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Partage impossible pour le moment.'),
          backgroundColor: Colors.redAccent,
        ),
      );
    }
  }

  String _formatPrice(double price) => '${price.toStringAsFixed(0)} DH';

  String _formatReadableDate(DateTime date) {
    final day = date.day.toString().padLeft(2, '0');
    final month = _monthLabels[date.month - 1];
    return '$day $month ${date.year}'.capitalize();
  }

  Future<Uint8List> _generateReceiptPdfBytes() async {
    final service = _services[_selectedServiceIndex];
    final specialist = _specialists[_selectedSpecialistIndex];
    final paymentLabel = _selectedPaymentMethod == 'stripe'
        ? 'Stripe - Carte bancaire'
        : 'Paiement direct';

    final pdf = pw.Document();
    final accent = PdfColor.fromInt(_accentColor.toARGB32());

    pdf.addPage(
      pw.Page(
        margin: const pw.EdgeInsets.all(24),
        build: (context) {
          return pw.Container(
            decoration: pw.BoxDecoration(
              color: PdfColors.white,
              borderRadius: pw.BorderRadius.circular(16),
              boxShadow: [
                pw.BoxShadow(
                  color: PdfColor.fromInt(
                    Colors.black.withValues(alpha: 0.05).value,
                  ),
                  blurRadius: 6,
                  offset: const PdfPoint(0, 3),
                ),
              ],
            ),
            padding: const pw.EdgeInsets.all(24),
            child: pw.Column(
              crossAxisAlignment: pw.CrossAxisAlignment.start,
              children: [
                pw.Text(
                  'Bold Beauty Lounge',
                  style: pw.TextStyle(
                    fontSize: 22,
                    fontWeight: pw.FontWeight.bold,
                    color: PdfColors.black,
                  ),
                ),
                pw.SizedBox(height: 12),
                pw.Center(
                  child: pw.Container(
                    padding: const pw.EdgeInsets.all(12),
                    decoration: pw.BoxDecoration(
                      color: PdfColors.white,
                      borderRadius: pw.BorderRadius.circular(12),
                      border: pw.Border.all(color: accent, width: 1),
                      boxShadow: [
                        pw.BoxShadow(
                          color: PdfColor.fromInt(
                            Colors.black.withValues(alpha: 0.05).value,
                          ),
                          blurRadius: 4,
                          offset: const PdfPoint(0, 2),
                        ),
                      ],
                    ),
                    child: pw.BarcodeWidget(
                      barcode: pw.Barcode.qrCode(),
                      data: _bookingReference ?? 'BBL-2024-0000',
                      width: 120,
                      height: 120,
                      drawText: false,
                    ),
                  ),
                ),
                pw.SizedBox(height: 22),
                _pdfInfoRow('N° réservation', _bookingReference ?? '—'),
                _pdfInfoRow(
                  'Client',
                  _nameController.text.trim().isEmpty
                      ? 'Client Bold'
                      : _nameController.text.trim(),
                ),
                if (_phoneController.text.trim().isNotEmpty)
                  _pdfInfoRow('Téléphone', _phoneController.text.trim()),
                if (_emailController.text.trim().isNotEmpty)
                  _pdfInfoRow('E-mail', _emailController.text.trim()),
                pw.Divider(
                  height: 24,
                  thickness: 0.8,
                  color: PdfColors.grey400,
                ),
                _pdfInfoRow('Service', service['name'] as String),
                _pdfInfoRow('Durée', service['duration'] as String),
                _pdfInfoRow('Date', _formatReadableDate(_selectedDate)),
                _pdfInfoRow('Heure', _selectedTime),
                _pdfInfoRow('Spécialiste', specialist['name'] as String),
                pw.Divider(
                  height: 24,
                  thickness: 0.8,
                  color: PdfColors.grey400,
                ),
                _pdfInfoRow('Mode de paiement', paymentLabel),
                pw.SizedBox(height: 12),
                pw.Container(
                  padding: const pw.EdgeInsets.symmetric(
                    vertical: 12,
                    horizontal: 16,
                  ),
                  decoration: pw.BoxDecoration(
                    color: accent,
                    borderRadius: pw.BorderRadius.circular(12),
                  ),
                  child: pw.Row(
                    mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
                    children: [
                      pw.Text(
                        'Total',
                        style: pw.TextStyle(
                          fontSize: 16,
                          fontWeight: pw.FontWeight.bold,
                          color: PdfColors.black,
                        ),
                      ),
                      pw.Text(
                        _formatPrice(service['price'] as double),
                        style: pw.TextStyle(
                          fontSize: 18,
                          fontWeight: pw.FontWeight.bold,
                          color: PdfColors.black,
                        ),
                      ),
                    ],
                  ),
                ),
                pw.SizedBox(height: 24),
                pw.Text(
                  'Merci pour votre confiance !',
                  style: pw.TextStyle(
                    fontSize: 14,
                    fontWeight: pw.FontWeight.normal,
                    color: PdfColors.black,
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );

    return pdf.save();
  }

  pw.Widget _pdfInfoRow(String label, String value) {
    return pw.Padding(
      padding: const pw.EdgeInsets.symmetric(vertical: 4),
      child: pw.Row(
        mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
        crossAxisAlignment: pw.CrossAxisAlignment.start,
        children: [
          pw.Text(
            label,
            style: pw.TextStyle(fontSize: 12, color: PdfColors.grey600),
          ),
          pw.SizedBox(width: 8),
          pw.Expanded(
            child: pw.Text(
              value,
              textAlign: pw.TextAlign.right,
              style: pw.TextStyle(
                fontSize: 13,
                fontWeight: pw.FontWeight.bold,
                color: PdfColors.black,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

extension _StringCasing on String {
  String capitalize() {
    if (isEmpty) return this;
    return this[0].toUpperCase() + substring(1);
  }
}
