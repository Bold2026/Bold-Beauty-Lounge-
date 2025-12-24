# Bold Beauty Lounge - Application Mobile & Panneau d'Administration

Application mobile Flutter pour Bold Beauty Lounge, salon de beauté à Casablanca, avec panneau d'administration web intégré.

## 📱 Fonctionnalités

### Application Mobile
- Réservation de services en ligne
- Gestion de profil utilisateur
- Consultation des services et offres
- Équipe de spécialistes
- Chatbot intégré
- Authentification sécurisée

### Panneau d'Administration Web
- Gestion des réservations
- Gestion des employés
- Gestion des clients
- Gestion des services et catégories
- Gestion des créneaux horaires
- Tableau de bord avec statistiques
- Avis clients

## 🛠️ Technologies

- **Flutter** 3.0+
- **Firebase** (Authentication, Firestore, Storage, Analytics, Messaging)
- **Provider** (State Management)
- **Material 3** (Design System)

## 📋 Prérequis

- Flutter SDK 3.0.0 ou supérieur
- Dart SDK 3.0.0 ou supérieur
- Android Studio / Xcode (pour le développement mobile)
- Compte Firebase configuré

## 🚀 Installation

### 1. Cloner le projet

```bash
git clone https://github.com/Bestcrea/Bold-beauty-Lounge-.git
cd Bold-beauty-Lounge-
```

### 2. Installer les dépendances

```bash
flutter pub get
```

### 3. Configurer Firebase

```bash
# Installer FlutterFire CLI
dart pub global activate flutterfire_cli

# Configurer Firebase pour votre projet
flutterfire configure --project=bold-beauty-app
```

Cette commande génère automatiquement le fichier `lib/firebase_options.dart` avec votre configuration Firebase.

### 4. Configuration Android

Le projet est déjà configuré avec :
- Google Services plugin
- Firebase BoM 34.7.0
- Dépendances Firebase nécessaires

### 5. Lancer l'application

#### Application Mobile
```bash
flutter run
```

#### Panneau d'Administration Web
```bash
flutter run -d chrome --web-port=8080 lib/main_admin_direct.dart
```

## 📁 Structure du Projet

```
lib/
├── main.dart                    # Point d'entrée application mobile
├── main_admin_direct.dart       # Point d'entrée panneau admin
├── firebase_options.dart        # Configuration Firebase (généré)
├── models/                      # Modèles de données
├── screens/                     # Écrans application mobile
│   ├── home/
│   ├── booking/
│   ├── profile/
│   └── ...
├── screens/admin_web/           # Écrans panneau admin
│   ├── admin_main_screen.dart
│   ├── admin_bookings_screen.dart
│   ├── admin_employees_screen.dart
│   └── ...
├── providers/                   # State management
│   └── admin/                   # Providers panneau admin
├── repositories/               # Accès aux données
│   └── admin/                   # Repositories panneau admin
├── services/                    # Services (Firebase, Analytics, etc.)
└── widgets/                     # Widgets réutilisables
    └── admin/                   # Widgets panneau admin
```

## 🎨 Design

L'application utilise la palette de couleurs **BOLD BEAUTY LOUNGE** :
- **Noir** : `#000000`
- **Blanc** : `#FFFFFF`
- **Beige** : `#DDD1BC`

## 🔐 Configuration Firebase

### Services Requis

1. **Authentication**
   - Email/Password activé
   - Comptes admin configurés dans Firestore

2. **Firestore Database**
   - Collections : `bookings`, `users`, `employees`, `services`, `categories`, `reviews`

3. **Storage**
   - Pour les images des employés et services

4. **Analytics** (optionnel)
   - Pour le suivi des statistiques

### Structure Firestore

```
bookings/
  └── {bookingId}
      ├── userId
      ├── serviceId
      ├── employeeId
      ├── date
      ├── time
      └── status

employees/
  └── {employeeId}
      ├── name
      ├── email
      ├── role
      ├── phone
      └── isActive

services/
  └── {serviceId}
      ├── name
      ├── category
      ├── duration
      ├── price
      └── isActive
```

## 📝 Notes Importantes

### Panneau d'Administration

- Le panneau admin est accessible uniquement via `lib/main_admin_direct.dart`
- L'authentification admin nécessite un compte configuré dans Firestore
- Les données sont synchronisées en temps réel avec Firestore

### Firebase Options

⚠️ **Important** : Le fichier `lib/firebase_options.dart` contient actuellement des valeurs placeholder. Vous devez exécuter `flutterfire configure` pour générer la configuration réelle.

## 🐛 Dépannage

### Erreur "No Firebase App '[DEFAULT]' has been created"
- Vérifiez que `flutterfire configure` a été exécuté
- Vérifiez que `firebase_options.dart` contient des valeurs réelles (pas des placeholders)

### Erreur de compilation Android
- Vérifiez que `android/app/build.gradle.kts` contient le plugin Google Services
- Vérifiez que les dépendances Firebase sont présentes

### Le panneau admin ne se charge pas
- Vérifiez la console du navigateur pour les erreurs
- Vérifiez que Firebase est correctement initialisé
- Vérifiez que les règles Firestore autorisent l'accès

## 📄 Licence

Ce projet est propriétaire de Bold Beauty Lounge.

## 👥 Développement

**Développeur** : M. Marouan Bahtit  
**Téléphone** : +212 636 499 140  
**Email** : bahtitmarouan@gmail.com / bahtitmarouan02@gmail.com

## 🏢 Entreprise

**Entreprise** : Bestcrea  
**Fondateur** : M. Marouan Bahtit  
**Contact** : 0636499140  
**Email** : contact@bestcrea.com  
**Site Web** : [www.bestcrea.com](https://www.bestcrea.com)

## 📞 Support

Pour toute question ou problème, contactez :
- **Email** : contact@bestcrea.com
- **Téléphone** : +212 636 499 140
- **Développeur** : bahtitmarouan@gmail.com

---

**Version** : 1.0.0+1  
**Dernière mise à jour** : 2024  
**Développé par** : Bestcrea - M. Marouan Bahtit

# Bold-Beauty-Lounge-
