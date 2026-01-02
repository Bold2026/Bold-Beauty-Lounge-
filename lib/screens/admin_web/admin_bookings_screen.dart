import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/admin/bookings_provider.dart';
import '../../providers/admin/services_provider.dart';
import '../../models/admin/booking_model.dart';
import '../../repositories/admin/bookings_repository.dart';
import '../../repositories/admin/services_repository.dart';

class AdminBookingsScreen extends StatelessWidget {
  const AdminBookingsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(
          create: (_) => BookingsProvider(
            repository: BookingsRepository(),
          ),
        ),
        ChangeNotifierProvider(
          create: (_) => ServicesProvider(
            repository: ServicesRepository(),
          ),
        ),
      ],
      child: const _BookingsContent(),
    );
  }
}

class _BookingsContent extends StatefulWidget {
  const _BookingsContent();

  @override
  State<_BookingsContent> createState() => _BookingsContentState();
}

class _BookingsContentState extends State<_BookingsContent> {
  final TextEditingController _searchController = TextEditingController();
  int _currentPage = 1;
  final int _itemsPerPage = 10;

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      body: Column(
        children: [
          // Header
          Container(
            padding: const EdgeInsets.all(24),
            color: Colors.white,
            child: Row(
              children: [
                IconButton(
                  icon: const Icon(Icons.menu),
                  onPressed: () {},
                ),
                const Text(
                  'Appointments',
                  style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: Colors.black,
                  ),
                ),
              ],
            ),
          ),

          // Search and filters bar
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
            color: Colors.white,
            child: Row(
              children: [
                // Search field
                Expanded(
                  child: Container(
                    decoration: BoxDecoration(
                      color: Colors.grey[100],
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: TextField(
                      controller: _searchController,
                      decoration: const InputDecoration(
                        hintText: 'Search',
                        prefixIcon: Icon(Icons.search, color: Colors.grey),
                        border: InputBorder.none,
                        contentPadding: EdgeInsets.symmetric(
                          horizontal: 16,
                          vertical: 12,
                        ),
                      ),
                      onChanged: (value) {
                        context.read<BookingsProvider>().searchBookings(value);
                      },
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                // Filter icon
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.grey[100],
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Icon(Icons.filter_list, color: Colors.grey),
                ),
                const SizedBox(width: 12),
                // Calendar icon with count
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.grey[100],
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Stack(
                    children: [
                      const Icon(Icons.calendar_today, color: Colors.grey),
                      Positioned(
                        right: 0,
                        top: 0,
                        child: Container(
                          padding: const EdgeInsets.all(4),
                          decoration: const BoxDecoration(
                            color: Colors.orange,
                            shape: BoxShape.circle,
                          ),
                          child: const Text(
                            '8',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 12),
                // Add New button
                ElevatedButton(
                  onPressed: () {
                    // TODO: Implement add new appointment
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFDDD1BC),
                    foregroundColor: Colors.black,
                    padding: const EdgeInsets.symmetric(
                      horizontal: 24,
                      vertical: 12,
                    ),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(8),
                    ),
                  ),
                  child: const Text('Add New'),
                ),
              ],
            ),
          ),

          // Title
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
            color: Colors.white,
            child: const Row(
              children: [
                Text(
                  'All Appointments',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w600,
                    color: Colors.black,
                  ),
                ),
              ],
            ),
          ),

          // Bookings table
          Expanded(
            child: Consumer<BookingsProvider>(
              builder: (context, bookingsProvider, _) {
                if (bookingsProvider.isLoading) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (bookingsProvider.error != null) {
                  return Center(
                    child: Text(
                      bookingsProvider.error!,
                      style: const TextStyle(color: Colors.red),
                    ),
                  );
                }

                final filteredBookings = bookingsProvider.bookings;
                if (filteredBookings.isEmpty) {
                  return const Center(
                    child: Text(
                      'Aucune réservation trouvée',
                      style: TextStyle(color: Colors.black54),
                    ),
                  );
                }

                // Pagination
                final totalPages = (filteredBookings.length / _itemsPerPage).ceil();
                final startIndex = (_currentPage - 1) * _itemsPerPage;
                final endIndex = startIndex + _itemsPerPage;
                final paginatedBookings = filteredBookings.sublist(
                  startIndex,
                  endIndex > filteredBookings.length
                      ? filteredBookings.length
                      : endIndex,
                );

                return Column(
                  children: [
                    Expanded(
                      child: SingleChildScrollView(
                        padding: const EdgeInsets.all(24),
                        child: Container(
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(12),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withValues(alpha: 0.05),
                                blurRadius: 10,
                                offset: const Offset(0, 2),
                              ),
                            ],
                          ),
                          child: _BookingsTable(bookings: paginatedBookings),
                        ),
                      ),
                    ),
                    // Pagination
                    Container(
                      padding: const EdgeInsets.all(16),
                      color: Colors.white,
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          TextButton(
                            onPressed: _currentPage > 1
                                ? () {
                                    setState(() {
                                      _currentPage--;
                                    });
                                  }
                                : null,
                            child: const Text('Previous'),
                          ),
                          const SizedBox(width: 8),
                          ...List.generate(
                            totalPages > 5 ? 5 : totalPages,
                            (index) {
                              if (totalPages > 5 && index == 3) {
                                return const Padding(
                                  padding: EdgeInsets.symmetric(horizontal: 8),
                                  child: Text('...'),
                                );
                              }
                              final pageNum = totalPages > 5 && index > 3
                                  ? totalPages
                                  : index + 1;
                              return Padding(
                                padding: const EdgeInsets.symmetric(horizontal: 4),
                                child: TextButton(
                                  onPressed: () {
                                    setState(() {
                                      _currentPage = pageNum;
                                    });
                                  },
                                  style: TextButton.styleFrom(
                                    backgroundColor: _currentPage == pageNum
                                        ? const Color(0xFFDDD1BC)
                                        : Colors.transparent,
                                    foregroundColor: _currentPage == pageNum
                                        ? Colors.black
                                        : Colors.black87,
                                    minimumSize: const Size(40, 40),
                                  ),
                                  child: Text('$pageNum'),
                                ),
                              );
                            },
                          ),
                          if (totalPages > 5)
                            Padding(
                              padding: const EdgeInsets.symmetric(horizontal: 4),
                              child: TextButton(
                                onPressed: () {
                                  setState(() {
                                    _currentPage = totalPages;
                                  });
                                },
                                style: TextButton.styleFrom(
                                  backgroundColor: _currentPage == totalPages
                                      ? const Color(0xFFDDD1BC)
                                      : Colors.transparent,
                                  foregroundColor: _currentPage == totalPages
                                      ? Colors.black
                                      : Colors.black,
                                  minimumSize: const Size(40, 40),
                                ),
                                child: Text('$totalPages'),
                              ),
                            ),
                          const SizedBox(width: 8),
                          TextButton(
                            onPressed: _currentPage < totalPages
                                ? () {
                                    setState(() {
                                      _currentPage++;
                                    });
                                  }
                                : null,
                            child: const Text('Next'),
                          ),
                          const SizedBox(width: 16),
                          Text(
                            'Page $_currentPage of $totalPages',
                            style: const TextStyle(color: Colors.black54),
                          ),
                        ],
                      ),
                    ),
                  ],
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}

class _BookingsTable extends StatelessWidget {
  final List<BookingModel> bookings;

  const _BookingsTable({required this.bookings});

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: DataTable(
        headingRowColor: WidgetStateProperty.all(Colors.grey[50]),
        columns: const [
          DataColumn(
            label: Row(
              children: [
                Text('ID'),
                Icon(Icons.arrow_drop_down, size: 16),
              ],
            ),
          ),
          DataColumn(
            label: Row(
              children: [
                Text('Date'),
                Icon(Icons.arrow_drop_down, size: 16),
              ],
            ),
          ),
          DataColumn(
            label: Row(
              children: [
                Text('Time'),
                Icon(Icons.arrow_drop_down, size: 16),
              ],
            ),
          ),
          DataColumn(
            label: Row(
              children: [
                Text('Name'),
                Icon(Icons.arrow_drop_down, size: 16),
              ],
            ),
          ),
          DataColumn(
            label: Row(
              children: [
                Text('Gender'),
                Icon(Icons.arrow_drop_down, size: 16),
              ],
            ),
          ),
          DataColumn(
            label: Row(
              children: [
                Text('Phone'),
                Icon(Icons.arrow_drop_down, size: 16),
              ],
            ),
          ),
          DataColumn(
            label: Row(
              children: [
                Text('Service'),
                Icon(Icons.arrow_drop_down, size: 16),
              ],
            ),
          ),
          DataColumn(
            label: Row(
              children: [
                Text('Stylist'),
                Icon(Icons.arrow_drop_down, size: 16),
              ],
            ),
          ),
          DataColumn(
            label: Row(
              children: [
                Text('Status'),
                Icon(Icons.arrow_drop_down, size: 16),
              ],
            ),
          ),
          DataColumn(label: Text('Actions')),
        ],
        rows: bookings.map((booking) {
          return DataRow(
            cells: [
              DataCell(Text('#APT${booking.id.substring(0, 3).toUpperCase()}')),
              DataCell(Text(DateFormat('yyyy-MM-dd').format(booking.date))),
              DataCell(Text(booking.time)),
              DataCell(Text(booking.userName)),
              DataCell(Text(booking.userGender ?? 'N/A')),
              DataCell(Text(booking.userPhone)),
              DataCell(Text(booking.serviceName)),
              DataCell(Text(booking.employeeName ?? 'N/A')),
              DataCell(_StatusChip(status: booking.status)),
              DataCell(_ActionMenu(booking: booking)),
            ],
          );
        }).toList(),
      ),
    );
  }
}

class _StatusChip extends StatelessWidget {
  final String status;

  const _StatusChip({required this.status});

  Color get _color {
    switch (status.toLowerCase()) {
      case 'completed':
        return Colors.green;
      case 'confirmed':
        return Colors.blue;
      case 'pending':
        return Colors.orange;
      case 'cancelled':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }

  String get _label {
    switch (status.toLowerCase()) {
      case 'completed':
        return 'Completed';
      case 'confirmed':
        return 'Confirmed';
      case 'pending':
        return 'Pending';
      case 'cancelled':
        return 'Cancelled';
      default:
        return status;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: _color.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        _label,
        style: TextStyle(
          color: _color,
          fontSize: 12,
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }
}

class _ActionMenu extends StatelessWidget {
  final BookingModel booking;

  const _ActionMenu({required this.booking});

  @override
  Widget build(BuildContext context) {
    final bookingsProvider = context.read<BookingsProvider>();

    return PopupMenuButton<String>(
      icon: const Icon(Icons.more_vert, size: 20),
      onSelected: (value) async {
        switch (value) {
          case 'view':
            // TODO: Show booking details
            break;
          case 'edit':
            // TODO: Edit booking
            break;
          case 'confirm':
            if (booking.status == 'pending') {
              await bookingsProvider.confirmBooking(booking.id);
              if (context.mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('Réservation confirmée'),
                    backgroundColor: Colors.green,
                  ),
                );
              }
            }
            break;
          case 'cancel':
            if (booking.status != 'cancelled' &&
                booking.status != 'completed') {
              final cancelled = await showDialog<bool>(
                context: context,
                builder: (context) => AlertDialog(
                  title: const Text('Annuler la réservation'),
                  content: Text(
                      'Voulez-vous annuler la réservation de ${booking.userName} ?'),
                  actions: [
                    TextButton(
                      onPressed: () => Navigator.pop(context, false),
                      child: const Text('Non'),
                    ),
                    ElevatedButton(
                      onPressed: () => Navigator.pop(context, true),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.red,
                      ),
                      child: const Text('Annuler'),
                    ),
                  ],
                ),
              );

              if (cancelled == true) {
                await bookingsProvider.cancelBooking(booking.id);
                if (context.mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('Réservation annulée'),
                      backgroundColor: Colors.red,
                    ),
                  );
                }
              }
            }
            break;
          case 'delete':
            final deleted = await showDialog<bool>(
              context: context,
              builder: (context) => AlertDialog(
                title: const Text('Supprimer la réservation'),
                content: const Text(
                    'Êtes-vous sûr de vouloir supprimer cette réservation ? Cette action est irréversible.'),
                actions: [
                  TextButton(
                    onPressed: () => Navigator.pop(context, false),
                    child: const Text('Annuler'),
                  ),
                  ElevatedButton(
                    onPressed: () => Navigator.pop(context, true),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.red,
                    ),
                    child: const Text('Supprimer'),
                  ),
                ],
              ),
            );

            if (deleted == true) {
              await bookingsProvider.deleteBooking(booking.id);
              if (context.mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('Réservation supprimée'),
                    backgroundColor: Colors.red,
                  ),
                );
              }
            }
            break;
        }
      },
      itemBuilder: (context) => [
        const PopupMenuItem(
          value: 'view',
          child: Row(
            children: [
              Icon(Icons.visibility, size: 18),
              SizedBox(width: 8),
              Text('Voir détails'),
            ],
          ),
        ),
        if (booking.status == 'pending')
          const PopupMenuItem(
            value: 'confirm',
            child: Row(
              children: [
                Icon(Icons.check, size: 18, color: Colors.green),
                SizedBox(width: 8),
                Text('Confirmer'),
              ],
            ),
          ),
        if (booking.status != 'cancelled' && booking.status != 'completed')
          const PopupMenuItem(
            value: 'cancel',
            child: Row(
              children: [
                Icon(Icons.cancel, size: 18, color: Colors.orange),
                SizedBox(width: 8),
                Text('Annuler'),
              ],
            ),
          ),
        const PopupMenuItem(
          value: 'delete',
          child: Row(
            children: [
              Icon(Icons.delete, size: 18, color: Colors.red),
              SizedBox(width: 8),
              Text('Supprimer'),
            ],
          ),
        ),
      ],
    );
  }
}
