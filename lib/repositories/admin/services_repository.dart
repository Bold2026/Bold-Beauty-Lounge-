import 'package:cloud_firestore/cloud_firestore.dart';
import '../../models/admin/service_model.dart';

class ServicesRepository {
  final FirebaseFirestore _firestore;

  ServicesRepository({FirebaseFirestore? firestore})
      : _firestore = firestore ?? FirebaseFirestore.instance;

  static const String collectionPath = 'services';

  // Get all services
  Stream<List<ServiceModel>> getAllServices() {
    return _firestore
        .collection(collectionPath)
        .orderBy('createdAt', descending: true)
        .snapshots()
        .map((snapshot) => snapshot.docs
            .map((doc) => ServiceModel.fromFirestore(doc))
            .toList());
  }

  // Get active services only
  Stream<List<ServiceModel>> getActiveServices() {
    return _firestore
        .collection(collectionPath)
        .where('isActive', isEqualTo: true)
        .orderBy('name')
        .snapshots()
        .map((snapshot) => snapshot.docs
            .map((doc) => ServiceModel.fromFirestore(doc))
            .toList());
  }

  // Get service by ID
  Future<ServiceModel?> getServiceById(String serviceId) async {
    final doc = await _firestore.collection(collectionPath).doc(serviceId).get();
    if (!doc.exists) return null;
    return ServiceModel.fromFirestore(doc);
  }

  // Add new service
  Future<String> addService(ServiceModel service) async {
    final docRef = _firestore.collection(collectionPath).doc();
    await docRef.set(service.copyWith(id: docRef.id).toFirestore());
    return docRef.id;
  }

  // Update service
  Future<void> updateService(ServiceModel service) async {
    await _firestore
        .collection(collectionPath)
        .doc(service.id)
        .update(service.toFirestore());
  }

  // Delete service
  Future<void> deleteService(String serviceId) async {
    await _firestore.collection(collectionPath).doc(serviceId).delete();
  }

  // Toggle service active status
  Future<void> toggleServiceStatus(String serviceId, bool isActive) async {
    await _firestore.collection(collectionPath).doc(serviceId).update({
      'isActive': isActive,
      'updatedAt': FieldValue.serverTimestamp(),
    });
  }
}








