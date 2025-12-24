# 📊 Guide du Panneau d'Administration - Bold Beauty Lounge

## 🎯 Accès au Panneau d'Administration

### Pour les Administrateurs

Les réservations des utilisateurs sont accessibles via le **Panneau d'Administration** professionnel avec un tableau de bord sophistiqué.

### 📍 Comment y accéder ?

#### Option 1 : Depuis l'écran de profil
1. Ouvrez l'application
2. Allez dans l'onglet **"Profil"** (icône personne en bas)
3. Si vous êtes déjà administrateur, vous verrez **"Panneau d'administration"**
4. Si vous n'êtes pas encore admin, cliquez sur **"Accès administrateur"**
5. Entrez le mot de passe administrateur : `BoldAdmin2024` (à changer en production)

#### Option 2 : Accès direct
```dart
Navigator.push(
  context,
  MaterialPageRoute(
    builder: (context) => const AdminDashboardScreen(),
  ),
);
```

---

## 🎨 Fonctionnalités du Panneau d'Administration

### 1. 📊 Tableau de Bord Principal (`AdminDashboardScreen`)

**Localisation :** `lib/screens/admin/admin_dashboard_screen.dart`

**Fonctionnalités :**
- ✅ Vue d'ensemble des statistiques en temps réel
- ✅ Cartes de statistiques :
  - Total des réservations
  - Réservations en attente
  - Réservations confirmées
  - Réservations du jour
- ✅ Revenus totaux
- ✅ Réservations récentes (5 dernières)
- ✅ Actions rapides vers la gestion et les statistiques

**Statistiques affichées :**
- Nombre total de réservations
- Réservations en attente de confirmation
- Réservations confirmées
- Réservations prévues aujourd'hui
- Revenus totaux générés

---

### 2. 📋 Gestion des Réservations (`BookingManagementScreen`)

**Localisation :** `lib/screens/admin/booking_management_screen.dart`

**Fonctionnalités :**
- ✅ **Liste complète** de toutes les réservations
- ✅ **Recherche** par service, client, notes
- ✅ **Filtres** par statut :
  - Toutes
  - En attente
  - Confirmées
  - Terminées
  - Annulées
- ✅ **Actions sur chaque réservation** :
  - Confirmer une réservation en attente
  - Annuler une réservation
  - Marquer comme terminée
- ✅ **Détails complets** de chaque réservation :
  - Service réservé
  - Date et heure
  - Prix
  - Statut
  - ID de réservation
  - ID client
  - Notes

**Actions disponibles :**
- **Confirmer** : Change le statut de "pending" à "confirmed"
- **Annuler** : Change le statut à "cancelled"
- **Terminer** : Change le statut de "confirmed" à "completed"

---

### 3. 📈 Statistiques Détaillées (`AdminStatisticsScreen`)

**Localisation :** `lib/screens/admin/admin_statistics_screen.dart`

**Fonctionnalités :**
- ✅ **Sélection de période** (par mois)
- ✅ **Statistiques détaillées** :
  - Total des réservations
  - Réservations terminées
  - Réservations en attente
  - Revenus du mois
- ✅ **Services les plus demandés** :
  - Top 5 des services
  - Nombre de réservations par service
  - Pourcentage de part de marché
  - Barres de progression visuelles
- ✅ **Revenus par service** :
  - Liste des services avec revenus générés
  - Tri par revenus décroissants

---

## 🔐 Configuration Admin

### Définir un utilisateur comme administrateur

**Méthode 1 : Via le code**
```dart
final adminService = AdminService();
await adminService.setAdmin(userId, true);
```

**Méthode 2 : Via Firestore Console**
1. Allez dans Firebase Console > Firestore Database
2. Collection `users` > Document de l'utilisateur
3. Ajoutez le champ `isAdmin: true` ou `role: "admin"`

### Vérifier si un utilisateur est admin
```dart
final adminService = AdminService();
final isAdmin = await adminService.isAdmin();
```

---

## 📱 Écrans Utilisateur

### Mes Réservations (`UserBookingsScreen`)

**Localisation :** `lib/screens/profile/user_bookings_screen.dart`

Les utilisateurs peuvent voir leurs propres réservations :
- Liste de toutes leurs réservations
- Filtres par statut
- Détails complets de chaque réservation
- Possibilité d'annuler leurs réservations en attente

**Accès :** Profil > "Mes réservations"

---

## 🗄️ Structure Firestore

### Collection `bookings`
```json
{
  "id": "booking_id",
  "userId": "user_uid",
  "serviceId": "service_id",
  "serviceName": "Nom du service",
  "servicePrice": 150.0,
  "selectedDate": Timestamp,
  "selectedTime": "14:00",
  "specialistId": "specialist_id (optionnel)",
  "notes": "Notes du client",
  "status": "pending | confirmed | completed | cancelled",
  "createdAt": Timestamp,
  "updatedAt": Timestamp
}
```

### Collection `users`
```json
{
  "uid": "user_uid",
  "firstName": "Prénom",
  "lastName": "Nom",
  "email": "email@example.com",
  "phone": "+212 6XX XXX XXX",
  "isAdmin": true,  // Pour les administrateurs
  "role": "admin",  // Alternative à isAdmin
  "points": 0,
  "totalSpent": 0.0,
  "reservationsCount": 0,
  "createdAt": Timestamp
}
```

---

## 🔒 Sécurité

### Règles Firestore

Les règles Firestore doivent être mises à jour pour permettre aux admins de voir toutes les réservations :

```javascript
match /bookings/{bookingId} {
  allow create: if isSignedIn();
  allow read: if isSignedIn() && (
    request.resource.data.userId == request.auth.uid || 
    isAdmin()
  );
  allow update: if isSignedIn() && (
    request.resource.data.userId == request.auth.uid || 
    isAdmin()
  );
  allow delete: if isSignedIn() && (
    request.resource.data.userId == request.auth.uid || 
    isAdmin()
  );
}
```

**⚠️ Important :** Mettez à jour le fichier `firestore.rules` dans Firebase Console.

---

## 🚀 Utilisation

### Pour les Administrateurs

1. **Accéder au tableau de bord :**
   - Profil > Panneau d'administration
   - Ou directement : `AdminDashboardScreen()`

2. **Gérer les réservations :**
   - Tableau de bord > "Gérer les réservations"
   - Ou directement : `BookingManagementScreen()`

3. **Voir les statistiques :**
   - Tableau de bord > "Statistiques"
   - Ou directement : `AdminStatisticsScreen()`

### Pour les Utilisateurs

1. **Voir mes réservations :**
   - Profil > "Mes réservations"
   - Ou directement : `UserBookingsScreen()`

2. **Créer une réservation :**
   - Profil > "Nouvelle réservation"
   - Ou directement : `ServiceBookingScreen()`

---

## 📝 Notes Importantes

1. **Mot de passe admin :** Le mot de passe par défaut est `BoldAdmin2024`. **Changez-le en production !**

2. **Permissions :** Seuls les utilisateurs avec `isAdmin: true` peuvent accéder au panneau d'administration.

3. **Données en temps réel :** Toutes les données sont synchronisées en temps réel avec Firestore.

4. **Statistiques :** Les statistiques sont calculées en temps réel depuis Firestore.

---

## 🎯 Résumé

**Où trouver les réservations ?**
- **Pour les admins :** Panneau d'administration > Gestion des réservations
- **Pour les utilisateurs :** Profil > Mes réservations

**Fonctionnalités principales :**
- ✅ Tableau de bord avec statistiques
- ✅ Gestion complète des réservations
- ✅ Recherche et filtres avancés
- ✅ Statistiques détaillées par période
- ✅ Actions sur les réservations (confirmer, annuler, terminer)

Le panneau d'administration est maintenant **professionnel, sophistiqué et complet** ! 🎉




