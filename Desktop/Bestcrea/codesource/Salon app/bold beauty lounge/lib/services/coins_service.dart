import 'package:cloud_firestore/cloud_firestore.dart';
import '../models/coin_transaction_model.dart';
import '../models/contact_model.dart';

/// Service for managing Bold Coins wallet system
class CoinsService {
  final FirebaseFirestore _firestore;

  static const String boldSystemId = 'BOLD_SYSTEM';
  static const String usersCollection = 'users';
  static const String coinTransactionsCollection = 'coinTransactions';
  static const String contactsCollection = 'contacts';

  CoinsService({
    FirebaseFirestore? firestore,
  })  : _firestore = firestore ?? FirebaseFirestore.instance;

  /// Ensure wallet is initialized for a user
  Future<void> ensureWalletInitialized(String uid) async {
    final userRef = _firestore.collection(usersCollection).doc(uid);
    final userDoc = await userRef.get();

    if (!userDoc.exists) {
      // Initialize wallet with balance 0 and QR code
      await userRef.set({
        'boldCoinsBalance': 0,
        'qrCodeValue': 'BOLDCOINS:$uid',
        'createdAt': FieldValue.serverTimestamp(),
        'updatedAt': FieldValue.serverTimestamp(),
      }, SetOptions(merge: true));
    } else {
      // Ensure QR code exists
      final data = userDoc.data();
      if (data == null || data['qrCodeValue'] == null) {
        await userRef.update({
          'qrCodeValue': 'BOLDCOINS:$uid',
          'updatedAt': FieldValue.serverTimestamp(),
        });
      }
    }
  }

  /// Stream user's coin balance
  Stream<int> streamBalance(String uid) {
    return _firestore
        .collection(usersCollection)
        .doc(uid)
        .snapshots()
        .map((doc) {
      if (!doc.exists) return 0;
      final data = doc.data();
      return (data?['boldCoinsBalance'] ?? 0).toInt();
    });
  }

  /// Earn coins from a completed booking (idempotent)
  Future<void> earnFromBooking(
    String bookingId,
    String uid,
    double servicePrice,
  ) async {
    if (servicePrice <= 0) return;

    // Calculate coins to earn: floor(servicePrice * 0.10)
    final coinsToEarn = (servicePrice * 0.10).floor();

    if (coinsToEarn <= 0) return;

    // Check if transaction already exists for this booking (idempotency)
    final existingTx = await _firestore
        .collection(usersCollection)
        .doc(uid)
        .collection(coinTransactionsCollection)
        .where('bookingId', isEqualTo: bookingId)
        .where('type', isEqualTo: 'earn')
        .limit(1)
        .get();

    if (existingTx.docs.isNotEmpty) {
      // Already earned for this booking
      return;
    }

    // Use transaction to ensure atomicity
    await _firestore.runTransaction((transaction) async {
      final userRef = _firestore.collection(usersCollection).doc(uid);
      final userDoc = await transaction.get(userRef);

      if (!userDoc.exists) {
        // Initialize wallet if needed
        transaction.set(userRef, {
          'boldCoinsBalance': coinsToEarn,
          'qrCodeValue': 'BOLDCOINS:$uid',
          'createdAt': FieldValue.serverTimestamp(),
          'updatedAt': FieldValue.serverTimestamp(),
        }, SetOptions(merge: true));
      } else {
        final currentBalance = (userDoc.data()?['boldCoinsBalance'] ?? 0).toInt();
        transaction.update(userRef, {
          'boldCoinsBalance': currentBalance + coinsToEarn,
          'updatedAt': FieldValue.serverTimestamp(),
        });
      }

      // Create transaction record
      final txRef = userRef.collection(coinTransactionsCollection).doc();
      transaction.set(txRef, {
        'type': 'earn',
        'amount': coinsToEarn,
        'direction': 'in',
        'createdAt': FieldValue.serverTimestamp(),
        'bookingId': bookingId,
        'status': 'success',
        'reason': 'Earned from completed booking',
      });
    });
  }

  /// Redeem coins as discount (for booking payment)
  Future<void> redeemCoins(
    String uid,
    int amount,
    String reason, {
    String? bookingId,
  }) async {
    if (amount <= 0) {
      throw Exception('Amount must be positive');
    }

    await _firestore.runTransaction((transaction) async {
      final userRef = _firestore.collection(usersCollection).doc(uid);
      final userDoc = await transaction.get(userRef);

      if (!userDoc.exists) {
        throw Exception('User wallet not found');
      }

      final currentBalance = (userDoc.data()?['boldCoinsBalance'] ?? 0).toInt();
      if (currentBalance < amount) {
        throw Exception('Insufficient coins balance');
      }

      // Update balance
      transaction.update(userRef, {
        'boldCoinsBalance': currentBalance - amount,
        'updatedAt': FieldValue.serverTimestamp(),
      });

      // Create transaction record
      final txRef = userRef.collection(coinTransactionsCollection).doc();
      transaction.set(txRef, {
        'type': 'pay',
        'amount': amount,
        'direction': 'out',
        'createdAt': FieldValue.serverTimestamp(),
        if (bookingId != null) 'bookingId': bookingId,
        'status': 'success',
        'reason': reason,
      });
    });
  }

  /// Transfer coins between users or to BOLD_SYSTEM
  Future<void> transferCoins(
    String fromUid,
    String toUidOrBoldSystem,
    int amount,
  ) async {
    if (amount <= 0) {
      throw Exception('Amount must be positive');
    }

    if (fromUid == toUidOrBoldSystem) {
      throw Exception('Cannot transfer to yourself');
    }

    final isToSystem = toUidOrBoldSystem == boldSystemId;

    await _firestore.runTransaction((transaction) async {
      // Get sender's balance
      final fromUserRef = _firestore.collection(usersCollection).doc(fromUid);
      final fromUserDoc = await transaction.get(fromUserRef);

      if (!fromUserDoc.exists) {
        throw Exception('Sender wallet not found');
      }

      final fromBalance = (fromUserDoc.data()?['boldCoinsBalance'] ?? 0).toInt();
      if (fromBalance < amount) {
        throw Exception('Insufficient coins balance');
      }

      // Update sender balance
      transaction.update(fromUserRef, {
        'boldCoinsBalance': fromBalance - amount,
        'updatedAt': FieldValue.serverTimestamp(),
      });

      // Create sender transaction record (transfer_out)
      final fromTxRef = fromUserRef.collection(coinTransactionsCollection).doc();
      transaction.set(fromTxRef, {
        'type': 'transfer_out',
        'amount': amount,
        'direction': 'out',
        'createdAt': FieldValue.serverTimestamp(),
        'toUserId': toUidOrBoldSystem,
        'status': 'success',
        'reason': isToSystem
            ? 'Transferred to BOLD_SYSTEM'
            : 'Transferred to user',
      });

      if (!isToSystem) {
        // Update receiver balance (if not system)
        final toUserRef = _firestore.collection(usersCollection).doc(toUidOrBoldSystem);
        final toUserDoc = await transaction.get(toUserRef);

        if (!toUserDoc.exists) {
          // Initialize receiver wallet if needed
          transaction.set(toUserRef, {
            'boldCoinsBalance': amount,
            'qrCodeValue': 'BOLDCOINS:$toUidOrBoldSystem',
            'createdAt': FieldValue.serverTimestamp(),
            'updatedAt': FieldValue.serverTimestamp(),
          }, SetOptions(merge: true));
        } else {
          final toBalance = (toUserDoc.data()?['boldCoinsBalance'] ?? 0).toInt();
          transaction.update(toUserRef, {
            'boldCoinsBalance': toBalance + amount,
            'updatedAt': FieldValue.serverTimestamp(),
          });
        }

        // Create receiver transaction record (transfer_in)
        final toTxRef = toUserRef.collection(coinTransactionsCollection).doc();
        transaction.set(toTxRef, {
          'type': 'transfer_in',
          'amount': amount,
          'direction': 'in',
          'createdAt': FieldValue.serverTimestamp(),
          'fromUserId': fromUid,
          'status': 'success',
          'reason': 'Received from user',
        });
      } else {
        // For system transfers, update system document if it exists
        final systemRef = _firestore.collection('system').doc('bold');
        final systemDoc = await transaction.get(systemRef);

        if (systemDoc.exists) {
          final systemBalance =
              (systemDoc.data()?['boldWalletBalance'] ?? 0).toInt();
          transaction.update(systemRef, {
            'boldWalletBalance': systemBalance + amount,
            'updatedAt': FieldValue.serverTimestamp(),
          });
        } else {
          transaction.set(systemRef, {
            'systemId': boldSystemId,
            'boldWalletBalance': amount,
            'createdAt': FieldValue.serverTimestamp(),
            'updatedAt': FieldValue.serverTimestamp(),
          });
        }
      }
    });
  }

  /// Admin adjustment: credit or debit user balance
  Future<void> adminAdjustBalance(
    String uid,
    int amount,
    String reason,
    String adminId,
  ) async {
    if (amount == 0) {
      throw Exception('Amount cannot be zero');
    }

    final isCredit = amount > 0;
    final absoluteAmount = amount.abs();

    await _firestore.runTransaction((transaction) async {
      final userRef = _firestore.collection(usersCollection).doc(uid);
      final userDoc = await transaction.get(userRef);

      if (!userDoc.exists) {
        if (isCredit) {
          // Initialize wallet with credit
          transaction.set(userRef, {
            'boldCoinsBalance': absoluteAmount,
            'qrCodeValue': 'BOLDCOINS:$uid',
            'createdAt': FieldValue.serverTimestamp(),
            'updatedAt': FieldValue.serverTimestamp(),
          }, SetOptions(merge: true));
        } else {
          throw Exception('Cannot debit from non-existent wallet');
        }
      } else {
        final currentBalance = (userDoc.data()?['boldCoinsBalance'] ?? 0).toInt();
        final newBalance = isCredit
            ? currentBalance + absoluteAmount
            : currentBalance - absoluteAmount;

        if (newBalance < 0) {
          throw Exception('Balance cannot be negative');
        }

        transaction.update(userRef, {
          'boldCoinsBalance': newBalance,
          'updatedAt': FieldValue.serverTimestamp(),
        });
      }

      // Create transaction record
      final txRef = userRef.collection(coinTransactionsCollection).doc();
      transaction.set(txRef, {
        'type': 'admin_adjustment',
        'amount': absoluteAmount,
        'direction': isCredit ? 'in' : 'out',
        'createdAt': FieldValue.serverTimestamp(),
        'adminId': adminId,
        'status': 'success',
        'reason': reason,
        'metadata': {
          'adjustmentType': isCredit ? 'credit' : 'debit',
        },
      });
    });
  }

  /// Add a contact to user's contact list
  Future<void> addContact(
    String ownerUid,
    String contactUid,
    String displayName, {
    String? email,
  }) async {
    if (ownerUid == contactUid) {
      throw Exception('Cannot add yourself as contact');
    }

    // Verify contact user exists
    final contactUserDoc = await _firestore
        .collection(usersCollection)
        .doc(contactUid)
        .get();

    if (!contactUserDoc.exists) {
      throw Exception('Contact user not found');
    }

    // Add or update contact
    await _firestore
        .collection(usersCollection)
        .doc(ownerUid)
        .collection(contactsCollection)
        .doc(contactUid)
        .set({
      'contactUid': contactUid,
      'displayName': displayName,
      if (email != null) 'email': email,
      'addedAt': FieldValue.serverTimestamp(),
    }, SetOptions(merge: true));
  }

  /// Get user's contacts
  Stream<List<Contact>> getContacts(String uid) {
    return _firestore
        .collection(usersCollection)
        .doc(uid)
        .collection(contactsCollection)
        .orderBy('addedAt', descending: true)
        .snapshots()
        .map((snapshot) => snapshot.docs
            .map((doc) => Contact.fromFirestore(doc))
            .toList());
  }

  /// Get transaction history with filters and pagination
  Stream<List<CoinTransaction>> getHistory(
    String uid, {
    String? type,
    String? direction,
    DateTime? startDate,
    DateTime? endDate,
    int? limit,
  }) {
    Query query = _firestore
        .collection(usersCollection)
        .doc(uid)
        .collection(coinTransactionsCollection);

    // Apply filters
    if (type != null) {
      query = query.where('type', isEqualTo: type);
    }
    if (direction != null) {
      query = query.where('direction', isEqualTo: direction);
    }
    if (startDate != null) {
      query = query.where('createdAt', isGreaterThanOrEqualTo: Timestamp.fromDate(startDate));
    }
    if (endDate != null) {
      query = query.where('createdAt', isLessThanOrEqualTo: Timestamp.fromDate(endDate));
    }

    // Order and limit
    query = query.orderBy('createdAt', descending: true);
    if (limit != null && limit > 0) {
      query = query.limit(limit);
    }

    return query.snapshots().map((snapshot) => snapshot.docs
        .map((doc) => CoinTransaction.fromFirestore(
            doc as DocumentSnapshot<Map<String, dynamic>>))
        .toList());
  }

  /// Get user's QR code value
  Future<String> getQrCodeValue(String uid) async {
    final userDoc = await _firestore.collection(usersCollection).doc(uid).get();
    if (!userDoc.exists) {
      await ensureWalletInitialized(uid);
      return 'BOLDCOINS:$uid';
    }
    final data = userDoc.data();
    return data?['qrCodeValue'] ?? 'BOLDCOINS:$uid';
  }

  /// Parse QR code and extract user ID
  static String? parseQrCode(String qrValue) {
    if (qrValue.startsWith('BOLDCOINS:')) {
      return qrValue.substring('BOLDCOINS:'.length);
    }
    return null;
  }
}

