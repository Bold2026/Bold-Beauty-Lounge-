# Bold Coins Integration Guide

## Quick Start

### 1. Deploy Firestore Rules and Indexes

```bash
# Deploy rules
firebase deploy --only firestore:rules

# Deploy indexes
firebase deploy --only firestore:indexes
```

### 2. Add Routes to App

Add these routes to your app router (e.g., `lib/main_original.dart` or router file):

```dart
routes: {
  '/coins': (context) => const BoldCoinsScreen(),
  '/coins/qr': (context) => const CoinsQrScreen(),
  '/coins/scan': (context) => const CoinsScanScreen(),
  // ... existing routes
}
```

### 3. Update Booking Flow

In `lib/screens/booking/booking_screen.dart`, update the navigation to payment:

```dart
// Replace PaymentScreen with PaymentScreenWithCoins
Navigator.push(
  context,
  MaterialPageRoute(
    builder: (context) => PaymentScreenWithCoins(bookingData: bookingData),
  ),
);
```

### 4. Initialize Booking Coins Listener

In your main app file (e.g., `lib/main_original.dart`), initialize the listener:

```dart
import 'package:bold_beauty_lounge/services/booking_coins_listener.dart';

// In main() or MyApp initState:
final listener = BookingCoinsListener();
listener.startListening();
```

### 5. Add Navigation to Bold Coins

Add entry point to Bold Coins screen (e.g., in profile screen or bottom navigation):

```dart
ListTile(
  leading: const Icon(Icons.coins),
  title: const Text('Bold Coins'),
  onTap: () {
    Navigator.pushNamed(context, '/coins');
  },
)
```

### 6. Add Admin Link to User Coins

In `lib/screens/admin_web/admin_bookings_screen.dart`, add link to view user coins:

```dart
// In booking card, add button:
IconButton(
  icon: const Icon(Icons.account_balance_wallet),
  onPressed: () {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => AdminUserCoinsScreen(
          userId: booking.userId,
          userName: booking.userName,
          userEmail: booking.userEmail,
        ),
      ),
    );
  },
  tooltip: 'View Coins',
)
```

---

## Testing

See `TEST_CHECKLIST.md` for comprehensive testing steps.

---

## Important Notes

1. **Balance Security**: Users CANNOT directly modify `boldCoinsBalance`. All changes must go through transactions.

2. **Idempotency**: Earning coins from bookings is idempotent - each booking can only earn once.

3. **Transactions**: All operations use Firestore transactions for atomicity.

4. **QR Codes**: Format is `BOLDCOINS:{uid}` - no sensitive data.

5. **Indexes**: Make sure to deploy indexes before using history filters.

---

## Troubleshooting

### Issue: "Missing index" error
**Solution**: Deploy `firestore.indexes.json` using `firebase deploy --only firestore:indexes`

### Issue: "Permission denied" error
**Solution**: Check Firestore rules are deployed and user is authenticated

### Issue: Coins not earning after booking completed
**Solution**: Ensure `BookingCoinsListener` is initialized and running

---

**Status**: ✅ Implementation Complete - Ready for Integration

