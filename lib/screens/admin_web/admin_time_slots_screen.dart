import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/admin/time_slots_provider.dart';
import '../../models/admin/time_slot_model.dart';
import '../../repositories/admin/time_slots_repository.dart';

class AdminTimeSlotsScreen extends StatelessWidget {
  const AdminTimeSlotsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider(
      create: (_) => TimeSlotsProvider(
        repository: TimeSlotsRepository(),
      ),
      child: const _TimeSlotsContent(),
    );
  }
}

class _TimeSlotsContent extends StatelessWidget {
  const _TimeSlotsContent();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header
            const Text(
              'Gestion des créneaux horaires',
              style: TextStyle(
                fontSize: 28,
                fontWeight: FontWeight.bold,
                color: Colors.black,
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              'Configurez les heures de travail et les dates désactivées',
              style: TextStyle(
                fontSize: 14,
                color: Colors.black54,
              ),
            ),

            const SizedBox(height: 32),

            // Working hours section
            Consumer<TimeSlotsProvider>(
              builder: (context, timeSlotsProvider, _) {
                if (timeSlotsProvider.isLoading) {
                  return const Center(
                    child: Padding(
                      padding: EdgeInsets.all(48.0),
                      child: CircularProgressIndicator(),
                    ),
                  );
                }

                if (timeSlotsProvider.error != null) {
                  return Center(
                    child: Padding(
                      padding: const EdgeInsets.all(48.0),
                      child: Text(
                        timeSlotsProvider.error!,
                        style: const TextStyle(color: Colors.red),
                      ),
                    ),
                  );
                }

                final timeSlot = timeSlotsProvider.timeSlot;
                if (timeSlot == null) {
                  return const Center(
                    child: Text('Aucune configuration trouvée'),
                  );
                }

                return Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Working hours card
                    Container(
                      padding: const EdgeInsets.all(24),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.05),
                            blurRadius: 10,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Heures de travail',
                            style: TextStyle(
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                              color: Colors.black,
                            ),
                          ),
                          const SizedBox(height: 24),
                          _WorkingHoursForm(timeSlot: timeSlot),
                        ],
                      ),
                    ),

                    const SizedBox(height: 24),

                    // Disabled dates section
                    Container(
                      padding: const EdgeInsets.all(24),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.05),
                            blurRadius: 10,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              const Text(
                                'Dates désactivées',
                                style: TextStyle(
                                  fontSize: 20,
                                  fontWeight: FontWeight.bold,
                                  color: Colors.black,
                                ),
                              ),
                              ElevatedButton.icon(
                                onPressed: () => _showAddDisabledDateDialog(
                                  context,
                                  timeSlotsProvider,
                                ),
                                icon: const Icon(Icons.add, size: 18),
                                label: const Text('Ajouter une date'),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: Colors.black,
                                  foregroundColor: Colors.white,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 16),
                          if (timeSlot.disabledDates.isEmpty)
                            const Padding(
                              padding: EdgeInsets.all(24.0),
                              child: Center(
                                child: Text(
                                  'Aucune date désactivée',
                                  style: TextStyle(color: Colors.black54),
                                ),
                              ),
                            )
                          else
                            Wrap(
                              spacing: 8,
                              runSpacing: 8,
                              children: timeSlot.disabledDates.map((date) {
                                return Chip(
                                  label: Text(
                                    DateFormat('dd/MM/yyyy').format(date),
                                  ),
                                  onDeleted: () async {
                                    await timeSlotsProvider
                                        .removeDisabledDate(date);
                                    if (context.mounted) {
                                      ScaffoldMessenger.of(context).showSnackBar(
                                        const SnackBar(
                                          content: Text('Date activée'),
                                          backgroundColor: Colors.green,
                                        ),
                                      );
                                    }
                                  },
                                  deleteIcon: const Icon(Icons.close, size: 18),
                                );
                              }).toList(),
                            ),
                        ],
                      ),
                    ),
                  ],
                );
              },
            ),
          ],
        ),
      ),
    );
  }

  void _showAddDisabledDateDialog(
    BuildContext context,
    TimeSlotsProvider provider,
  ) async {
    final date = await showDatePicker(
      context: context,
      initialDate: DateTime.now(),
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 365)),
    );

    if (date != null) {
      await provider.addDisabledDate(date);
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Date désactivée'),
            backgroundColor: Colors.green,
          ),
        );
      }
    }
  }
}

class _WorkingHoursForm extends StatefulWidget {
  final TimeSlotModel timeSlot;

  const _WorkingHoursForm({required this.timeSlot});

  @override
  State<_WorkingHoursForm> createState() => _WorkingHoursFormState();
}

class _WorkingHoursFormState extends State<_WorkingHoursForm> {
  late int _startHour;
  late int _startMinute;
  late int _endHour;
  late int _endMinute;
  late int _slotDuration;

  @override
  void initState() {
    super.initState();
    _startHour = widget.timeSlot.startHour;
    _startMinute = widget.timeSlot.startMinute;
    _endHour = widget.timeSlot.endHour;
    _endMinute = widget.timeSlot.endMinute;
    _slotDuration = widget.timeSlot.slotDuration;
  }

  Future<void> _save() async {
    final provider = context.read<TimeSlotsProvider>();
    final updatedSlot = widget.timeSlot.copyWith(
      startHour: _startHour,
      startMinute: _startMinute,
      endHour: _endHour,
      endMinute: _endMinute,
      slotDuration: _slotDuration,
      updatedAt: DateTime.now(),
    );

    final success = await provider.updateTimeSlot(updatedSlot);
    if (success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Configuration enregistrée'),
          backgroundColor: Colors.green,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        // Start time
        Row(
          children: [
            const Expanded(
              flex: 2,
              child: Text(
                'Heure de début',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
            Expanded(
              child: DropdownButtonFormField<int>(
                value: _startHour,
                decoration: const InputDecoration(
                  labelText: 'Heure',
                  border: OutlineInputBorder(),
                ),
                items: List.generate(24, (i) {
                  return DropdownMenuItem(
                    value: i,
                    child: Text('${i.toString().padLeft(2, '0')}h'),
                  );
                }),
                onChanged: (value) {
                  if (value != null) {
                    setState(() => _startHour = value);
                  }
                },
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: DropdownButtonFormField<int>(
                value: _startMinute,
                decoration: const InputDecoration(
                  labelText: 'Minute',
                  border: OutlineInputBorder(),
                ),
                items: [0, 15, 30, 45].map((minute) {
                  return DropdownMenuItem(
                    value: minute,
                    child: Text('${minute.toString().padLeft(2, '0')}min'),
                  );
                }).toList(),
                onChanged: (value) {
                  if (value != null) {
                    setState(() => _startMinute = value);
                  }
                },
              ),
            ),
          ],
        ),

        const SizedBox(height: 16),

        // End time
        Row(
          children: [
            const Expanded(
              flex: 2,
              child: Text(
                'Heure de fin',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
            Expanded(
              child: DropdownButtonFormField<int>(
                value: _endHour,
                decoration: const InputDecoration(
                  labelText: 'Heure',
                  border: OutlineInputBorder(),
                ),
                items: List.generate(24, (i) {
                  return DropdownMenuItem(
                    value: i,
                    child: Text('${i.toString().padLeft(2, '0')}h'),
                  );
                }),
                onChanged: (value) {
                  if (value != null) {
                    setState(() => _endHour = value);
                  }
                },
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: DropdownButtonFormField<int>(
                value: _endMinute,
                decoration: const InputDecoration(
                  labelText: 'Minute',
                  border: OutlineInputBorder(),
                ),
                items: [0, 15, 30, 45].map((minute) {
                  return DropdownMenuItem(
                    value: minute,
                    child: Text('${minute.toString().padLeft(2, '0')}min'),
                  );
                }).toList(),
                onChanged: (value) {
                  if (value != null) {
                    setState(() => _endMinute = value);
                  }
                },
              ),
            ),
          ],
        ),

        const SizedBox(height: 16),

        // Slot duration
        Row(
          children: [
            const Expanded(
              flex: 2,
              child: Text(
                'Durée des créneaux',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
            Expanded(
              child: DropdownButtonFormField<int>(
                value: _slotDuration,
                decoration: const InputDecoration(
                  labelText: 'Durée',
                  border: OutlineInputBorder(),
                ),
                items: const [
                  DropdownMenuItem(value: 30, child: Text('30 minutes')),
                  DropdownMenuItem(value: 60, child: Text('60 minutes')),
                ],
                onChanged: (value) {
                  if (value != null) {
                    setState(() => _slotDuration = value);
                  }
                },
              ),
            ),
          ],
        ),

        const SizedBox(height: 24),

        // Save button
        SizedBox(
          width: double.infinity,
          child: ElevatedButton(
            onPressed: _save,
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.black,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(vertical: 16),
            ),
            child: const Text(
              'Enregistrer les modifications',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
          ),
        ),
      ],
    );
  }
}








