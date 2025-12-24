import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/admin/customers_provider.dart';
import '../../models/admin/customer_model.dart';
import '../../repositories/admin/customers_repository.dart';

class AdminCustomersScreen extends StatelessWidget {
  const AdminCustomersScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider(
      create: (_) => CustomersProvider(
        repository: CustomersRepository(),
      )..loadCustomers(),
      child: const _CustomersContent(),
    );
  }
}

class _CustomersContent extends StatefulWidget {
  const _CustomersContent();

  @override
  State<_CustomersContent> createState() => _CustomersContentState();
}

class _CustomersContentState extends State<_CustomersContent> {
  final TextEditingController _searchController = TextEditingController();
  int _currentPage = 1;
  final int _itemsPerPage = 10;
  bool _isListView = true;

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  void initState() {
    super.initState();
    _searchController.addListener(() {
      context.read<CustomersProvider>().setSearchQuery(_searchController.text);
    });
  }

  @override
  Widget build(BuildContext context) {
    final customersProvider = context.watch<CustomersProvider>();
    final filteredCustomers = customersProvider.filteredCustomers;

    // Pagination
    final totalPages = (filteredCustomers.length / _itemsPerPage).ceil();
    final startIndex = (_currentPage - 1) * _itemsPerPage;
    final endIndex = startIndex + _itemsPerPage;
    final paginatedCustomers = filteredCustomers.sublist(
      startIndex < filteredCustomers.length ? startIndex : 0,
      endIndex < filteredCustomers.length ? endIndex : filteredCustomers.length,
    );

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
                const Text(
                  'All Customers',
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const Spacer(),
                // Search bar
                Container(
                  width: 300,
                  height: 40,
                  decoration: BoxDecoration(
                    color: Colors.grey[100],
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: TextField(
                    controller: _searchController,
                    decoration: const InputDecoration(
                      hintText: 'Search',
                      prefixIcon: Icon(Icons.search, size: 20),
                      border: InputBorder.none,
                      contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                    ),
                  ),
                ),
                const SizedBox(width: 16),
                // View toggle
                Row(
                  children: [
                    IconButton(
                      icon: Icon(
                        Icons.view_list,
                        color: _isListView ? const Color(0xFFDDD1BC) : Colors.grey,
                      ),
                      onPressed: () {
                        setState(() {
                          _isListView = true;
                        });
                      },
                    ),
                    IconButton(
                      icon: Icon(
                        Icons.grid_view,
                        color: !_isListView ? const Color(0xFFDDD1BC) : Colors.grey,
                      ),
                      onPressed: () {
                        setState(() {
                          _isListView = false;
                        });
                      },
                    ),
                  ],
                ),
                const SizedBox(width: 16),
                // Add New button
                ElevatedButton(
                  onPressed: () => _showAddCustomerDialog(context),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFDDD1BC),
                    foregroundColor: Colors.black,
                    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                  ),
                  child: const Text('Add New'),
                ),
              ],
            ),
          ),
          // Content
          Expanded(
            child: customersProvider.isLoading
                ? const Center(child: CircularProgressIndicator())
                : customersProvider.error != null
                    ? Center(
                        child: Text(
                          'Erreur: ${customersProvider.error}',
                          style: const TextStyle(color: Colors.red),
                        ),
                      )
                    : filteredCustomers.isEmpty
                        ? const Center(
                            child: Text('Aucun client trouvé'),
                          )
                        : _isListView
                            ? _buildListView(paginatedCustomers)
                            : _buildGridView(paginatedCustomers),
          ),
          // Pagination
          if (filteredCustomers.isNotEmpty)
            Container(
              padding: const EdgeInsets.all(16),
              color: Colors.white,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Page $_currentPage of $totalPages',
                    style: const TextStyle(fontSize: 14),
                  ),
                  Row(
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
                      ...List.generate(
                        totalPages > 5 ? 5 : totalPages,
                        (index) {
                          if (totalPages > 5) {
                            if (index == 0) {
                              return _buildPageButton(1);
                            } else if (index == 1) {
                              return _buildPageButton(2);
                            } else if (index == 2) {
                              return _buildPageButton(_currentPage);
                            } else if (index == 3) {
                              return const Text('...');
                            } else {
                              return _buildPageButton(totalPages);
                            }
                          } else {
                            return _buildPageButton(index + 1);
                          }
                        },
                      ),
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
                    ],
                  ),
                ],
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildPageButton(int page) {
    final isCurrentPage = page == _currentPage;
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 4),
      child: TextButton(
        onPressed: () {
          setState(() {
            _currentPage = page;
          });
        },
        style: TextButton.styleFrom(
          backgroundColor: isCurrentPage ? const Color(0xFFDDD1BC) : null,
          foregroundColor: isCurrentPage ? Colors.black : Colors.black87,
          minimumSize: const Size(40, 40),
        ),
        child: Text('$page'),
      ),
    );
  }

  Widget _buildListView(List<CustomerModel> customers) {
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
                Text('Customer'),
                Icon(Icons.arrow_drop_down, size: 16),
              ],
            ),
          ),
          DataColumn(
            label: Row(
              children: [
                Text('Email'),
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
                Text('Last Visit'),
                Icon(Icons.arrow_drop_down, size: 16),
              ],
            ),
          ),
          DataColumn(label: Text('Actions')),
        ],
        rows: customers.map((customer) {
          return DataRow(
            cells: [
              DataCell(
                Text(
                  customer.customerId,
                  style: const TextStyle(
                    color: Colors.black,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ),
              DataCell(Text(customer.name)),
              DataCell(Text(customer.email)),
              DataCell(Text(customer.phone)),
              DataCell(
                Text(
                  customer.lastVisit != null
                      ? DateFormat('yyyy-MM-dd').format(customer.lastVisit!)
                      : '',
                ),
              ),
              DataCell(_ActionMenu(customer: customer)),
            ],
          );
        }).toList(),
      ),
    );
  }

  Widget _buildGridView(List<CustomerModel> customers) {
    return GridView.builder(
      padding: const EdgeInsets.all(16),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 4,
        crossAxisSpacing: 16,
        mainAxisSpacing: 16,
        childAspectRatio: 1.2,
      ),
      itemCount: customers.length,
      itemBuilder: (context, index) {
        final customer = customers[index];
        return Card(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      customer.customerId,
                      style: const TextStyle(
                        color: Colors.black,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    _ActionMenu(customer: customer),
                  ],
                ),
                const SizedBox(height: 8),
                Text(
                  customer.name,
                  style: const TextStyle(
                    fontWeight: FontWeight.bold,
                    fontSize: 16,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  customer.email,
                  style: TextStyle(color: Colors.grey[600], fontSize: 12),
                ),
                const SizedBox(height: 4),
                Text(
                  customer.phone,
                  style: TextStyle(color: Colors.grey[600], fontSize: 12),
                ),
                if (customer.lastVisit != null) ...[
                  const SizedBox(height: 4),
                  Text(
                    'Last Visit: ${DateFormat('yyyy-MM-dd').format(customer.lastVisit!)}',
                    style: TextStyle(color: Colors.grey[600], fontSize: 12),
                  ),
                ],
              ],
            ),
          ),
        );
      },
    );
  }

  void _showAddCustomerDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => const _AddCustomerDialog(),
    );
  }
}

class _ActionMenu extends StatelessWidget {
  final CustomerModel customer;

  const _ActionMenu({required this.customer});

  @override
  Widget build(BuildContext context) {
    final customersProvider = context.read<CustomersProvider>();

    return PopupMenuButton<String>(
      icon: const Icon(Icons.more_vert, size: 20),
      onSelected: (value) async {
        switch (value) {
          case 'edit':
            showDialog(
              context: context,
              builder: (context) => _EditCustomerDialog(customer: customer),
            );
            break;
          case 'delete':
            final deleted = await showDialog<bool>(
              context: context,
              builder: (context) => AlertDialog(
                title: const Text('Supprimer le client'),
                content: Text(
                    'Êtes-vous sûr de vouloir supprimer ${customer.name} ? Cette action est irréversible.'),
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
              await customersProvider.deleteCustomer(customer.id);
              if (context.mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('Client supprimé avec succès'),
                    backgroundColor: Colors.green,
                  ),
                );
              }
            }
            break;
        }
      },
      itemBuilder: (context) => [
        const PopupMenuItem(
          value: 'edit',
          child: Row(
            children: [
              Icon(Icons.edit, size: 18),
              SizedBox(width: 8),
              Text('Modifier'),
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

class _AddCustomerDialog extends StatefulWidget {
  const _AddCustomerDialog();

  @override
  State<_AddCustomerDialog> createState() => _AddCustomerDialogState();
}

class _AddCustomerDialogState extends State<_AddCustomerDialog> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _emailController = TextEditingController();
  String _selectedGender = 'Male';

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _emailController.dispose();
    super.dispose();
  }

  Future<void> _saveCustomer() async {
    if (!_formKey.currentState!.validate()) return;

    final customersProvider = context.read<CustomersProvider>();

    final customer = CustomerModel(
      id: '', // Will be generated by repository
      name: _nameController.text.trim(),
      email: _emailController.text.trim(),
      phone: _phoneController.text.trim(),
      gender: _selectedGender,
      createdAt: DateTime.now(),
    );

    final success = await customersProvider.addCustomer(customer);

    if (success && mounted) {
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Client ajouté avec succès'),
          backgroundColor: Colors.green,
        ),
      );
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(customersProvider.error ?? 'Erreur lors de l\'ajout'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      child: Container(
        width: 600,
        padding: const EdgeInsets.all(24),
        child: Form(
          key: _formKey,
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const Text(
                  'Ajouter un nouveau client',
                  style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 24),
                // Name
                TextFormField(
                  controller: _nameController,
                  decoration: const InputDecoration(
                    labelText: 'Nom complet',
                    border: OutlineInputBorder(),
                  ),
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Veuillez entrer le nom';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 16),
                // Email
                TextFormField(
                  controller: _emailController,
                  decoration: const InputDecoration(
                    labelText: 'Email',
                    border: OutlineInputBorder(),
                  ),
                  keyboardType: TextInputType.emailAddress,
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Veuillez entrer l\'email';
                    }
                    if (!value.contains('@')) {
                      return 'Email invalide';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 16),
                // Phone
                TextFormField(
                  controller: _phoneController,
                  decoration: const InputDecoration(
                    labelText: 'Téléphone',
                    border: OutlineInputBorder(),
                  ),
                  keyboardType: TextInputType.phone,
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Veuillez entrer le téléphone';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 16),
                // Gender
                DropdownButtonFormField<String>(
                  value: _selectedGender,
                  decoration: const InputDecoration(
                    labelText: 'Sexe',
                    border: OutlineInputBorder(),
                  ),
                  items: const [
                    DropdownMenuItem(value: 'Male', child: Text('Homme')),
                    DropdownMenuItem(value: 'Female', child: Text('Femme')),
                  ],
                  onChanged: (value) {
                    setState(() {
                      _selectedGender = value!;
                    });
                  },
                ),
                const SizedBox(height: 24),
                // Buttons
                Row(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    TextButton(
                      onPressed: () => Navigator.pop(context),
                      child: const Text('Annuler'),
                    ),
                    const SizedBox(width: 12),
                    ElevatedButton(
                      onPressed: _saveCustomer,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFFDDD1BC),
                        foregroundColor: Colors.black,
                      ),
                      child: const Text('Ajouter'),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _EditCustomerDialog extends StatefulWidget {
  final CustomerModel customer;

  const _EditCustomerDialog({required this.customer});

  @override
  State<_EditCustomerDialog> createState() => _EditCustomerDialogState();
}

class _EditCustomerDialogState extends State<_EditCustomerDialog> {
  late final _formKey = GlobalKey<FormState>();
  late final TextEditingController _nameController;
  late final TextEditingController _phoneController;
  late final TextEditingController _emailController;
  late String _selectedGender;

  @override
  void initState() {
    super.initState();
    _nameController = TextEditingController(text: widget.customer.name);
    _phoneController = TextEditingController(text: widget.customer.phone);
    _emailController = TextEditingController(text: widget.customer.email);
    _selectedGender = widget.customer.gender ?? 'Male';
  }

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _emailController.dispose();
    super.dispose();
  }

  Future<void> _updateCustomer() async {
    if (!_formKey.currentState!.validate()) return;

    final customersProvider = context.read<CustomersProvider>();

    final updatedCustomer = widget.customer.copyWith(
      name: _nameController.text.trim(),
      email: _emailController.text.trim(),
      phone: _phoneController.text.trim(),
      gender: _selectedGender,
      updatedAt: DateTime.now(),
    );

    final success = await customersProvider.updateCustomer(updatedCustomer);

    if (success && mounted) {
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Client modifié avec succès'),
          backgroundColor: Colors.green,
        ),
      );
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(customersProvider.error ?? 'Erreur lors de la modification'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      child: Container(
        width: 600,
        padding: const EdgeInsets.all(24),
        child: Form(
          key: _formKey,
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const Text(
                  'Modifier le client',
                  style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 24),
                // Name
                TextFormField(
                  controller: _nameController,
                  decoration: const InputDecoration(
                    labelText: 'Nom complet',
                    border: OutlineInputBorder(),
                  ),
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Veuillez entrer le nom';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 16),
                // Email
                TextFormField(
                  controller: _emailController,
                  decoration: const InputDecoration(
                    labelText: 'Email',
                    border: OutlineInputBorder(),
                  ),
                  keyboardType: TextInputType.emailAddress,
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Veuillez entrer l\'email';
                    }
                    if (!value.contains('@')) {
                      return 'Email invalide';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 16),
                // Phone
                TextFormField(
                  controller: _phoneController,
                  decoration: const InputDecoration(
                    labelText: 'Téléphone',
                    border: OutlineInputBorder(),
                  ),
                  keyboardType: TextInputType.phone,
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Veuillez entrer le téléphone';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 16),
                // Gender
                DropdownButtonFormField<String>(
                  value: _selectedGender,
                  decoration: const InputDecoration(
                    labelText: 'Sexe',
                    border: OutlineInputBorder(),
                  ),
                  items: const [
                    DropdownMenuItem(value: 'Male', child: Text('Homme')),
                    DropdownMenuItem(value: 'Female', child: Text('Femme')),
                  ],
                  onChanged: (value) {
                    setState(() {
                      _selectedGender = value!;
                    });
                  },
                ),
                const SizedBox(height: 24),
                // Buttons
                Row(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    TextButton(
                      onPressed: () => Navigator.pop(context),
                      child: const Text('Annuler'),
                    ),
                    const SizedBox(width: 12),
                    ElevatedButton(
                      onPressed: _updateCustomer,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFFDDD1BC),
                        foregroundColor: Colors.black,
                      ),
                      child: const Text('Enregistrer'),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

