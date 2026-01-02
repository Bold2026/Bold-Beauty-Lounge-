# Bold Coins Implementation Summary

## Overview
Complete loyalty wallet system called "Bold Coins" has been implemented with 4 main modules: Pay, Earn, Transfer, and History, plus QR Code functionality and admin panel integration.

---

## Files Created

### Models
1. **`lib/models/coin_transaction_model.dart`**
   - `CoinTransaction` model for transaction records
   - Supports types: earn, pay, transfer_in, transfer_out, admin_adjustment
   - Includes direction (in/out), amounts, timestamps, and metadata

2. **`lib/models/contact_model.dart`**
   - `Contact` model for user contacts
   - Stores contactUid, displayName, email, addedAt

### Services
3. **`lib/services/coins_service.dart`**
   - Core service for all coin operations
   - Methods:
     - `ensureWalletInitialized(uid)` - Initialize wallet with balance 0
     - `streamBalance(uid)` - Stream user's balance
     - `earnFromBooking(bookingId, uid, servicePrice)` - Earn coins (idempotent)
     - `redeemCoins(uid, amount, reason, bookingId?)` - Redeem as discount
     - `transferCoins(fromUid, toUidOrBoldSystem, amount)` - Transfer between users
     - `adminAdjustBalance(uid, amount, reason, adminId)` - Admin adjustments
     - `addContact(ownerUid, contactUid, displayName, email?)` - Add contact
     - `getContacts(uid)` - Stream user's contacts
     - `getHistory(uid, filters, pagination)` - Transaction history with filters
     - `getQrCodeValue(uid)` - Get user's QR code
     - `parseQrCode(qrValue)` - Parse QR code to extract UID

4. **`lib/services/booking_coins_listener.dart`**
   - Listens for booking status changes
   - Automatically earns coins when booking status becomes "completed"
   - Idempotent: checks for existing transaction before earning

### UI Screens - Client App
5. **`lib/screens/coins/bold_coins_screen.dart`**
   - Main screen with 4 tabs (Pay, Earn, Transfer, History)
   - Shows current balance card
   - Tab navigation with icons

6. **`lib/screens/coins/coins_pay_tab.dart`**
   - Pay tab: Redeem coins as discount
   - Input field for amount
   - Validation and error handling

7. **`lib/screens/coins/coins_earn_tab.dart`**
   - Earn tab: Information about earning coins
   - Explains earning formula (10% of service price)
   - Step-by-step guide

8. **`lib/screens/coins/coins_transfer_tab.dart`**
   - Transfer tab: Send coins to other users or BOLD_SYSTEM
   - QR code scanner integration
   - Contacts list
   - Amount input and transfer confirmation

9. **`lib/screens/coins/coins_history_tab.dart`**
   - History tab: Transaction history with filters
   - Filter by type, direction, date range
   - Pagination support
   - Transaction cards with icons and colors

10. **`lib/screens/coins/coins_qr_screen.dart`**
    - Display user's QR code
    - Share functionality (placeholder)
    - Copy QR value

11. **`lib/screens/coins/coins_scan_screen.dart`**
    - Scan QR code to add contact
    - Manual QR code entry
    - Validates QR format and adds contact

### UI Screens - Booking Integration
12. **`lib/screens/booking/payment_screen_with_coins.dart`**
    - Payment screen with coin redemption
    - Shows available balance
    - Input for coins to redeem
    - Calculates final price after discount
    - Integrates with booking confirmation

### UI Screens - Admin Panel
13. **`lib/screens/admin_web/admin_user_coins_screen.dart`**
    - Admin screen to view/manage user's Bold Coins
    - Shows user balance
    - Admin adjustment form (credit/debit)
    - Contacts list
    - Transaction history with filters

---

## Files Modified

### Models
1. **`lib/models/booking_data.dart`**
   - Added `coinsRedeemed` field (int, default 0)
   - Added `finalPrice` getter (price after coin discount)
   - Formula: `finalPrice = servicePrice - coinsRedeemed` (min 0)

### Dependencies
2. **`pubspec.yaml`**
   - Added `qr_flutter: ^4.1.0` for QR code generation

### Security & Indexes
3. **`firestore.rules`**
   - Added rules for `users/{uid}` collection
   - Added rules for `users/{uid}/coinTransactions` subcollection
   - Added rules for `users/{uid}/contacts` subcollection
   - Added rules for `system/bold` document
   - **Key Security**: Users cannot directly modify `boldCoinsBalance` - only through transactions

4. **`firestore.indexes.json`**
   - Created composite indexes for transaction history queries:
     - `type + createdAt DESC`
     - `direction + createdAt DESC`
     - `type + direction + createdAt DESC`
     - `createdAt DESC` (for general history)

---

## Data Model Structure

### Firestore Collections

#### `users/{uid}`
```json
{
  "boldCoinsBalance": 0,
  "qrCodeValue": "BOLDCOINS:{uid}",
  "createdAt": Timestamp,
  "updatedAt": Timestamp
}
```

#### `users/{uid}/coinTransactions/{txId}`
```json
{
  "type": "earn" | "pay" | "transfer_in" | "transfer_out" | "admin_adjustment",
  "amount": 10,
  "direction": "in" | "out",
  "createdAt": Timestamp,
  "bookingId": "optional",
  "fromUserId": "optional",
  "toUserId": "optional",
  "adminId": "optional",
  "reason": "optional",
  "status": "success",
  "metadata": {}
}
```

#### `users/{uid}/contacts/{contactUid}`
```json
{
  "contactUid": "uid",
  "displayName": "Name",
  "email": "optional",
  "addedAt": Timestamp
}
```

#### `system/bold`
```json
{
  "systemId": "BOLD_SYSTEM",
  "boldWalletBalance": 0,
  "createdAt": Timestamp,
  "updatedAt": Timestamp
}
```

---

## Business Rules Implementation

### A) Wallet
✅ Each user has `boldCoinsBalance` (integer >= 0)
✅ Wallet initialized automatically with balance = 0
✅ QR code value: `BOLDCOINS:{uid}`

### B) Earn (Gagner)
✅ Coins earned = `floor(servicePrice * 0.10)`
✅ Idempotent: checks for existing transaction by `bookingId`
✅ Triggered when booking status becomes "completed"
✅ Stored in transaction record with `bookingId`

### C) Pay (Payer)
✅ User can redeem coins as discount before booking confirmation
✅ Validation: coins redeemed <= current balance
✅ Creates "pay" transaction in history
✅ Applied to booking final price (1 coin = 1 MAD)

### D) Transfer (Transfert)
✅ Atomic operations using Firestore transactions
✅ Transfer to another user or BOLD_SYSTEM
✅ Creates history entries:
   - Sender: `transfer_out`
   - Receiver: `transfer_in` (or system record)

### E) History (Histoire)
✅ Shows all transactions ordered by newest first
✅ Filtering support:
   - Type (earn/pay/transfer/admin_adjustment)
   - Direction (in/out)
   - Date range
✅ Pagination support

### F) QR Code + Contacts
✅ QR value format: `BOLDCOINS:{uid}`
✅ Profile screen shows user's QR
✅ Scan QR flow validates and adds contact
✅ Contacts stored per user

### G) Admin Panel
✅ Admin can view any user's:
   - Wallet balance
   - Full coin history with filters
   - Contacts list
✅ Admin can adjust balance (credit/debit) with reason
✅ Admin adjustment creates transaction record (type: `admin_adjustment`)

---

## Integration Points

### Booking Flow Integration
1. **Payment Screen**: User can redeem coins before confirmation
2. **Confirmation Screen**: Applies coin discount to final price
3. **Booking Status Listener**: Automatically earns coins when status = "completed"

### Admin Panel Integration
- Admin can access user coins from booking details (TODO: Add link in AdminBookingsScreen)

---

## Security Rules Summary

### User Access
- ✅ Users can read/write their own `users/{uid}` document
- ✅ Users CANNOT directly modify `boldCoinsBalance` (enforced by rules)
- ✅ Users can read/write their own transactions and contacts
- ✅ Users cannot create transactions directly (must use service layer)

### Admin Access
- ✅ Admins can read all users and their data
- ✅ Admins can perform balance adjustments (via transactions)
- ✅ Admins can read system documents

---

## Next Steps (TODO)

1. **Route Registration**
   - Add routes for Bold Coins screens:
     - `/coins` → `BoldCoinsScreen`
     - `/coins/qr` → `CoinsQrScreen`
     - `/coins/scan` → `CoinsScanScreen`

2. **Booking Screen Update**
   - Update `booking_screen.dart` to navigate to `PaymentScreenWithCoins` instead of `PaymentScreen`

3. **Main App Integration**
   - Add Bold Coins entry point (e.g., in profile screen or bottom navigation)

4. **Admin Panel Integration**
   - Add link to `AdminUserCoinsScreen` from booking details in `AdminBookingsScreen`

5. **Booking Coins Listener**
   - Initialize listener in main app (e.g., in `main_original.dart` or user provider)

6. **QR Code Scanner**
   - Integrate actual QR scanner (e.g., `qr_code_scanner` package) in `CoinsScanScreen`

7. **Copy to Clipboard**
   - Implement clipboard copy in `CoinsQrScreen`

8. **Share Functionality**
   - Implement share in `CoinsQrScreen`

---

## Testing Checklist

See `TEST_CHECKLIST.md` for detailed step-by-step testing instructions.

---

## Notes

- All code and comments are in ENGLISH
- All operations use Firestore transactions for atomicity
- Balance changes are only possible through transaction records
- Idempotency is enforced for earning coins from bookings
- QR codes contain only UID (no sensitive data)

---

**Status**: ✅ Core implementation complete. Integration and testing pending.
