BEGIN TRANSACTION;
DROP TABLE IF EXISTS "theme";
CREATE TABLE IF NOT EXISTS "theme" (
	"id"	INTEGER NOT NULL,
	"nom"	VARCHAR(255) NOT NULL,
	PRIMARY KEY("id" AUTOINCREMENT)
);
DROP TABLE IF EXISTS "pub";
CREATE TABLE IF NOT EXISTS "pub" (
	"id"	INTEGER NOT NULL,
	"flux_id"	INTEGER NOT NULL,
	"html"	CLOB NOT NULL,
	"chaine"	CLOB DEFAULT NULL,
	PRIMARY KEY("id" AUTOINCREMENT),
	CONSTRAINT "FK_5A443C85C85926E" FOREIGN KEY("flux_id") REFERENCES "flux"("id") NOT DEFERRABLE INITIALLY IMMEDIATE
);
DROP TABLE IF EXISTS "arti";
CREATE TABLE IF NOT EXISTS "arti" (
	"id"	INTEGER NOT NULL,
	PRIMARY KEY("id" AUTOINCREMENT)
);
DROP TABLE IF EXISTS "article";
CREATE TABLE IF NOT EXISTS "article" (
	"id"	INTEGER NOT NULL,
	"flux_id"	INTEGER DEFAULT NULL,
	"url"	VARCHAR(255) NOT NULL,
	"etat"	VARCHAR(100) DEFAULT NULL,
	"titre"	VARCHAR(255) DEFAULT NULL,
	"infos"	CLOB DEFAULT NULL,
	"sitename"	VARCHAR(255) DEFAULT NULL,
	"author"	VARCHAR(255) DEFAULT NULL,
	"content"	CLOB DEFAULT NULL,
	"image"	VARCHAR(255) DEFAULT NULL,
	"priorite"	INTEGER DEFAULT NULL,
	"lecturemn"	INTEGER DEFAULT NULL,
	"notes"	CLOB DEFAULT NULL,
	PRIMARY KEY("id" AUTOINCREMENT),
	CONSTRAINT "FK_23A0E66C85926E" FOREIGN KEY("flux_id") REFERENCES "flux"("id") NOT DEFERRABLE INITIALLY IMMEDIATE
);
DROP TABLE IF EXISTS "flux";
CREATE TABLE IF NOT EXISTS "flux" (
	"id"	INTEGER NOT NULL,
	"theme_id"	INTEGER DEFAULT NULL,
	"url"	VARCHAR(255) NOT NULL,
	"nom"	VARCHAR(255) DEFAULT NULL,
	"domaine"	VARCHAR(255) DEFAULT NULL,
	"bas_pub"	VARCHAR(255) DEFAULT NULL,
	PRIMARY KEY("id" AUTOINCREMENT),
	CONSTRAINT "FK_7252313A59027487" FOREIGN KEY("theme_id") REFERENCES "theme"("id") NOT DEFERRABLE INITIALLY IMMEDIATE
);
DROP TABLE IF EXISTS "question";
CREATE TABLE IF NOT EXISTS "question" (
	"id"	INTEGER NOT NULL,
	"article_id"	INTEGER NOT NULL,
	"texte"	CLOB DEFAULT NULL,
	"question"	VARCHAR(255) NOT NULL,
	"reponse"	CLOB DEFAULT NULL,
	"etat"	BOOLEAN DEFAULT NULL,
	CONSTRAINT "FK_B6F7494E7294869C" FOREIGN KEY("article_id") REFERENCES "article"("id") NOT DEFERRABLE INITIALLY IMMEDIATE,
	PRIMARY KEY("id" AUTOINCREMENT)
);
DROP TABLE IF EXISTS "marque";
CREATE TABLE IF NOT EXISTS "marque" (
	"id"	INTEGER NOT NULL,
	"article_id"	INTEGER NOT NULL,
	"style"	VARCHAR(255) NOT NULL,
	"selection"	CLOB DEFAULT NULL,
	"etat"	BOOLEAN DEFAULT NULL,
	CONSTRAINT "FK_5A6F91CE7294869C" FOREIGN KEY("article_id") REFERENCES "article"("id") NOT DEFERRABLE INITIALLY IMMEDIATE,
	PRIMARY KEY("id" AUTOINCREMENT)
);
DROP TABLE IF EXISTS "messenger_messages";
CREATE TABLE IF NOT EXISTS "messenger_messages" (
	"id"	INTEGER NOT NULL,
	"body"	CLOB NOT NULL,
	"headers"	CLOB NOT NULL,
	"queue_name"	VARCHAR(190) NOT NULL,
	"created_at"	DATETIME NOT NULL,
	"available_at"	DATETIME NOT NULL,
	"delivered_at"	DATETIME DEFAULT NULL,
	PRIMARY KEY("id" AUTOINCREMENT)
);
DROP INDEX IF EXISTS "IDX_5A443C85C85926E";
CREATE INDEX IF NOT EXISTS "IDX_5A443C85C85926E" ON "pub" (
	"flux_id"
);
DROP INDEX IF EXISTS "IDX_23A0E66C85926E";
CREATE INDEX IF NOT EXISTS "IDX_23A0E66C85926E" ON "article" (
	"flux_id"
);
DROP INDEX IF EXISTS "IDX_7252313A59027487";
CREATE INDEX IF NOT EXISTS "IDX_7252313A59027487" ON "flux" (
	"theme_id"
);
DROP INDEX IF EXISTS "IDX_B6F7494E7294869C";
CREATE INDEX IF NOT EXISTS "IDX_B6F7494E7294869C" ON "question" (
	"article_id"
);
DROP INDEX IF EXISTS "IDX_5A6F91CE7294869C";
CREATE INDEX IF NOT EXISTS "IDX_5A6F91CE7294869C" ON "marque" (
	"article_id"
);
DROP INDEX IF EXISTS "IDX_75EA56E0FB7336F0";
CREATE INDEX IF NOT EXISTS "IDX_75EA56E0FB7336F0" ON "messenger_messages" (
	"queue_name"
);
DROP INDEX IF EXISTS "IDX_75EA56E0E3BD61CE";
CREATE INDEX IF NOT EXISTS "IDX_75EA56E0E3BD61CE" ON "messenger_messages" (
	"available_at"
);
DROP INDEX IF EXISTS "IDX_75EA56E016BA31DB";
CREATE INDEX IF NOT EXISTS "IDX_75EA56E016BA31DB" ON "messenger_messages" (
	"delivered_at"
);
COMMIT;
