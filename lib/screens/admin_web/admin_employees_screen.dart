import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import 'package:image_picker/image_picker.dart';
import 'dart:io';
import '../../providers/admin/employees_provider.dart';
import '../../models/admin/employee_model.dart';
import '../../repositories/admin/employees_repository.dart';

// Helper to load employee photo from assets
Widget _buildEmployeePhoto(String? photoUrl) {
  if (photoUrl == null || photoUrl.isEmpty) {
    return Container(
      width: 40,
      height: 40,
      decoration: const BoxDecoration(
        color: Colors.grey,
        shape: BoxShape.circle,
      ),
      child: const Icon(Icons.person, color: Colors.white),
    );
  }

  // If it's an asset path
  if (photoUrl.startsWith('assets/')) {
    return CircleAvatar(
      radius: 20,
      backgroundImage: AssetImage(photoUrl),
      onBackgroundImageError: (exception, stackTrace) {
        // Handle error - show placeholder
      },
      child: photoUrl.contains('leila') ||
              photoUrl.contains('nasira') ||
              photoUrl.contains('laarach') ||
              photoUrl.contains('zineb') ||
              photoUrl.contains('bachir') ||
              photoUrl.contains('raja') ||
              photoUrl.contains('Hiyar')
          ? null
          : const Icon(Icons.person, color: Colors.white),
    );
  }

  // If it's a file path (for newly added employees)
  if (photoUrl.startsWith('/') || photoUrl.contains('\\')) {
    return CircleAvatar(
      radius: 20,
      backgroundImage: FileImage(File(photoUrl)),
      onBackgroundImageError: (exception, stackTrace) {
        // Handle error
      },
      child: const Icon(Icons.person, color: Colors.white),
    );
  }

  // Default placeholder
  return Container(
    width: 40,
    height: 40,
    decoration: const BoxDecoration(
      color: Colors.grey,
      shape: BoxShape.circle,
    ),
    child: const Icon(Icons.person, color: Colors.white),
  );
}

class AdminEmployeesScreen extends StatelessWidget {
  const AdminEmployeesScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider(
      create: (_) => EmployeesProvider(
        repository: EmployeesRepository(),
      ),
      child: const _EmployeesContent(),
    );
  }
}

class _EmployeesContent extends StatefulWidget {
  const _EmployeesContent();

  @override
  State<_EmployeesContent> createState() => _EmployeesContentState();
}

class _EmployeesContentState extends State<_EmployeesContent> {
  final TextEditingController _searchController = TextEditingController();
  int _currentPage = 1;
  final int _itemsPerPage = 10;
  bool _isListView = true; // Toggle between list and grid view

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
                  'Employees',
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
                // Title
                const Text(
                  'All Employees',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w600,
                    color: Colors.black,
                  ),
                ),
                const Spacer(),
                // Search field
                SizedBox(
                  width: 300,
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
                        context.read<EmployeesProvider>().searchEmployees(value);
                      },
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                // View toggle buttons
                Container(
                  decoration: BoxDecoration(
                    color: Colors.grey[100],
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      IconButton(
                        icon: const Icon(Icons.view_list),
                        color: _isListView
                            ? const Color(0xFFDDD1BC)
                            : Colors.grey,
                        onPressed: () {
                          setState(() {
                            _isListView = true;
                          });
                        },
                      ),
                      IconButton(
                        icon: const Icon(Icons.grid_view),
                        color: !_isListView
                            ? const Color(0xFFDDD1BC)
                            : Colors.grey,
                        onPressed: () {
                          setState(() {
                            _isListView = false;
                          });
                        },
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 12),
                // Add New button
                ElevatedButton(
                  onPressed: () {
                    _showAddEmployeeDialog(context);
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

          // Employees table
          Expanded(
            child: Consumer<EmployeesProvider>(
              builder: (context, employeesProvider, _) {
                if (employeesProvider.isLoading) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (employeesProvider.error != null) {
                  return Center(
                    child: Text(
                      employeesProvider.error!,
                      style: const TextStyle(color: Colors.red),
                    ),
                  );
                }

                final employees = employeesProvider.employees;
                if (employees.isEmpty) {
                  return const Center(
                    child: Text(
                      'Aucun employé trouvé',
                      style: TextStyle(color: Colors.black54),
                    ),
                  );
                }

                // Pagination
                final totalPages = (employees.length / _itemsPerPage).ceil();
                final startIndex = (_currentPage - 1) * _itemsPerPage;
                final endIndex = startIndex + _itemsPerPage;
                final paginatedEmployees = employees.sublist(
                  startIndex,
                  endIndex > employees.length ? employees.length : endIndex,
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
                          child: _EmployeesTable(employees: paginatedEmployees),
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
                                      : Colors.black87,
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

  void _showAddEmployeeDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => const _AddEmployeeDialog(),
    );
  }
}

class _EmployeesTable extends StatelessWidget {
  final List<EmployeeModel> employees;

  const _EmployeesTable({required this.employees});

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
                Text('EMP ID'),
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
                Text('Role'),
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
                Text('Email'),
                Icon(Icons.arrow_drop_down, size: 16),
              ],
            ),
          ),
          DataColumn(
            label: Row(
              children: [
                Text('Joining Date'),
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
        rows: employees.map((employee) {
          return DataRow(
            cells: [
              DataCell(Text(employee.employeeId)),
              DataCell(Row(
                children: [
                  _buildEmployeePhoto(employee.photoUrl),
                  const SizedBox(width: 8),
                  Text(employee.firstName),
                ],
              )),
              DataCell(Text(employee.gender)),
              DataCell(Text(employee.role)),
              DataCell(Text(employee.phone)),
              DataCell(Text(employee.email)),
              DataCell(Text(DateFormat('yyyy-MM-dd').format(employee.joiningDate))),
              DataCell(_StatusChip(status: employee.status)),
              DataCell(_ActionMenu(employee: employee)),
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

  @override
  Widget build(BuildContext context) {
    final isActive = status.toLowerCase() == 'active';
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: isActive ? Colors.green.withValues(alpha: 0.15) : Colors.red.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        isActive ? 'Active' : 'In-Active',
        style: TextStyle(
          color: isActive ? Colors.green : Colors.red,
          fontSize: 12,
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }
}

class _ActionMenu extends StatelessWidget {
  final EmployeeModel employee;

  const _ActionMenu({required this.employee});

  @override
  Widget build(BuildContext context) {
    final employeesProvider = context.read<EmployeesProvider>();

    return PopupMenuButton<String>(
      icon: const Icon(Icons.more_vert, size: 20),
      onSelected: (value) async {
        switch (value) {
          case 'edit':
            showDialog(
              context: context,
              builder: (context) => _EditEmployeeDialog(employee: employee),
            );
            break;
          case 'toggle_status':
            final newStatus = employee.status == 'active' ? 'inactive' : 'active';
            await employeesProvider.toggleEmployeeStatus(employee.id, newStatus);
            if (context.mounted) {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text('Statut modifié: ${newStatus == 'active' ? 'Active' : 'In-Active'}'),
                  backgroundColor: Colors.green,
                ),
              );
            }
            break;
          case 'delete':
            final deleted = await showDialog<bool>(
              context: context,
              builder: (context) => AlertDialog(
                title: const Text('Supprimer l\'employé'),
                content: Text(
                    'Êtes-vous sûr de vouloir supprimer ${employee.firstName} ? Cette action est irréversible.'),
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
              await employeesProvider.deleteEmployee(employee.id);
              if (context.mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('Employé supprimé'),
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
          value: 'edit',
          child: Row(
            children: [
              Icon(Icons.edit, size: 18),
              SizedBox(width: 8),
              Text('Modifier'),
            ],
          ),
        ),
        PopupMenuItem(
          value: 'toggle_status',
          child: Row(
            children: [
              Icon(
                employee.status == 'active' ? Icons.block : Icons.check_circle,
                size: 18,
                color: employee.status == 'active' ? Colors.orange : Colors.green,
              ),
              const SizedBox(width: 8),
              Text(employee.status == 'active' ? 'Désactiver' : 'Activer'),
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

class _AddEmployeeDialog extends StatefulWidget {
  const _AddEmployeeDialog();

  @override
  State<_AddEmployeeDialog> createState() => _AddEmployeeDialogState();
}

class _AddEmployeeDialogState extends State<_AddEmployeeDialog> {
  final _formKey = GlobalKey<FormState>();
  final _firstNameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _emailController = TextEditingController();
  final _roleController = TextEditingController();
  String _selectedGender = 'Male';
  String? _selectedPhotoPath;
  final ImagePicker _picker = ImagePicker();

  @override
  void dispose() {
    _firstNameController.dispose();
    _phoneController.dispose();
    _emailController.dispose();
    _roleController.dispose();
    super.dispose();
  }

  Future<void> _pickImage() async {
    // For web, we'll use a file picker or asset selection
    // For now, we'll allow manual entry of asset path
    final result = await showDialog<String>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Sélectionner une photo'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              leading: const Icon(Icons.photo_library),
              title: const Text('Depuis la galerie'),
              onTap: () async {
                final XFile? image = await _picker.pickImage(source: ImageSource.gallery);
                Navigator.pop(context, image?.path);
              },
            ),
            const Divider(),
            const Text('Ou entrer le chemin de l\'asset:'),
            const SizedBox(height: 8),
            TextField(
              decoration: const InputDecoration(
                hintText: 'assets/specialists/nom.jpg',
                border: OutlineInputBorder(),
              ),
              onSubmitted: (value) {
                Navigator.pop(context, value);
              },
            ),
          ],
        ),
      ),
    );

    if (result != null) {
      setState(() {
        _selectedPhotoPath = result;
      });
    }
  }

  Future<void> _saveEmployee() async {
    if (!_formKey.currentState!.validate()) return;

    final employeesProvider = context.read<EmployeesProvider>();

    final employee = EmployeeModel(
      id: '', // Will be generated by repository
      firstName: _firstNameController.text.trim(),
      gender: _selectedGender,
      role: _roleController.text.trim(),
      phone: _phoneController.text.trim(),
      email: _emailController.text.trim(),
      photoUrl: _selectedPhotoPath,
      status: 'active',
      joiningDate: DateTime.now(),
      createdAt: DateTime.now(),
    );

    final success = await employeesProvider.addEmployee(employee);

    if (success && mounted) {
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Employé ajouté avec succès'),
          backgroundColor: Colors.green,
        ),
      );
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(employeesProvider.error ?? 'Erreur lors de l\'ajout'),
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
                  'Ajouter un nouvel employé',
                  style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 24),
                // Photo
                Center(
                  child: GestureDetector(
                    onTap: _pickImage,
                    child: Container(
                      width: 120,
                      height: 120,
                      decoration: BoxDecoration(
                        color: Colors.grey[200],
                        shape: BoxShape.circle,
                        border: Border.all(color: Colors.grey, width: 2),
                      ),
                      child: _selectedPhotoPath != null
                          ? ClipOval(
                              child: Image.file(
                                File(_selectedPhotoPath!),
                                fit: BoxFit.cover,
                              ),
                            )
                          : const Icon(Icons.add_a_photo, size: 40),
                    ),
                  ),
                ),
                const SizedBox(height: 24),
                // Prénom
                TextFormField(
                  controller: _firstNameController,
                  decoration: const InputDecoration(
                    labelText: 'Prénom',
                    border: OutlineInputBorder(),
                  ),
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Veuillez entrer le prénom';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 16),
                // Sexe
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
                const SizedBox(height: 16),
                // Fonction
                TextFormField(
                  controller: _roleController,
                  decoration: const InputDecoration(
                    labelText: 'Fonction',
                    border: OutlineInputBorder(),
                  ),
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Veuillez entrer la fonction';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 16),
                // Téléphone
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
                // Courriel
                TextFormField(
                  controller: _emailController,
                  decoration: const InputDecoration(
                    labelText: 'Courriel',
                    border: OutlineInputBorder(),
                  ),
                  keyboardType: TextInputType.emailAddress,
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Veuillez entrer le courriel';
                    }
                    if (!value.contains('@')) {
                      return 'Courriel invalide';
                    }
                    return null;
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
                      onPressed: _saveEmployee,
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


class _EditEmployeeDialog extends StatefulWidget {
  final EmployeeModel employee;

  const _EditEmployeeDialog({required this.employee});

  @override
  State<_EditEmployeeDialog> createState() => _EditEmployeeDialogState();
}

class _EditEmployeeDialogState extends State<_EditEmployeeDialog> {
  late final _formKey = GlobalKey<FormState>();
  late final TextEditingController _firstNameController;
  late final TextEditingController _phoneController;
  late final TextEditingController _emailController;
  late final TextEditingController _roleController;
  late String _selectedGender;
  String? _selectedPhotoPath;
  final ImagePicker _picker = ImagePicker();

  @override
  void initState() {
    super.initState();
    _firstNameController = TextEditingController(text: widget.employee.firstName);
    _phoneController = TextEditingController(text: widget.employee.phone);
    _emailController = TextEditingController(text: widget.employee.email);
    _roleController = TextEditingController(text: widget.employee.role);
    _selectedGender = widget.employee.gender;
    _selectedPhotoPath = widget.employee.photoUrl;
  }

  @override
  void dispose() {
    _firstNameController.dispose();
    _phoneController.dispose();
    _emailController.dispose();
    _roleController.dispose();
    super.dispose();
  }

  Future<void> _pickImage() async {
    final result = await showDialog<String>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Sélectionner une photo'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              leading: const Icon(Icons.photo_library),
              title: const Text('Depuis la galerie'),
              onTap: () async {
                final XFile? image = await _picker.pickImage(source: ImageSource.gallery);
                Navigator.pop(context, image?.path);
              },
            ),
            const Divider(),
            const Text('Ou entrer le chemin de l\'asset:'),
            const SizedBox(height: 8),
            TextField(
              controller: TextEditingController(text: _selectedPhotoPath),
              decoration: const InputDecoration(
                hintText: 'assets/specialists/nom.jpg',
                border: OutlineInputBorder(),
              ),
              onSubmitted: (value) {
                Navigator.pop(context, value);
              },
            ),
          ],
        ),
      ),
    );

    if (result != null) {
      setState(() {
        _selectedPhotoPath = result;
      });
    }
  }

  Future<void> _updateEmployee() async {
    if (!_formKey.currentState!.validate()) return;

    final employeesProvider = context.read<EmployeesProvider>();

    final updatedEmployee = widget.employee.copyWith(
      firstName: _firstNameController.text.trim(),
      gender: _selectedGender,
      role: _roleController.text.trim(),
      phone: _phoneController.text.trim(),
      email: _emailController.text.trim(),
      photoUrl: _selectedPhotoPath,
      updatedAt: DateTime.now(),
    );

    final success = await employeesProvider.updateEmployee(updatedEmployee);

    if (success && mounted) {
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Employé modifié avec succès'),
          backgroundColor: Colors.green,
        ),
      );
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(employeesProvider.error ?? 'Erreur lors de la modification'),
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
                  'Modifier l\'employé',
                  style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 24),
                Center(
                  child: GestureDetector(
                    onTap: _pickImage,
                    child: Container(
                      width: 120,
                      height: 120,
                      decoration: BoxDecoration(
                        color: Colors.grey[200],
                        shape: BoxShape.circle,
                        border: Border.all(color: Colors.grey, width: 2),
                      ),
                      child: _selectedPhotoPath != null && _selectedPhotoPath!.startsWith('assets/')
                          ? ClipOval(
                              child: Image.asset(
                                _selectedPhotoPath!,
                                fit: BoxFit.cover,
                                errorBuilder: (context, error, stackTrace) {
                                  return const Icon(Icons.person, size: 40);
                                },
                              ),
                            )
                          : _selectedPhotoPath != null && (_selectedPhotoPath!.startsWith('/') || _selectedPhotoPath!.contains('\\'))
                              ? ClipOval(
                                  child: Image.file(
                                    File(_selectedPhotoPath!),
                                    fit: BoxFit.cover,
                                    errorBuilder: (context, error, stackTrace) {
                                      return const Icon(Icons.person, size: 40);
                                    },
                                  ),
                                )
                              : const Icon(Icons.add_a_photo, size: 40),
                    ),
                  ),
                ),
                const SizedBox(height: 24),
                TextFormField(
                  controller: _firstNameController,
                  decoration: const InputDecoration(
                    labelText: 'Prénom',
                    border: OutlineInputBorder(),
                  ),
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Veuillez entrer le prénom';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 16),
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
                const SizedBox(height: 16),
                TextFormField(
                  controller: _roleController,
                  decoration: const InputDecoration(
                    labelText: 'Fonction',
                    border: OutlineInputBorder(),
                  ),
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Veuillez entrer la fonction';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 16),
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
                TextFormField(
                  controller: _emailController,
                  decoration: const InputDecoration(
                    labelText: 'Courriel',
                    border: OutlineInputBorder(),
                  ),
                  keyboardType: TextInputType.emailAddress,
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Veuillez entrer le courriel';
                    }
                    if (!value.contains('@')) {
                      return 'Courriel invalide';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 24),
                Row(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    TextButton(
                      onPressed: () => Navigator.pop(context),
                      child: const Text('Annuler'),
                    ),
                    const SizedBox(width: 12),
                    ElevatedButton(
                      onPressed: _updateEmployee,
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
