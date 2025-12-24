#!/bin/bash

# Script de transfert APK pour Samsung S22
# Bold Beauty Lounge - Version Bêta

echo "🚀 Bold Beauty Lounge - Transfert APK vers Samsung S22"
echo "=================================================="

# Vérifier si l'APK existe
APK_PATH="build/app/outputs/flutter-apk/app-release.apk"

if [ ! -f "$APK_PATH" ]; then
    echo "❌ Erreur: APK non trouvé à $APK_PATH"
    echo "💡 Exécutez d'abord: flutter build apk --release"
    exit 1
fi

echo "✅ APK trouvé: $APK_PATH"
echo "📱 Taille: $(du -h "$APK_PATH" | cut -f1)"

# Créer un dossier de transfert
TRANSFER_DIR="$HOME/Desktop/Bold_Beauty_Lounge_APK"
mkdir -p "$TRANSFER_DIR"

# Copier l'APK
cp "$APK_PATH" "$TRANSFER_DIR/"

# Copier le guide d'installation
cp "INSTALLATION_GUIDE.md" "$TRANSFER_DIR/"

echo ""
echo "📁 Fichiers copiés dans: $TRANSFER_DIR"
echo "   ├── app-release.apk"
echo "   └── INSTALLATION_GUIDE.md"
echo ""

# Instructions de transfert
echo "📱 Instructions de transfert vers Samsung S22:"
echo "   1. Connectez votre Samsung S22 via USB"
echo "   2. Copiez app-release.apk sur votre téléphone"
echo "   3. Ou utilisez AirDrop pour envoyer l'APK"
echo "   4. Suivez le guide d'installation"
echo ""

# Ouvrir le dossier de transfert
echo "🔍 Ouverture du dossier de transfert..."
open "$TRANSFER_DIR"

echo "✅ Transfert préparé avec succès !"
echo "🎉 Votre APK Bold Beauty Lounge est prêt pour l'installation !"















