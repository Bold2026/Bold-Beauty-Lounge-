# 📍 Where to Find User Bookings in Admin Panel

## 🎯 **EXACT LOCATION: "Gestion des réservations" Screen**

All user bookings are displayed in the **"Gestion des réservations"** (Booking Management) screen.

---

## 📱 **How to Access:**

### Step 1: Open Admin Panel
1. Open the app
2. Go to **"Profil"** tab (person icon at bottom)
3. Click **"Panneau d'administration"** or **"Accès direct (Test)"**

### Step 2: Navigate to Bookings
From the **Admin Dashboard**, you have two options:

**Option A: Quick Action Button**
- Click the **"Gérer les réservations"** button (blue card with calendar icon)
- This opens the **BookingManagementScreen** with ALL user bookings

**Option B: From Dashboard Menu**
- In the dashboard, scroll down to **"Réservations récentes"** section
- Click **"Voir tout"** link at the top right
- This also opens the **BookingManagementScreen**

---

## 📊 **What You'll See in "Gestion des réservations":**

### **ALL User Bookings Are Displayed Here:**
- ✅ Complete list of ALL bookings from ALL users
- ✅ Real-time updates (automatically refreshes when new bookings are created)
- ✅ Search functionality (by service name, client notes)
- ✅ Filter by status:
  - **Toutes** (All)
  - **En attente** (Pending)
  - **Confirmées** (Confirmed)
  - **Terminées** (Completed)
  - **Annulées** (Cancelled)

### **Each Booking Shows:**
- Service name
- Date and time
- Price
- Status (with color coding)
- User ID
- Booking ID
- Notes (if any)

### **Actions Available:**
- **Confirm** a pending booking
- **Cancel** a booking
- **Mark as completed**
- **View full details** (expandable card)

---

## 🔍 **Technical Details:**

### **Firestore Query:**
The admin panel queries the `bookings` collection **WITHOUT filtering by userId**, which means it shows **ALL bookings from ALL users**:

```dart
Stream<QuerySnapshot> _getBookingsStream() {
  Query query = _firestore.collection('bookings');
  
  // Only filters by status (if selected)
  if (_selectedFilter != 'all') {
    query = query.where('status', isEqualTo: _selectedFilter);
  }
  
  // Orders by creation date (newest first)
  query = query.orderBy('createdAt', descending: true);
  
  return query.snapshots();
}
```

**✅ This means ALL user bookings are visible to admins!**

---

## 📂 **File Location:**

The booking management screen is located at:
```
lib/screens/admin/booking_management_screen.dart
```

---

## 🎨 **Visual Guide:**

```
App Navigation:
└── Profil Tab
    └── Panneau d'administration
        └── Admin Dashboard
            ├── [Button] "Gérer les réservations" ← CLICK HERE
            └── Section "Réservations récentes"
                └── [Link] "Voir tout" ← OR CLICK HERE
                    └── BookingManagementScreen
                        └── 📋 ALL USER BOOKINGS DISPLAYED HERE
```

---

## ✅ **Summary:**

**Question:** Where will I find those bookings that come from users?

**Answer:** 
1. **Admin Dashboard** → Click **"Gérer les réservations"** button
2. OR **Admin Dashboard** → Scroll to **"Réservations récentes"** → Click **"Voir tout"**

**Result:** You'll see the **"Gestion des réservations"** screen with **ALL bookings from ALL users** displayed in a scrollable list with search and filter capabilities.

---

## 🔐 **Important Notes:**

1. **Firestore Rules:** Make sure your Firestore rules allow admins to read all bookings (see `firestore.rules`)

2. **Real-time Updates:** The list updates automatically when users create new bookings

3. **No User Filter:** The admin panel does NOT filter by userId, so you see EVERYTHING

4. **Permissions:** Only users with `isAdmin: true` in Firestore can access this panel

---

## 🚀 **Quick Test:**

To verify bookings are showing:
1. Have a user create a booking through the app
2. Open Admin Panel → "Gérer les réservations"
3. The new booking should appear at the top of the list (newest first)

**That's where all user bookings are!** 🎉


