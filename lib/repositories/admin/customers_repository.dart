import 'package:cloud_firestore/cloud_firestore.dart';
import '../../models/admin/customer_model.dart';

class CustomersRepository {
  final FirebaseFirestore _firestore;

  CustomersRepository({FirebaseFirestore? firestore})
      : _firestore = firestore ?? FirebaseFirestore.instance;

  static const String collectionPath = 'customers';

  // Get all customers
  Stream<List<CustomerModel>> getAllCustomers() {
    return _firestore
        .collection(collectionPath)
        .orderBy('createdAt', descending: true)
        .snapshots()
        .map((snapshot) => snapshot.docs
            .map((doc) => CustomerModel.fromFirestore(doc))
            .toList());
  }

  // Get customer by ID
  Future<CustomerModel?> getCustomerById(String customerId) async {
    final doc = await _firestore
        .collection(collectionPath)
        .doc(customerId)
        .get();
    if (!doc.exists) return null;
    return CustomerModel.fromFirestore(doc);
  }

  // Get customer by email
  Future<CustomerModel?> getCustomerByEmail(String email) async {
    final query = await _firestore
        .collection(collectionPath)
        .where('email', isEqualTo: email)
        .limit(1)
        .get();
    if (query.docs.isEmpty) return null;
    return CustomerModel.fromFirestore(query.docs.first);
  }

  // Add new customer
  Future<String> addCustomer(CustomerModel customer) async {
    final docRef = _firestore.collection(collectionPath).doc();
    await docRef.set(customer.copyWith(id: docRef.id).toFirestore());
    return docRef.id;
  }

  // Update customer
  Future<void> updateCustomer(CustomerModel customer) async {
    await _firestore
        .collection(collectionPath)
        .doc(customer.id)
        .update(customer.toFirestore());
  }

  // Delete customer
  Future<void> deleteCustomer(String customerId) async {
    await _firestore.collection(collectionPath).doc(customerId).delete();
  }

  // Update last visit date
  Future<void> updateLastVisit(String customerId, DateTime visitDate) async {
    await _firestore.collection(collectionPath).doc(customerId).update({
      'lastVisit': Timestamp.fromDate(visitDate),
      'updatedAt': FieldValue.serverTimestamp(),
    });
  }
}




