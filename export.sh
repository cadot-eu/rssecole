#!/bin/bash

# Variables de connexion à la base de données
SQLITE_DB="var/data.db"      # Chemin vers votre base SQLite
POSTGRES_DB="your_postgres_db"          # Nom de votre base de données PostgreSQL
POSTGRES_USER="your_postgres_user"      # Utilisateur PostgreSQL
POSTGRES_PASSWORD="your_postgres_password"  # Mot de passe PostgreSQL

# Fichier SQL exporté
EXPORT_FILE="sqlite_to_postgres.sql"

# Exporter la base SQLite vers un fichier SQL
echo "Exporting SQLite to SQL file..."
sqlite3 $SQLITE_DB .dump > $EXPORT_FILE

# Adapter le fichier SQL pour PostgreSQL
echo "Converting SQL for PostgreSQL compatibility..."
sed -i '' 's/INTEGER PRIMARY KEY/ SERIAL PRIMARY KEY/g' $EXPORT_FILE
sed -i '' 's/`//g' $EXPORT_FILE      # Supprimer les backticks (`) pour la compatibilité PostgreSQL
sed -i '' 's/INSERT INTO "table_name"/INSERT INTO table_name/g' $EXPORT_FILE  # Supprimer les guillemets autour des tables

# Assurez-vous que les types sont bien adaptés pour PostgreSQL
sed -i '' 's/BOOLEAN/BOOLEAN/g' $EXPORT_FILE  # SQLite a parfois BOOLEAN comme un alias pour INTEGER
sed -i '' 's/TINYINT/SMALLINT/g' $EXPORT_FILE  # Adapter TINYINT à SMALLINT pour PostgreSQL
sed -i '' 's/VARCHAR/CHARACTER VARYING/g' $EXPORT_FILE  # Adapter VARCHAR

# Optionnel : vous pouvez ajouter ou adapter d'autres ajustements spécifiques ici

