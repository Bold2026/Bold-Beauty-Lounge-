import 'package:cloud_firestore/cloud_firestore.dart';
import '../../models/admin/employee_model.dart';

class EmployeesRepository {
  final FirebaseFirestore _firestore;

  EmployeesRepository({FirebaseFirestore? firestore})
      : _firestore = firestore ?? FirebaseFirestore.instance;

  static const String collectionPath = 'employees';

  // Get all employees
  Stream<List<EmployeeModel>> getAllEmployees() {
    return _firestore
        .collection(collectionPath)
        .orderBy('createdAt', descending: true)
        .snapshots()
        .map((snapshot) => snapshot.docs
            .map((doc) => EmployeeModel.fromFirestore(doc))
            .toList());
  }

  // Get active employees only
  Stream<List<EmployeeModel>> getActiveEmployees() {
    return _firestore
        .collection(collectionPath)
        .where('status', isEqualTo: 'active')
        .orderBy('firstName')
        .snapshots()
        .map((snapshot) => snapshot.docs
            .map((doc) => EmployeeModel.fromFirestore(doc))
            .toList());
  }

  // Get employee by ID
  Future<EmployeeModel?> getEmployeeById(String employeeId) async {
    final doc = await _firestore
        .collection(collectionPath)
        .doc(employeeId)
        .get();
    if (!doc.exists) return null;
    return EmployeeModel.fromFirestore(doc);
  }

  // Add new employee
  Future<String> addEmployee(EmployeeModel employee) async {
    final docRef = _firestore.collection(collectionPath).doc();
    await docRef.set(employee.copyWith(id: docRef.id).toFirestore());
    return docRef.id;
  }

  // Update employee
  Future<void> updateEmployee(EmployeeModel employee) async {
    await _firestore
        .collection(collectionPath)
        .doc(employee.id)
        .update(employee.toFirestore());
  }

  // Delete employee
  Future<void> deleteEmployee(String employeeId) async {
    await _firestore.collection(collectionPath).doc(employeeId).delete();
  }

  // Toggle employee status
  Future<void> toggleEmployeeStatus(String employeeId, String status) async {
    await _firestore.collection(collectionPath).doc(employeeId).update({
      'status': status,
      'updatedAt': FieldValue.serverTimestamp(),
    });
  }
}






