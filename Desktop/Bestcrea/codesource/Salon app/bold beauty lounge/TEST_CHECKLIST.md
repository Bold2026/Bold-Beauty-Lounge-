# Bold Coins System - Test Checklist

## Prerequisites
- ✅ Firebase Authentication enabled (Email/Password)
- ✅ Firestore database initialized
- ✅ Admin user created in `admins/{uid}` with `isActive: true`
- ✅ Firestore rules deployed
- ✅ Firestore indexes deployed

---

## Test 1: Wallet Initialization

### Steps:
1. Sign in as a regular user
2. Navigate to Bold Coins screen
3. Check that balance shows 0
4. Check that QR code value is `BOLDCOINS:{uid}`

### Expected Results:
- ✅ Wallet initialized automatically
- ✅ Balance = 0
- ✅ QR code value correct

---

## Test 2: Earn Coins from Booking

### Steps:
1. Create a booking with service price = 200 MAD
2. Admin changes booking status to "completed"
3. Check user's Bold Coins balance
4. Check transaction history

### Expected Results:
- ✅ User earns 20 coins (floor(200 * 0.10) = 20)
- ✅ Balance updated to 20
- ✅ Transaction record created:
   - Type: "earn"
   - Direction: "in"
   - Amount: 20
   - BookingId: matches booking ID
- ✅ Idempotency: Changing status to "completed" again does NOT create duplicate transaction

---

## Test 3: Redeem Coins (Pay Tab)

### Steps:
1. User has balance = 50 coins
2. Navigate to Pay tab
3. Enter 30 coins to redeem
4. Confirm redemption
5. Check balance and history

### Expected Results:
- ✅ Balance decreases to 20 coins
- ✅ Transaction record created:
   - Type: "pay"
   - Direction: "out"
   - Amount: 30
- ✅ Error if trying to redeem more than balance

---

## Test 4: Coin Redemption in Booking Flow

### Steps:
1. User has balance = 100 coins
2. Start booking flow
3. Select service (price = 200 MAD)
4. On payment screen, redeem 50 coins
5. Complete booking
6. Check final price and balance

### Expected Results:
- ✅ Payment screen shows available balance
- ✅ User can enter coins to redeem
- ✅ Final price = 200 - 50 = 150 MAD
- ✅ Coins redeemed before booking confirmation
- ✅ Balance decreases by 50
- ✅ Transaction record created with bookingId

---

## Test 5: Transfer Coins to Another User

### Steps:
1. User A has 100 coins
2. User B has 0 coins
3. User A scans User B's QR code (or adds as contact)
4. User A transfers 30 coins to User B
5. Check both balances and histories

### Expected Results:
- ✅ User A balance = 70 coins
- ✅ User B balance = 30 coins
- ✅ User A has transaction:
   - Type: "transfer_out"
   - Direction: "out"
   - Amount: 30
   - toUserId: User B's UID
- ✅ User B has transaction:
   - Type: "transfer_in"
   - Direction: "in"
   - Amount: 30
   - fromUserId: User A's UID
- ✅ Error if User A tries to transfer more than balance

---

## Test 6: Transfer to BOLD_SYSTEM

### Steps:
1. User has 50 coins
2. Transfer 20 coins to BOLD_SYSTEM
3. Check user balance and system document

### Expected Results:
- ✅ User balance = 30 coins
- ✅ User has transaction: "transfer_out" to BOLD_SYSTEM
- ✅ System document `system/bold` updated with balance +20
- ✅ No "transfer_in" transaction for system (system doesn't have transactions subcollection)

---

## Test 7: QR Code and Contacts

### Steps:
1. User A views their QR code
2. User B scans User A's QR code
3. User B adds User A as contact
4. Check User B's contacts list
5. User B transfers coins to User A from contacts

### Expected Results:
- ✅ QR code displays correctly
- ✅ QR value format: `BOLDCOINS:{uid}`
- ✅ Scan validates QR format
- ✅ Contact added to User B's contacts
- ✅ Contact appears in transfer tab
- ✅ Transfer works from contacts list

---

## Test 8: Transaction History Filters

### Steps:
1. User has multiple transactions (earn, pay, transfer)
2. Navigate to History tab
3. Filter by type: "earn"
4. Filter by direction: "in"
5. Filter by date range
6. Clear filters

### Expected Results:
- ✅ All transactions visible by default
- ✅ Type filter shows only matching transactions
- ✅ Direction filter shows only matching transactions
- ✅ Date range filter works correctly
- ✅ Clear filters resets to all transactions
- ✅ Transactions ordered by newest first

---

## Test 9: Admin Panel - View User Coins

### Steps:
1. Admin signs in
2. Navigate to a booking
3. Click on user to view coins (TODO: Add link)
4. Or navigate directly to AdminUserCoinsScreen with userId

### Expected Results:
- ✅ Admin sees user's balance
- ✅ Admin sees transaction history
- ✅ Admin sees user's contacts
- ✅ All data loads correctly

---

## Test 10: Admin Panel - Adjust Balance

### Steps:
1. Admin views user with balance = 50
2. Credit 20 coins with reason "Promotion"
3. Check user balance and history
4. Debit 10 coins with reason "Refund adjustment"
5. Check user balance and history

### Expected Results:
- ✅ After credit: balance = 70
- ✅ Transaction created:
   - Type: "admin_adjustment"
   - Direction: "in"
   - Amount: 20
   - AdminId: admin's UID
   - Reason: "Promotion"
- ✅ After debit: balance = 60
- ✅ Transaction created:
   - Type: "admin_adjustment"
   - Direction: "out"
   - Amount: 10
   - Reason: "Refund adjustment"
- ✅ Error if debit would make balance negative

---

## Test 11: Edge Cases

### Test 11.1: Insufficient Balance
- Try to redeem more coins than balance
- Try to transfer more coins than balance
- **Expected**: Error message, no transaction created

### Test 11.2: Zero Amount
- Try to redeem 0 coins
- Try to transfer 0 coins
- **Expected**: Error message

### Test 11.3: Negative Amount
- Try to enter negative amount
- **Expected**: Validation error

### Test 11.4: Invalid QR Code
- Scan invalid QR code (not BOLDCOINS: format)
- **Expected**: Error message

### Test 11.5: Self Transfer
- Try to transfer coins to yourself
- **Expected**: Error message

### Test 11.6: Duplicate Earn
- Complete booking twice (status → completed → completed)
- **Expected**: Coins earned only once (idempotent)

---

## Test 12: Security Rules

### Test 12.1: User Cannot Modify Balance Directly
- Try to update `users/{uid}` with different `boldCoinsBalance`
- **Expected**: Firestore rules prevent direct modification

### Test 12.2: User Can Only Access Own Data
- User A tries to read User B's transactions
- **Expected**: Access denied (rules enforce ownership)

### Test 12.3: Admin Can Access All
- Admin reads any user's balance/history
- **Expected**: Access granted

---

## Test 13: Integration with Booking Status

### Steps:
1. Create booking with price = 150 MAD
2. User has 0 coins
3. Admin changes status: pending → confirmed → completed
4. Check user's coins after each status change

### Expected Results:
- ✅ Coins earned ONLY when status = "completed"
- ✅ Coins earned = floor(150 * 0.10) = 15
- ✅ No coins earned for pending/confirmed status

---

## Test 14: Full Booking Flow with Coins

### Steps:
1. User has 80 coins
2. Book service (price = 200 MAD)
3. Redeem 50 coins on payment screen
4. Complete booking
5. Admin marks booking as "completed"
6. Check final state

### Expected Results:
- ✅ Initial balance: 80 coins
- ✅ After redemption: 30 coins
- ✅ Final price paid: 200 - 50 = 150 MAD
- ✅ After booking completed: 30 + 20 = 50 coins (earned 20 from 200 MAD service)
- ✅ Two transactions:
   - Pay: 50 coins (out)
   - Earn: 20 coins (in)

---

## Test 15: Pagination

### Steps:
1. User has 50+ transactions
2. Navigate to History tab
3. Check that only first page loads (limit = 20)
4. Scroll to load more (if implemented)

### Expected Results:
- ✅ Initial load shows 20 transactions
- ✅ Pagination works correctly (if implemented)

---

## Performance Tests

### Test 16: Concurrent Operations
- Multiple users transfer coins simultaneously
- **Expected**: All transactions succeed, balances correct

### Test 17: Large History
- User with 1000+ transactions
- **Expected**: History loads efficiently with pagination

---

## Deployment Checklist

Before deploying to production:

- [ ] Deploy Firestore rules: `firebase deploy --only firestore:rules`
- [ ] Deploy Firestore indexes: `firebase deploy --only firestore:indexes`
- [ ] Verify indexes are built (check Firebase Console)
- [ ] Test all security rules
- [ ] Test admin access
- [ ] Test user access restrictions
- [ ] Verify QR code generation works
- [ ] Test booking integration end-to-end
- [ ] Test admin panel integration

---

## Known Limitations / TODOs

1. **QR Scanner**: Currently manual entry only. Need to integrate actual QR scanner package.
2. **Share Functionality**: Placeholder in QR screen, needs implementation.
3. **Copy to Clipboard**: Placeholder in QR screen, needs implementation.
4. **Admin Link**: Need to add link from booking details to user coins screen.
5. **Booking Listener**: Need to initialize in main app.
6. **Route Registration**: Need to register new routes in app router.

---

## Success Criteria

✅ All tests pass
✅ No security rule violations
✅ All transactions are atomic
✅ Balance calculations are correct
✅ Idempotency works for earning
✅ Admin adjustments work correctly
✅ QR codes and contacts work
✅ History filtering works
✅ Integration with booking flow works

---

**Last Updated**: Implementation Date
**Status**: Ready for Testing

