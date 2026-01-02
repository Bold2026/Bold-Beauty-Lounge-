# 🏗️ Structure Détaillée de la Page Home

**Fichier :** `lib/screens/home/offline_home_screen.dart`  
**Type :** `StatefulWidget`  
**Lignes :** 2315 lignes

---

## 📐 Structure Hiérarchique Complète

```
OfflineHomeScreen (StatefulWidget)
│
├── Scaffold
│   ├── backgroundColor: Colors.black
│   │
│   └── SafeArea
│       └── ListView (physics: BouncingScrollPhysics)
│           │
│           ├── 1. _buildHeader(context)
│           │   │
│           │   ├── Container (Gradient Header)
│           │   │   ├── Row (Logo + Icônes)
│           │   │   │   ├── Logo Container (56x56)
│           │   │   │   ├── Spacer
│           │   │   │   ├── _buildHeaderIconButton (Profil)
│           │   │   │   └── _buildHeaderIconButton (Notifications)
│           │   │   │
│           │   │   ├── SizedBox(height: 26)
│           │   │   │
│           │   │   ├── _buildSearchBar()
│           │   │   │   ├── Expanded (TextField)
│           │   │   │   └── Container (Bouton Favoris)
│           │   │   │
│           │   │   └── SizedBox(height: 24)
│           │   │       │
│           │   │       └── _buildLoyaltyCard(context)
│           │   │           ├── Row (Solde + QR Code)
│           │   │           │   ├── Column (Solde fidélité)
│           │   │           │   │   ├── Text ("Votre solde fidélité")
│           │   │           │   │   ├── Row ("0 pts" + Badge)
│           │   │           │   │   └── Text (Description)
│           │   │           │   └── _buildHeaderIconButton (QR Code)
│           │   │           └── Container (Message info)
│           │   │
│           ├── SizedBox(height: 20)
│           │
│           ├── 2. _buildLoyaltySection(context)
│           │   │
│           │   ├── Container (Margin)
│           │   │   ├── Column
│           │   │   │   ├── Text ("Nos Avantages et Fidélité")
│           │   │   │   │
│           │   │   │   ├── SingleChildScrollView (Horizontal)
│           │   │   │   │   └── Row (ChoiceChips)
│           │   │   │   │       ├── Recommandé
│           │   │   │   │       ├── Promotion
│           │   │   │   │       ├── Distance
│           │   │   │   │       ├── Favoris
│           │   │   │   │       ├── Gagner
│           │   │   │   │       └── Historique
│           │   │   │   │
│           │   │   │   ├── SizedBox(height: 20)
│           │   │   │   │
│           │   │   │   └── AnimatedSwitcher
│           │   │   │       └── _buildLoyaltyContent(context, tab)
│           │   │   │           ├── _buildRecommendedContent()
│           │   │   │           ├── _buildPromotionContent()
│           │   │   │           ├── _buildDistanceContent()
│           │   │   │           ├── _buildFavoritesContent()
│           │   │   │           ├── _buildEarnPointsContent()
│           │   │   │           └── _buildHistoryContent()
│           │
│           ├── 3. _buildCategoriesSection()
│           │   │
│           │   ├── Container (Margin)
│           │   │   ├── Column
│           │   │   │   ├── Text ("Nos Catégories") [Poppins, Animated]
│           │   │   │   │
│           │   │   │   └── SizedBox (height: 220)
│           │   │   │       └── PageView.builder
│           │   │   │           └── Row (3 catégories par slide)
│           │   │   │               └── Expanded
│           │   │   │                   └── _buildCategoryCard(service)
│           │   │   │                       ├── GestureDetector
│           │   │   │                       └── Container
│           │   │   │                           └── Stack
│           │   │   │                               ├── Image.asset
│           │   │   │                               ├── Gradient Overlay
│           │   │   │                               └── Text (Nom catégorie)
│           │
│           ├── 4. _buildComboPacksSection(context)
│           │   │
│           │   ├── Container (Margin)
│           │   │   ├── Column
│           │   │   │   ├── Text ("Packs combinés") [Poppins, Animated]
│           │   │   │   ├── Text (Description)
│           │   │   │   │
│           │   │   │   ├── SizedBox (height: 410)
│           │   │   │   │   └── PageView.builder
│           │   │   │   │       └── AnimatedBuilder
│           │   │   │   │           └── Transform.scale
│           │   │   │   │               └── _buildPackCard(pack)
│           │   │   │   │                   ├── GestureDetector
│           │   │   │   │                   └── Container
│           │   │   │   │                       ├── Text (Nom pack)
│           │   │   │   │                       ├── Text (Tagline)
│           │   │   │   │                       ├── Text (Prix)
│           │   │   │   │                       ├── ListView (Détails)
│           │   │   │   │                       └── ElevatedButton ("Réservez")
│           │   │   │   │
│           │   │   │   ├── SizedBox(height: 14)
│           │   │   │   │
│           │   │   │   └── _buildPageIndicator(controller, count)
│           │
│           ├── 5. _buildTeamSection()
│           │   │
│           │   ├── Container (Margin)
│           │   │   ├── Column
│           │   │   │   ├── Text ("Nos Spécialistes")
│           │   │   │   │
│           │   │   │   ├── SizedBox (height: 420)
│           │   │   │   │   └── PageView.builder
│           │   │   │   │       └── AnimatedBuilder
│           │   │   │   │           └── Transform.scale
│           │   │   │   │               └── _buildSpecialistCard(specialist)
│           │   │   │   │                   ├── Container (Blanc)
│           │   │   │   │                   │   ├── Expanded
│           │   │   │   │                   │   │   └── Image.asset (Photo)
│           │   │   │   │                   │   └── Padding
│           │   │   │   │                   │       ├── Text (Nom)
│           │   │   │   │                   │       └── Text (Titre)
│           │   │   │   │
│           │   │   │   ├── SizedBox(height: 16)
│           │   │   │   │
│           │   │   │   └── _buildPageIndicator(controller, count)
│           │
│           ├── 6. _buildPourElleBanner(context)
│           │   │
│           │   ├── Container (Margin)
│           │   │   ├── Column
│           │   │   │   ├── Text ("Pour Elle")
│           │   │   │   │
│           │   │   │   └── Container (height: 220)
│           │   │   │       └── ClipRRect
│           │   │   │           └── Stack
│           │   │   │               ├── Image.asset (boldbeautylounge.jpg)
│           │   │   │               ├── Gradient Overlay
│           │   │   │               └── Positioned (bottom)
│           │   │   │                   ├── Text ("Bold Beauty Lounge")
│           │   │   │                   ├── Text ("Salon de beauté")
│           │   │   │                   └── Row
│           │   │   │                       ├── Text ("★ 4.9 (127)")
│           │   │   │                       ├── Text ("Casablanca, Maroc")
│           │   │   │                       └── GestureDetector ("Voir plus")
│           │
│           ├── 7. _buildQuickActions(context)
│           │   │
│           │   ├── Container (Margin)
│           │   │   ├── Column
│           │   │   │   ├── Text ("Actions rapides")
│           │   │   │   │
│           │   │   │   └── LayoutBuilder
│           │   │   │       └── Wrap
│           │   │   │           └── List.generate (4 actions)
│           │   │   │               └── SizedBox (width: itemWidth)
│           │   │   │                   └── AnimatedOpacity + AnimatedSlide
│           │   │   │                       └── _buildQuickActionCard()
│           │   │   │                           ├── GestureDetector
│           │   │   │                           └── Container
│           │   │   │                               ├── Container (Icône)
│           │   │   │                               ├── Text (Titre)
│           │   │   │                               ├── Text (Sous-titre)
│           │   │   │                               └── Row ("Ouvrir" + Arrow)
│           │
│           └── SizedBox(height: 24)
```

---

## 🔧 Méthodes Principales

### 1. `build(BuildContext context)` - Ligne 1126
```dart
@override
Widget build(BuildContext context) {
  return Scaffold(
    backgroundColor: Colors.black,
    body: SafeArea(
      child: ListView(
        children: [
          _buildHeader(context),
          _buildLoyaltySection(context),
          _buildCategoriesSection(),
          _buildComboPacksSection(context),
          _buildTeamSection(),
          _buildPourElleBanner(context),
          _buildQuickActions(context),
        ],
      ),
    ),
  );
}
```

### 2. `_buildHeader(BuildContext context)` - Ligne 1149
- **Retourne :** Container avec gradient
- **Contient :** Logo, icônes, barre de recherche, carte fidélité

### 3. `_buildLoyaltySection(BuildContext context)` - Ligne 1251
- **Retourne :** Container avec onglets et contenu dynamique
- **Onglets :** 6 onglets avec ChoiceChips
- **Contenu :** AnimatedSwitcher avec contenu selon l'onglet

### 4. `_buildCategoriesSection()` - Ligne 1481
- **Retourne :** Container avec PageView de catégories
- **Affichage :** 3 catégories par slide
- **Navigation :** Vers DetailedPricingScreen

### 5. `_buildComboPacksSection(BuildContext context)` - Ligne 1532
- **Retourne :** Container avec PageView de packs
- **Affichage :** 1 pack par slide avec animations
- **Indicateurs :** Points indicateurs de page

### 6. `_buildTeamSection()` - Ligne 2007
- **Retourne :** Container avec PageView de spécialistes
- **Affichage :** 1 spécialiste par slide avec animations
- **Indicateurs :** Points indicateurs de page

### 7. `_buildPourElleBanner(BuildContext context)` - Ligne 1747
- **Retourne :** Container avec bannière image
- **Contenu :** Image, overlay, informations salon
- **Navigation :** Vers BoldBeautyDetailPage

### 8. `_buildQuickActions(BuildContext context)` - Ligne 2142
- **Retourne :** Container avec grille d'actions
- **Affichage :** 2x2 avec animations
- **Actions :** 4 actions rapides

---

## 📊 Données Statiques

### Services (6 services) - Lignes 21-64
```dart
static const List<Map<String, dynamic>> _services = [
  {'name': 'Coiffure', 'price': 'Dès 70 DH', ...},
  {'name': 'Onglerie', 'price': 'Dès 50 DH', ...},
  {'name': 'Hammam', 'price': 'Dès 150 DH', ...},
  {'name': 'Massages', 'price': 'Dès 100 DH', ...},
  {'name': 'Head Spa', 'price': 'Dès 350 DH', ...},
  {'name': 'Soins', 'price': 'Dès 25 DH', ...},
];
```

### Spécialistes (7 spécialistes) - Lignes 67-110
```dart
static const List<Map<String, dynamic>> _specialists = [
  {'name': 'Laila Bazzi', 'title': 'Directeur général', 'rating': 4.9, ...},
  {'name': 'Nasira Mounir', 'title': 'Esthéticienne Senior', 'rating': 4.8, ...},
  // ... 5 autres
];
```

### Packs Combinés (5 packs) - Lignes 112-165
```dart
static const List<Map<String, dynamic>> _comboPacks = [
  {'name': 'Pause Précieuse', 'price': '590 DH', ...},
  {'name': 'Douce Évasion', 'price': '650 DH', ...},
  // ... 3 autres
];
```

### Onglets Fidélité (6 onglets) - Lignes 167-174
```dart
final List<String> _loyaltyTabs = [
  'Recommandé',
  'Promotion',
  'Distance',
  'Favoris',
  'Gagner',
  'Historique',
];
```

---

## 🎨 Composants Réutilisables

### `_buildHeaderIconButton()` - Ligne 1228
- **Usage :** Icônes dans le header
- **Paramètres :** `icon`, `onTap`
- **Retourne :** Container avec icône cliquable

### `_buildCategoryCard()` - Ligne 1900
- **Usage :** Carte de catégorie
- **Paramètres :** `context`, `service`
- **Retourne :** GestureDetector avec image et texte

### `_buildPackCard()` - Ligne 1606
- **Usage :** Carte de pack combiné
- **Paramètres :** `context`, `pack`
- **Retourne :** Container avec détails du pack

### `_buildSpecialistCard()` - Ligne 2068
- **Usage :** Carte de spécialiste
- **Paramètres :** `specialist` (Map)
- **Retourne :** Container avec photo et infos

### `_buildQuickActionCard()` - Ligne 2236
- **Usage :** Carte d'action rapide
- **Paramètres :** `title`, `subtitle`, `icon`, `onTap`
- **Retourne :** Container avec icône et texte

### `_buildPageIndicator()` - Ligne 1714
- **Usage :** Indicateurs de page pour PageView
- **Paramètres :** `controller`, `itemCount`
- **Retourne :** Row avec points indicateurs

---

## 🔄 Contrôleurs et État

### PageControllers
```dart
late final PageController _specialistPageController;  // Ligne 179
late final PageController _packPageController;        // Ligne 180
```

### État
```dart
int _selectedLoyaltyTab = 0;        // Ligne 176
bool _animateSections = false;      // Ligne 181
```

### Initialisation (initState) - Ligne 1107
```dart
@override
void initState() {
  super.initState();
  _specialistPageController = PageController(viewportFraction: 0.88);
  _packPageController = PageController(viewportFraction: 0.88);
  Future.delayed(const Duration(milliseconds: 150), () {
    if (mounted) {
      setState(() => _animateSections = true);
    }
  });
}
```

### Nettoyage (dispose) - Ligne 1119
```dart
@override
void dispose() {
  _specialistPageController.dispose();
  _packPageController.dispose();
  super.dispose();
}
```

---

## 🎯 Navigation et Actions

### Méthodes de Navigation

1. **`_openCategory(context, category)`** - Ligne 1977
   - Navigation vers `DetailedPricingScreen`

2. **`_handlePackTap(context)`** - Ligne 183
   - Vérifie la connexion
   - Navigation vers profil ou modal d'inscription

3. **`_openGoogleMaps(context)`** - Ligne 1079
   - Ouvre Google Maps avec l'adresse du salon

4. **`_showAccountPrompt(context)`** - Ligne 975
   - Affiche un modal bottom sheet pour inscription/connexion

---

## 📱 Responsive Design

### LayoutBuilder
- Utilisé dans `_buildQuickActions` pour adapter la largeur des cartes
- Calcul : `(constraints.maxWidth - 24) / 2` pour 2 colonnes

### PageView avec viewportFraction
- `_specialistPageController`: `viewportFraction: 0.88`
- `_packPageController`: `viewportFraction: 0.88`
- Permet d'afficher une partie de la carte suivante

---

## 🎨 Animations

### Types d'animations utilisées :

1. **AnimatedSwitcher** - Ligne 1304
   - Transition entre les onglets de fidélité

2. **AnimatedDefaultTextStyle** - Ligne 1489
   - Animation des titres de section

3. **AnimatedBuilder** - Ligne 1571
   - Animation de zoom pour les packs et spécialistes

4. **Transform.scale** - Ligne 1588
   - Effet de zoom basé sur la position du PageView

5. **AnimatedOpacity + AnimatedSlide** - Ligne 2208
   - Apparition progressive des actions rapides

6. **AnimatedContainer** - Ligne 120
   - Transition des boutons de navigation

---

## 📐 Dimensions et Espacements

### Hauteurs fixes :
- Header : Gradient avec padding
- Catégories : 220px
- Packs : 410px
- Spécialistes : 420px
- Bannière "Pour Elle" : 220px

### Marges :
- Sections : `EdgeInsets.symmetric(horizontal: 20.0, vertical: 20.0)`
- Padding interne : Variable selon la section

---

## 🔍 Méthodes Utilitaires

### `_mapToPricingCategory(String category)` - Ligne 1986
- Convertit le nom de catégorie pour la navigation
- Mapping : "Massages" → "Massage & Spa"

### `_buildLoyaltyContent(context, tab)` - Ligne 195
- Retourne le contenu selon l'onglet sélectionné
- Switch case sur les 6 onglets

---

## ✅ Résumé de la Structure

```
Scaffold (Noir)
└── SafeArea
    └── ListView (Scrollable)
        ├── Header (Gradient + Logo + Recherche + Fidélité)
        ├── Section Fidélité (6 onglets avec contenu dynamique)
        ├── Section Catégories (PageView, 3 par slide)
        ├── Section Packs (PageView, animations zoom)
        ├── Section Spécialistes (PageView, animations zoom)
        ├── Bannière "Pour Elle" (Image + Infos)
        └── Actions Rapides (Grille 2x2, animations)
```

---

**Structure complète documentée !** ✅








