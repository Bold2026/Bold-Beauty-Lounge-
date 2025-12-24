import 'package:cloud_firestore/cloud_firestore.dart';

class FirestoreService {
  final FirebaseFirestore _db;

  FirestoreService({FirebaseFirestore? db})
      : _db = db ?? FirebaseFirestore.instance;

  CollectionReference<Map<String, dynamic>> collection(String path) =>
      _db.collection(path);

  Future<void> setDocument(
      {required String path,
      required String id,
      required Map<String, dynamic> data,
      bool merge = true}) {
    return _db.collection(path).doc(id).set(data, SetOptions(merge: merge));
  }

  Future<DocumentSnapshot<Map<String, dynamic>>> getDocument(
      {required String path, required String id}) {
    return _db.collection(path).doc(id).get();
  }
}
