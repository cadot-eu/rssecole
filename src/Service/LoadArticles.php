<?php

namespace App\Service;

use DOMDocument;
use App\Entity\Article;
use App\Repository\ArticleRepository;

class LoadArticles
{

    public function load($urlFlux): array|string
    {
        $articles = array();
        $content = $this->getContentWithContext($urlFlux);
        // Utilisation de DOMDocument pour charger le XML
        $doc = new DOMDocument();
        libxml_use_internal_errors(true); // Ignore des erreurs
        if (empty($content)) return false;
        $doc->loadXML($content);
        libxml_clear_errors();
        // Collecter tous les articles du flux
        foreach ($doc->getElementsByTagName('item') as $article) {

            $title = $article->getElementsByTagName('title')->item(0) !== null ? $article->getElementsByTagName('title')->item(0)->textContent : 'pas de titre pour le flux de ' . $urlFlux;
            $link = $article->getElementsByTagName('link')->item(0)->textContent;
            //nettoyage du slash a la fin
            if (substr($link, -1) == '/') {
                $link = rtrim($link, '/');
            }
            $article = new Article();
            $article->setUrl($link);
            $article->setTitre($title);
            $articles[] = $article;
        }
        return $articles;
    }
    private function getContentWithContext($flux)
    {
        // Création du contexte pour la requête HTTP
        $context = stream_context_create([
            'http' => [
                "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
            ],
        ]);

        // Récupération des en-têtes HTTP
        $headers = @get_headers($flux, 1, $context);

        if (!$headers) {
            echo "Impossible de récupérer les en-têtes HTTP de " . $flux;
            return false;
        }

        // Vérification du code de statut
        $statusLine = $headers[0];
        preg_match('{HTTP\/\S*\s(\d{3})}', $statusLine, $match);
        $statusCode = $match[1];

        // La logique était inversée ici - on veut continuer si le code N'EST PAS 404
        if ($statusCode === '404') {
            echo "L'URL renvoie une erreur 404 : " . $flux;
            return false;
        }

        // Récupération du contenu
        try {
            $content = file_get_contents($flux, false, $context);
        } catch (\Exception $e) {

            return false;
        }

        if ($content === false) {
            echo "Échec de la récupération du contenu de " . $flux;
            return false;
        }

        return $content;
    }
}
