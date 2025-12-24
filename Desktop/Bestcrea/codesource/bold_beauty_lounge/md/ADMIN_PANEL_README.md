# Bold Beauty Lounge - Panneau d'Administration

Panneau d'administration web complet pour la gestion du salon de beauté Bold Beauty Lounge.

## 🚀 Fonctionnalités

### Pages disponibles

1. **Page de connexion**
   - Authentification sécurisée avec Firebase Auth
   - Vérification du rôle administrateur

2. **Tableau de bord**
   - Statistiques en temps réel
   - Réservations du jour
   - Réservations du mois
   - Service le plus réservé

3. **Gestion des réservations**
   - Liste complète des réservations
   - Filtres par date, service, statut
   - Actions : confirmer, annuler, supprimer
   - Tableau interactif avec toutes les informations

4. **Gestion des services**
   - Ajouter, modifier, supprimer des services
   - Activer/désactiver des services
   - Gestion complète des prix, durées, catégories

5. **Gestion des créneaux horaires**
   - Configuration des heures de travail
   - Durée des créneaux (30 ou 60 minutes)
   - Désactivation de dates spécifiques

## 📋 Prérequis

- Flutter SDK (version 3.0.0 ou supérieure)
- Firebase configuré avec :
  - Authentication activée
  - Firestore activé
  - Collections : `bookings`, `services`, `admins`, `timeSlots`

## 🏗️ Structure du projet

```
lib/
├── models/admin/
│   ├── booking_model.dart
│   ├── service_model.dart
│   ├── admin_model.dart
│   └── time_slot_model.dart
├── repositories/admin/
│   ├── bookings_repository.dart
│   ├── services_repository.dart
│   ├── admin_repository.dart
│   └── time_slots_repository.dart
├── providers/admin/
│   ├── admin_auth_provider.dart
│   ├── bookings_provider.dart
│   ├── services_provider.dart
│   ├── dashboard_provider.dart
│   └── time_slots_provider.dart
└── screens/admin_web/
    ├── admin_login_screen.dart
    ├── admin_main_screen.dart
    ├── admin_dashboard_screen.dart
    ├── admin_bookings_screen.dart
    ├── admin_services_screen.dart
    └── admin_time_slots_screen.dart
```

## 🚀 Lancement

### Pour le développement web

```bash
flutter run -d chrome --web-port=8080 lib/main_admin_web.dart
```

### Pour la production

```bash
flutter build web --release --target=lib/main_admin_web.dart
```

Les fichiers seront générés dans `build/web/`.

## 🔐 Configuration Firebase

### Créer un administrateur

1. Créez un utilisateur dans Firebase Authentication
2. Ajoutez un document dans la collection `admins` avec cette structure :

```json
{
  "email": "admin@boldbeauty.com",
  "name": "Nom Admin",
  "role": "admin",
  "isActive": true,
  "createdAt": "2024-01-01T00:00:00Z"
}
```

L'ID du document doit correspondre à l'UID de l'utilisateur Firebase Auth.

### Structure Firestore

#### Collection `bookings`
```json
{
  "userId": "user_id",
  "userName": "Nom Client",
  "userEmail": "client@email.com",
  "userPhone": "+212600000000",
  "serviceId": "service_id",
  "serviceName": "Nom Service",
  "date": "Timestamp",
  "time": "14:30",
  "employeeId": "employee_id (optional)",
  "employeeName": "Nom Employé (optional)",
  "status": "pending|confirmed|cancelled|completed",
  "amount": 150.0,
  "createdAt": "Timestamp",
  "updatedAt": "Timestamp (optional)"
}
```

#### Collection `services`
```json
{
  "name": "Nom du service",
  "description": "Description",
  "price": 150.0,
  "duration": 30,
  "category": "Coiffure",
  "isActive": true,
  "imageUrl": "url (optional)",
  "createdAt": "Timestamp",
  "updatedAt": "Timestamp (optional)"
}
```

#### Collection `admins`
```json
{
  "email": "admin@email.com",
  "name": "Nom Admin",
  "role": "admin|super_admin",
  "isActive": true,
  "createdAt": "Timestamp",
  "lastLoginAt": "Timestamp (optional)"
}
```

#### Collection `timeSlots`
```json
{
  "startHour": 9,
  "startMinute": 0,
  "endHour": 18,
  "endMinute": 0,
  "slotDuration": 30,
  "disabledDates": ["Timestamp"],
  "createdAt": "Timestamp",
  "updatedAt": "Timestamp (optional)"
}
```

## 🎨 Design

Le panneau utilise le design system de Bold Beauty Lounge :
- **Couleurs principales** : Blanc, Noir, Beige (#DDD1BC)
- **Material 3** : Design moderne et épuré
- **Responsive** : Adapté pour desktop et tablette

## 🔒 Sécurité

- Routes protégées : Seuls les administrateurs authentifiés peuvent accéder
- Vérification du rôle : Vérification dans Firestore à chaque connexion
- Transactions Firestore : Prévention des doubles réservations

## 📝 Notes importantes

1. **Double réservation** : Le système empêche automatiquement deux réservations au même créneau horaire
2. **Statuts des réservations** :
   - `pending` : En attente de confirmation
   - `confirmed` : Confirmée
   - `cancelled` : Annulée
   - `completed` : Terminée

3. **Créneaux horaires** : La durée par défaut est de 30 minutes, configurable à 60 minutes

## 🐛 Dépannage

### Erreur d'authentification
- Vérifiez que l'utilisateur existe dans Firebase Auth
- Vérifiez que le document existe dans la collection `admins`
- Vérifiez que `isActive` est à `true`

### Données non affichées
- Vérifiez les règles Firestore
- Vérifiez que les collections existent
- Vérifiez la console Firebase pour les erreurs

## 📞 Support

Pour toute question ou problème, contactez l'équipe de développement.





