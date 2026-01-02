import '../models/admin/employee_model.dart';
import '../repositories/admin/employees_repository.dart';

/// Utility class to initialize default employees in Firestore
class EmployeesInitializer {
  static final List<Map<String, dynamic>> defaultEmployees = [
    {
      'firstName': 'Mme Laila Bazzi',
      'gender': 'Female',
      'role': 'Directeur Général',
      'phone': '0619249249',
      'email': 'laila.bazzi@boldbeauty.com',
      'photoUrl': 'assets/specialists/leila bazi.jpg',
      'status': 'active',
    },
    {
      'firstName': 'Nasira Mounir',
      'gender': 'Female',
      'role': 'Esthéticienne Senior',
      'phone': '',
      'email': 'nasira.mounir@boldbeauty.com',
      'photoUrl': 'assets/specialists/nasira mounir.jpg',
      'status': 'active',
    },
    {
      'firstName': 'Laarach Fadoua',
      'gender': 'Female',
      'role': 'Esthéticienne Senior',
      'phone': '',
      'email': 'laarach.fadoua@boldbeauty.com',
      'photoUrl': 'assets/specialists/laarach fadoua.jpg',
      'status': 'active',
    },
    {
      'firstName': 'Zineb Zineddine',
      'gender': 'Female',
      'role': 'Esthéticienne Gestion',
      'phone': '',
      'email': 'zineb.zineddine@boldbeauty.com',
      'photoUrl': 'assets/specialists/zineb zineddine.jpg',
      'status': 'active',
    },
    {
      'firstName': 'Bachir Bachir',
      'gender': 'Male',
      'role': 'Technicien Principal',
      'phone': '',
      'email': 'bachir.bachir@boldbeauty.com',
      'photoUrl': 'assets/specialists/bachir.jpeg',
      'status': 'active',
    },
    {
      'firstName': 'Raja Jouani',
      'gender': 'Female',
      'role': 'Gommeuse',
      'phone': '',
      'email': 'raja.jouani@boldbeauty.com',
      'photoUrl': 'assets/specialists/raja jouani.jpeg',
      'status': 'active',
    },
    {
      'firstName': 'Hiyar San`ae',
      'gender': 'Female',
      'role': 'Experts Beauté',
      'phone': '',
      'email': 'hiyar.sanae@boldbeauty.com',
      'photoUrl': 'assets/specialists/Hiyar Sanae.jpeg',
      'status': 'active',
    },
  ];

  /// Initialize default employees in Firestore
  /// This should be called once to populate the database
  static Future<void> initializeEmployees(EmployeesRepository repository) async {
    for (final employeeData in defaultEmployees) {
      // Check if employee already exists by email
      final existingEmployees = await repository.getAllEmployees().first;
      final exists = existingEmployees.any(
        (e) => e.email == employeeData['email'],
      );

      if (!exists) {
        final employee = EmployeeModel(
          id: '', // Will be generated
          firstName: employeeData['firstName'] as String,
          gender: employeeData['gender'] as String,
          role: employeeData['role'] as String,
          phone: employeeData['phone'] as String,
          email: employeeData['email'] as String,
          photoUrl: employeeData['photoUrl'] as String?,
          status: employeeData['status'] as String,
          joiningDate: DateTime.now(),
          createdAt: DateTime.now(),
        );

        await repository.addEmployee(employee);
      }
    }
  }
}






