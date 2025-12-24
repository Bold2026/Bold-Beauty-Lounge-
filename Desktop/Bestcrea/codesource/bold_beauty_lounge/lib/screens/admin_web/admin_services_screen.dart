import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:image_picker/image_picker.dart';
import '../../providers/admin/services_provider.dart';
import '../../providers/admin/categories_provider.dart';
import '../../models/admin/service_model.dart';
import '../../models/admin/category_model.dart';
import '../../repositories/admin/services_repository.dart';
import '../../repositories/admin/categories_repository.dart';

class AdminServicesScreen extends StatelessWidget {
  const AdminServicesScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(
          create: (_) => ServicesProvider(
            repository: ServicesRepository(),
          ),
        ),
        ChangeNotifierProvider(
          create: (_) => CategoriesProvider(
            repository: CategoriesRepository(),
          ),
        ),
      ],
      child: const _ServicesContent(),
    );
  }
}

class _ServicesContent extends StatefulWidget {
  const _ServicesContent();

  @override
  State<_ServicesContent> createState() => _ServicesContentState();
}

class _ServicesContentState extends State<_ServicesContent> {
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
                const Text(
                  'Services',
                  style: TextStyle(
                    fontSize: 24,
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
                // Add New button
                ElevatedButton(
                  onPressed: () => _showAddServiceDialog(context),
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
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Categories Section
                  _buildCategoriesSection(context),
                  const SizedBox(height: 32),
                  // Services Section
                  _buildServicesSection(context),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCategoriesSection(BuildContext context) {
    final categoriesProvider = context.watch<CategoriesProvider>();
    final categories = categoriesProvider.activeCategories;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'Categories',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
              ),
            ),
            TextButton.icon(
              onPressed: () => _showAddCategoryDialog(context),
              icon: const Icon(Icons.add, size: 18),
              label: const Text('Add Category'),
              style: TextButton.styleFrom(
                foregroundColor: Colors.black87,
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),
        SizedBox(
          height: 120,
          child: categoriesProvider.isLoading
              ? const Center(child: CircularProgressIndicator())
              : categories.isEmpty
                  ? const Center(
                      child: Text('Aucune catégorie trouvée'),
                    )
                  : ListView.builder(
                      scrollDirection: Axis.horizontal,
                      itemCount: categories.length,
                      itemBuilder: (context, index) {
                        final category = categories[index];
                        return _CategoryCard(category: category);
                      },
                    ),
        ),
      ],
    );
  }

  Widget _buildServicesSection(BuildContext context) {
    final servicesProvider = context.watch<ServicesProvider>();
    final services = servicesProvider.services;

    // Filter by search
    final filteredServices = _searchController.text.isEmpty
        ? services
        : services.where((service) {
            final query = _searchController.text.toLowerCase();
            return service.name.toLowerCase().contains(query) ||
                service.category.toLowerCase().contains(query) ||
                service.serviceId.toLowerCase().contains(query);
          }).toList();

    // Pagination
    final totalPages = (filteredServices.length / _itemsPerPage).ceil();
    final startIndex = (_currentPage - 1) * _itemsPerPage;
    final endIndex = startIndex + _itemsPerPage;
    final paginatedServices = filteredServices.sublist(
      startIndex < filteredServices.length ? startIndex : 0,
      endIndex < filteredServices.length ? endIndex : filteredServices.length,
    );

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Services',
          style: TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: 16),
        servicesProvider.isLoading
            ? const Center(child: CircularProgressIndicator())
            : servicesProvider.error != null
                ? Center(
                    child: Text(
                      'Erreur: ${servicesProvider.error}',
                      style: const TextStyle(color: Colors.red),
                    ),
                  )
                : filteredServices.isEmpty
                    ? const Center(
                        child: Text('Aucun service trouvé'),
                      )
                    : Column(
                        children: [
                          _buildServicesTable(paginatedServices),
                          if (filteredServices.isNotEmpty)
                            _buildPagination(totalPages),
                        ],
                      ),
      ],
    );
  }

  Widget _buildServicesTable(List<ServiceModel> services) {
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
                Text('Name'),
                Icon(Icons.arrow_drop_down, size: 16),
              ],
            ),
          ),
          DataColumn(
            label: Row(
              children: [
                Text('Category'),
                Icon(Icons.arrow_drop_down, size: 16),
              ],
            ),
          ),
          DataColumn(
            label: Row(
              children: [
                Text('Duration'),
                Icon(Icons.arrow_drop_down, size: 16),
              ],
            ),
          ),
          DataColumn(
            label: Row(
              children: [
                Text('Price(\$)'),
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
        rows: services.map((service) {
          return DataRow(
            cells: [
              DataCell(
                Text(
                  service.serviceId,
                  style: const TextStyle(
                    color: Colors.black,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ),
              DataCell(Text(service.name)),
              DataCell(Text(service.category)),
              DataCell(Text(service.durationDisplay)),
              DataCell(Text(service.price.toStringAsFixed(0))),
              DataCell(
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: service.isActive
                        ? Colors.green.withValues(alpha: 0.1)
                        : Colors.red.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    service.isActive ? 'Active' : 'Inactive',
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      color: service.isActive ? Colors.green : Colors.red,
                    ),
                  ),
                ),
              ),
              DataCell(_ServiceActionMenu(service: service)),
            ],
          );
        }).toList(),
      ),
    );
  }

  Widget _buildPagination(int totalPages) {
    return Container(
      padding: const EdgeInsets.all(16),
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
                      return _buildPageButton(1, totalPages);
                    } else if (index == 1) {
                      return _buildPageButton(2, totalPages);
                    } else if (index == 2) {
                      return _buildPageButton(_currentPage, totalPages);
                    } else if (index == 3) {
                      return const Text('...');
                    } else {
                      return _buildPageButton(totalPages, totalPages);
                    }
                  } else {
                    return _buildPageButton(index + 1, totalPages);
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
    );
  }

  Widget _buildPageButton(int page, int totalPages) {
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

  void _showAddServiceDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => const _AddServiceDialog(),
    );
  }

  void _showAddCategoryDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => const _AddCategoryDialog(),
    );
  }
}

class _CategoryCard extends StatelessWidget {
  final CategoryModel category;

  const _CategoryCard({required this.category});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 100,
      margin: const EdgeInsets.only(right: 16),
      child: Column(
        children: [
          Container(
            width: 100,
            height: 100,
            decoration: BoxDecoration(
              color: Colors.grey[200],
              borderRadius: BorderRadius.circular(8),
            ),
            child: category.imageUrl != null && category.imageUrl!.startsWith('assets/')
                ? ClipRRect(
                    borderRadius: BorderRadius.circular(8),
                    child: Image.asset(
                      category.imageUrl!,
                      fit: BoxFit.cover,
                      errorBuilder: (context, error, stackTrace) {
                        return const Icon(Icons.category, size: 40);
                      },
                    ),
                  )
                : const Icon(Icons.category, size: 40),
          ),
          const SizedBox(height: 8),
          Text(
            category.name,
            style: const TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w500,
            ),
            textAlign: TextAlign.center,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }
}

class _ServiceActionMenu extends StatelessWidget {
  final ServiceModel service;

  const _ServiceActionMenu({required this.service});

  @override
  Widget build(BuildContext context) {
    final servicesProvider = context.read<ServicesProvider>();

    return PopupMenuButton<String>(
      icon: const Icon(Icons.more_vert, size: 20),
      onSelected: (value) async {
        switch (value) {
          case 'edit':
            showDialog(
              context: context,
              builder: (context) => _AddServiceDialog(service: service),
            );
            break;
          case 'delete':
            final deleted = await showDialog<bool>(
              context: context,
              builder: (context) => AlertDialog(
                title: const Text('Supprimer le service'),
                content: Text(
                    'Êtes-vous sûr de vouloir supprimer ${service.name} ? Cette action est irréversible.'),
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
              await servicesProvider.deleteService(service.id);
              if (context.mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('Service supprimé avec succès'),
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

// Continue in next part due to length...

class _AddServiceDialog extends StatefulWidget {
  final ServiceModel? service;

  const _AddServiceDialog({this.service});

  @override
  State<_AddServiceDialog> createState() => _AddServiceDialogState();
}

class _AddServiceDialogState extends State<_AddServiceDialog> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _priceController = TextEditingController();
  String? _selectedCategory;
  String? _selectedDuration;
  List<String> _selectedRoles = [];
  String? _selectedImagePath;
  final ImagePicker _picker = ImagePicker();

  final List<String> _durationOptions = [
    '30 Minutes',
    '1 Hour',
    '1.5 Hours',
    '2 Hours',
  ];

  final List<String> _roleOptions = [
    'Esthéticienne Senior',
    'Esthéticienne',
    'Directeur Général',
    'Technicien Principal',
    'Gommeuse',
    'Experts Beauté',
  ];

  void _showRolesDialog() {
    showDialog(
      context: context,
      builder: (context) => StatefulBuilder(
        builder: (context, setDialogState) {
          return AlertDialog(
            title: const Text('Select Roles'),
            content: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: _roleOptions.map((role) {
                  return CheckboxListTile(
                    title: Text(role),
                    value: _selectedRoles.contains(role),
                    onChanged: (value) {
                      setDialogState(() {
                        if (value == true) {
                          _selectedRoles.add(role);
                        } else {
                          _selectedRoles.remove(role);
                        }
                      });
                    },
                  );
                }).toList(),
              ),
            ),
            actions: [
              TextButton(
                onPressed: () {
                  setState(() {});
                  Navigator.pop(context);
                },
                child: const Text('Done'),
              ),
            ],
          );
        },
      ),
    );
  }

  @override
  void initState() {
    super.initState();
    if (widget.service != null) {
      _nameController.text = widget.service!.name;
      _descriptionController.text = widget.service!.description;
      _priceController.text = widget.service!.price.toString();
      _selectedCategory = widget.service!.category;
      _selectedDuration = widget.service!.durationDisplay;
      _selectedRoles = widget.service!.roles ?? [];
      _selectedImagePath = widget.service!.imageUrl;
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _descriptionController.dispose();
    _priceController.dispose();
    super.dispose();
  }

  Future<void> _pickImage() async {
    final result = await showDialog<String>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Sélectionner une image'),
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
                hintText: 'assets/services/nom.jpg',
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
        _selectedImagePath = result;
      });
    }
  }

  int _parseDuration(String duration) {
    if (duration.contains('Minutes')) {
      return int.parse(duration.split(' ')[0]);
    } else if (duration.contains('Hour')) {
      final parts = duration.split(' ');
      if (parts.length == 2) {
        return int.parse(parts[0]) * 60;
      } else {
        return (int.parse(parts[0]) * 60) + 30;
      }
    }
    return 30;
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedCategory == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Veuillez sélectionner une catégorie'),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }
    if (_selectedDuration == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Veuillez sélectionner une durée'),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }

    final servicesProvider = context.read<ServicesProvider>();

    final service = ServiceModel(
      id: widget.service?.id ?? '',
      name: _nameController.text.trim(),
      description: _descriptionController.text.trim(),
      price: double.tryParse(_priceController.text) ?? 0,
      duration: _parseDuration(_selectedDuration!),
      category: _selectedCategory!,
      isActive: widget.service?.isActive ?? true,
      imageUrl: _selectedImagePath,
      roles: _selectedRoles.isEmpty ? null : _selectedRoles,
      createdAt: widget.service?.createdAt ?? DateTime.now(),
      updatedAt: DateTime.now(),
    );

    bool success;
    if (widget.service == null) {
      success = await servicesProvider.addService(service);
    } else {
      success = await servicesProvider.updateService(service);
    }

    if (success && mounted) {
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            widget.service == null
                ? 'Service ajouté avec succès'
                : 'Service modifié avec succès',
          ),
          backgroundColor: Colors.green,
        ),
      );
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(servicesProvider.error ?? 'Erreur'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final categoriesProvider = context.watch<CategoriesProvider>();
    final categories = categoriesProvider.activeCategories;

    return Dialog(
      child: Container(
        width: 800,
        padding: const EdgeInsets.all(24),
        child: Form(
          key: _formKey,
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      widget.service == null ? 'Add New Service' : 'Edit Service',
                      style: const TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close),
                      onPressed: () => Navigator.pop(context),
                    ),
                  ],
                ),
                const SizedBox(height: 24),
                // Two columns layout
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Left column
                    Expanded(
                      child: Column(
                        children: [
                          TextFormField(
                            controller: _nameController,
                            decoration: const InputDecoration(
                              labelText: 'Name',
                              hintText: 'Enter service name',
                              border: OutlineInputBorder(),
                            ),
                            validator: (value) {
                              if (value == null || value.isEmpty) {
                                return 'Veuillez entrer un nom';
                              }
                              return null;
                            },
                          ),
                          const SizedBox(height: 16),
                          DropdownButtonFormField<String>(
                            value: _selectedDuration,
                            decoration: const InputDecoration(
                              labelText: 'Duration',
                              hintText: 'Select Duration',
                              border: OutlineInputBorder(),
                            ),
                            items: _durationOptions.map((duration) {
                              return DropdownMenuItem(
                                value: duration,
                                child: Text(duration),
                              );
                            }).toList(),
                            onChanged: (value) {
                              setState(() {
                                _selectedDuration = value;
                              });
                            },
                            validator: (value) {
                              if (value == null) {
                                return 'Veuillez sélectionner une durée';
                              }
                              return null;
                            },
                          ),
                          const SizedBox(height: 16),
                          TextFormField(
                            controller: _descriptionController,
                            decoration: const InputDecoration(
                              labelText: 'Description',
                              hintText: 'Enter description',
                              border: OutlineInputBorder(),
                            ),
                            maxLines: 4,
                            validator: (value) {
                              if (value == null || value.isEmpty) {
                                return 'Veuillez entrer une description';
                              }
                              return null;
                            },
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 16),
                    // Right column
                    Expanded(
                      child: Column(
                        children: [
                          DropdownButtonFormField<String>(
                            value: _selectedCategory,
                            decoration: const InputDecoration(
                              labelText: 'Category',
                              hintText: 'Select Category',
                              border: OutlineInputBorder(),
                            ),
                            items: categories.map((category) {
                              return DropdownMenuItem(
                                value: category.name,
                                child: Text(category.name),
                              );
                            }).toList(),
                            onChanged: (value) {
                              setState(() {
                                _selectedCategory = value;
                              });
                            },
                            validator: (value) {
                              if (value == null) {
                                return 'Veuillez sélectionner une catégorie';
                              }
                              return null;
                            },
                          ),
                          const SizedBox(height: 16),
                          TextFormField(
                            controller: _priceController,
                            decoration: const InputDecoration(
                              labelText: 'Price',
                              hintText: 'Enter price in dollars',
                              prefixIcon: Icon(Icons.attach_money),
                              border: OutlineInputBorder(),
                            ),
                            keyboardType: TextInputType.number,
                            validator: (value) {
                              if (value == null || value.isEmpty) {
                                return 'Veuillez entrer un prix';
                              }
                              if (double.tryParse(value) == null) {
                                return 'Prix invalide';
                              }
                              return null;
                            },
                          ),
                          const SizedBox(height: 16),
                          InkWell(
                            onTap: _showRolesDialog,
                            child: InputDecorator(
                              decoration: const InputDecoration(
                                labelText: 'Roles',
                                hintText: 'Select Options',
                                border: OutlineInputBorder(),
                                suffixIcon: Icon(Icons.arrow_drop_down),
                              ),
                              child: Text(
                                _selectedRoles.isEmpty
                                    ? 'Select Options'
                                    : _selectedRoles.join(', '),
                                style: TextStyle(
                                  color: _selectedRoles.isEmpty
                                      ? Colors.grey[600]
                                      : Colors.black,
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(height: 16),
                          OutlinedButton.icon(
                            onPressed: _pickImage,
                            icon: const Icon(Icons.upload_file),
                            label: const Text('Upload image'),
                            style: OutlinedButton.styleFrom(
                              padding: const EdgeInsets.symmetric(vertical: 12),
                            ),
                          ),
                          if (_selectedImagePath != null)
                            Padding(
                              padding: const EdgeInsets.only(top: 8),
                              child: Text(
                                _selectedImagePath!,
                                style: const TextStyle(fontSize: 12),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 24),
                // Action buttons
                Row(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    TextButton(
                      onPressed: () => Navigator.pop(context),
                      style: TextButton.styleFrom(
                        foregroundColor: Colors.orange,
                      ),
                      child: const Text('Cancel'),
                    ),
                    const SizedBox(width: 12),
                    ElevatedButton(
                      onPressed: _save,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFFDDD1BC),
                        foregroundColor: Colors.black,
                      ),
                      child: const Text('Save'),
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

class _AddCategoryDialog extends StatefulWidget {
  const _AddCategoryDialog();

  @override
  State<_AddCategoryDialog> createState() => _AddCategoryDialogState();
}

class _AddCategoryDialogState extends State<_AddCategoryDialog> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  String? _selectedImagePath;
  final ImagePicker _picker = ImagePicker();

  @override
  void dispose() {
    _nameController.dispose();
    super.dispose();
  }

  Future<void> _pickImage() async {
    final result = await showDialog<String>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Sélectionner une image'),
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
                hintText: 'assets/services/category.jpg',
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
        _selectedImagePath = result;
      });
    }
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;

    final categoriesProvider = context.read<CategoriesProvider>();

    final category = CategoryModel(
      id: '',
      name: _nameController.text.trim(),
      imageUrl: _selectedImagePath,
      isActive: true,
      createdAt: DateTime.now(),
    );

    final success = await categoriesProvider.addCategory(category);

    if (success && mounted) {
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Catégorie ajoutée avec succès'),
          backgroundColor: Colors.green,
        ),
      );
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(categoriesProvider.error ?? 'Erreur'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      child: Container(
        width: 500,
        padding: const EdgeInsets.all(24),
        child: Form(
          key: _formKey,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Text(
                'Add New Category',
                style: TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 24),
              TextFormField(
                controller: _nameController,
                decoration: const InputDecoration(
                  labelText: 'Category Name',
                  border: OutlineInputBorder(),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Veuillez entrer un nom';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
              OutlinedButton.icon(
                onPressed: _pickImage,
                icon: const Icon(Icons.upload_file),
                label: const Text('Upload image'),
                style: OutlinedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 12),
                ),
              ),
              if (_selectedImagePath != null)
                Padding(
                  padding: const EdgeInsets.only(top: 8),
                  child: Text(
                    _selectedImagePath!,
                    style: const TextStyle(fontSize: 12),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
              const SizedBox(height: 24),
              Row(
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  TextButton(
                    onPressed: () => Navigator.pop(context),
                    child: const Text('Cancel'),
                  ),
                  const SizedBox(width: 12),
                  ElevatedButton(
                    onPressed: _save,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFFDDD1BC),
                      foregroundColor: Colors.black,
                    ),
                    child: const Text('Save'),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
