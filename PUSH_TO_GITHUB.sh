#!/bin/bash

# Script pour pousser le projet vers GitHub
# Dépôt: https://github.com/Bestcrea/Bold-beauty-Lounge-.git

echo "=== Configuration GitHub pour Bold Beauty Lounge ==="
echo ""

# Vérifier qu'on est dans le bon répertoire
if [ ! -f "pubspec.yaml" ]; then
    echo "❌ Erreur: Ce script doit être exécuté depuis la racine du projet"
    exit 1
fi

echo "✅ Répertoire correct"
echo ""

# Vérifier l'état Git
echo "📋 État actuel du dépôt:"
git status --short | head -10
echo ""

# Ajouter tous les fichiers
echo "📦 Ajout de tous les fichiers..."
git add .
echo "✅ Fichiers ajoutés"
echo ""

# Créer le commit
echo "💾 Création du commit..."
git commit -m "Initial commit: Bold Beauty Lounge - Application mobile et panneau admin

- Application mobile Flutter complète
- Panneau d'administration web
- Configuration Firebase
- Gestion des réservations, employés, clients, services
- Développé par Bestcrea - M. Marouan Bahtit"
echo "✅ Commit créé"
echo ""

# Renommer la branche en main si nécessaire
CURRENT_BRANCH=$(git branch --show-current)
if [ "$CURRENT_BRANCH" != "main" ]; then
    echo "🔄 Renommage de la branche '$CURRENT_BRANCH' en 'main'..."
    git branch -M main
    echo "✅ Branche renommée en 'main'"
else
    echo "✅ Déjà sur la branche 'main'"
fi
echo ""

# Vérifier le remote
echo "🔗 Configuration du remote GitHub..."
git remote remove origin 2>/dev/null || true
git remote add origin https://github.com/Bestcrea/Bold-beauty-Lounge-.git
echo "✅ Remote configuré: https://github.com/Bestcrea/Bold-beauty-Lounge-.git"
echo ""

# Afficher le résumé
echo "=== RÉSUMÉ ==="
echo "📁 Dépôt local: $(pwd)"
echo "🌐 Remote GitHub: https://github.com/Bestcrea/Bold-beauty-Lounge-.git"
echo "🌿 Branche: main"
echo ""

# Demander confirmation avant de pousser
read -p "Voulez-vous pousser le code vers GitHub maintenant? (o/n) " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Oo]$ ]]; then
    echo "🚀 Poussage vers GitHub..."
    git push -u origin main
    echo ""
    echo "✅ Code poussé vers GitHub avec succès!"
    echo "🌐 Dépôt: https://github.com/Bestcrea/Bold-beauty-Lounge-"
else
    echo "⏸️  Poussage annulé. Vous pouvez pousser manuellement avec:"
    echo "   git push -u origin main"
fi



