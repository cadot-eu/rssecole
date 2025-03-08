<?php

namespace App\Service;

use fivefilters\Readability\Readability;
use Symfony\Component\DomCrawler\Crawler;
use App\Entity\Article;
use fivefilters\Readability\Configuration;

class RenderArticle
{
    public function render(Article $article): array|bool
    {
        $readability = new Readability(new Configuration([
            'fixRelativeURLs' => true,
            'originalURL'     => $article->getBaseUrl(),
            'CleanConditionally' => false,
            'StripUnlikelyCandidates' => true,
            'MaxTopCandidates' => 5,
            'ArticleByline' => false


        ]));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $article->getUrl());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Suivre les redirections
        curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookies.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');


        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) ' .
            'AppleWebKit/537.36 (KHTML, like Gecko) ' .
            'Chrome/91.0.4472.124 Safari/537.36');

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Referer: https://www.google.com/',
            'Connection: keep-alive',
            'Accept-Encoding: gzip, deflate, br',
            'Accept-Language: en-US,en;q=0.9',
            'Upgrade-Insecure-Requests: 1',
            'Sec-Fetch-Dest: document',
            'Sec-Fetch-Mode: navigate',
            'Sec-Fetch-Site: none',
            'Sec-Fetch-User: ?1',
            'Viewport-Width: 1920',
            'Width: 1920',
            'DPR: 1.0',
            'Save-Data: on',
            'ECT: 4g'
        ]);

        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate, br');

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Erreur cURL: ' . curl_error($ch);
            curl_close($ch);
            return false;
        }

        // Séparation des en-têtes et du contenu
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $body = substr($response, $headerSize);

        curl_close($ch);

        // Nettoyage et conversion de l'encodage
        $body = preg_replace('/<\?xml.*?\?>/', '', $body);

        // Détection de l'encodage
        $encoding = mb_detect_encoding($body, ['UTF-8', 'ISO-8859-1', 'ISO-8859-15', 'ASCII']);

        // Conversion en UTF-8 si nécessaire
        if ($encoding && $encoding !== 'UTF-8') {
            $body = mb_convert_encoding($body, 'UTF-8', $encoding);
        }

        // Décodage des entités HTML
        $body = html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Nettoyage supplémentaire des caractères spéciaux si nécessaire
        $body = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', $body);

        //on remplace les &nbsp;
        $body = preg_replace('/&nbsp;/', ' ', $body);


        try {
            $readability->parse($body);
        } catch (\Exception $e) {
            return false;
        }
        $content = $readability->getContent();

        // foreach ($readability->getImages() as $images) {
        //     $content .= '<img src="' . $images . '" width="25%" height="auto">';
        // }
        $crawler = new Crawler($content);
        //on remplace les <span><span><a href="/planete/definitions/zoologie-emergence-8743/" universe="health">émergence</a></span></span> qui sont doublés en un texte simple
        foreach ($crawler->filter('span') as $element) {
            $text = $element->nodeValue;
            //on regarde si le node parent est un span
            if (isset($element->parentNode) && $element->parentNode->nodeName == 'span') {
                if (isset($element->parentNode->nextSibling) && $element->parentNode->nextSibling->nodeValue == $text) {
                    $element->parentNode->nextSibling->remove();
                }
            }
        }
        //on remplace les liens dans les images qui dommence par data: par sa balise data-src s'il existe
        foreach ($crawler->filter('img') as $element) {
            //on définit la largeur à 25% et la hauteur à auto
            //on vérifie la présence de l'attribut width
            $style = $element->getAttribute('style');
            if (!$element->hasAttribute('width') && strpos($style, 'width:') !== false) {
                $width = (int)trim(explode(';', explode('width:', $style)[1])[0]);
            } else {
                $width = (int)$element->getAttribute('width');
            }
            if ($width > 250) {
                $element->setAttribute('width', '100%');
                $element->setAttribute('height', 'auto');
                $element->setAttribute('style', '');
            }
            //on prend les images et on remplace le src par le data-src s'il existe
            if (strpos($element->getAttribute('src'), 'data:') === 0) {
                $dataSrc = $element->getAttribute('data-src');
                if ($dataSrc) {
                    $element->setAttribute('src', $dataSrc);
                }
            }
            //si le src commence par /
            // if ($element->getAttribute('src')[0] === '/') {
            //     //on prend l'url de base
            //     // Analyser l'URL pour obtenir ses composants
            //     $parsed_url = parse_url($url);
            //     // Construire l'URL de base en utilisant le schéma et l'hôte
            //     $base_url = $parsed_url['scheme'] . '://' . $parsed_url['host'];
            //     $src = $base_url . $element->getAttribute('src');
            //     $element->setAttribute('src', $src);
            // }


            // on supprime les images qui ont un src avec data:
            // if (strpos($element->getAttribute('src'), 'data:') === 0) {
            //     $element->parentNode->removeChild($element);
            // }
        }
        foreach ($crawler->filter('svg') as $element) {
            //on le supprime
            $element->parentNode->removeChild($element);
            //si on a un a comme parent on supprime les deux
            if (isset($element->parentNode) && $element->parentNode->nodeName == 'a') {
                $element->parentNode->removeChild($element);
            }
        }
        //pour l'affichage
        foreach ($crawler->filter('img') as $element) {
            //on définit la largeur à 25% et la hauteur à auto
            if ($element->getAttribute('width') == '100%') {
                $element->setAttribute('width', '25%');
            }
        }
        $content = $crawler->html();
        return ['readability' => $readability, 'content' => $content];
    }
}
