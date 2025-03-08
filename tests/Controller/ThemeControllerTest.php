<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\ThemeRepository;
use App\Repository\FluxRepository;
use Doctrine\ORM\EntityManagerInterface;

final class ThemeControllerTest extends WebTestCase
{
    private $client;
    private $themeRepository;
    private $fluxRepository;
    private $theme = '';

    protected function setUp(): void
    {

        $this->client = static::createClient();
        $this->themeRepository = static::getContainer()->get(ThemeRepository::class);
        $this->fluxRepository = static::getContainer()->get(FluxRepository::class);
        $this->theme = $this->client->getContainer()->get('router')->generate('theme');
    }
    public function testIndex(): void
    {
        $this->client->request('GET', $this->theme);

        self::assertResponseIsSuccessful();
    }
    /**
     * test que l'ajout d'un thème rajoute bien un thème dans la bd avec 200 et un nouveau flux
     */
    function testAddThemeAndFlux(): void
    {
        $this->testIndex();
        //on récupère le nombre de themes dans la bd
        $nbTheme = count($this->themeRepository->findAll());
        $nbFlux = count($this->fluxRepository->findAll());
        $this->client->submitForm('Ajouter', [
            'nom' => 'test',
            'url' => 'https://www.google.com/',
        ]);
        $this->client->followRedirect();
        $nbThemeAfterAdd = count($this->themeRepository->findAll());
        $fluxAfteradd = count($this->fluxRepository->findAll());
        self::assertResponseIsSuccessful();
        self::assertEquals($nbTheme + 1, $nbThemeAfterAdd);
        self::assertEquals($nbFlux + 1, $fluxAfteradd);
    }
    /**
     * test que l'ajout d'un thème existant ne modifie pas le nombre de thème dans la bd
     */
    function testAddThemeExistant(): void
    {
        $this->testAddThemeAndFlux();
        //on récupère le nombre de themes dans la bd
        $nbTheme = count($this->themeRepository->findAll());
        $this->client->submitForm('Ajouter', [
            'nom' => 'test',
            'url' => 'https://www.google.com/',
        ]);
        $this->client->followRedirect();
        $nbThemeAfterAdd = count($this->themeRepository->findAll());
        self::assertEquals($nbTheme, $nbThemeAfterAdd);
    }
    /**
     * On ajoute un flux dans un thème existant et on verifie que le nombre de flux ne change pas
     * puis on ajoute un nouveau flux et on vérifie que le nombre de flux change
     */
    function testAddThemeExistantAndFlux(): void
    {

        $this->testAddThemeAndFlux();
        $nbTheme = count($this->themeRepository->findAll());
        $nbFlux = count($this->fluxRepository->findAll());
        //avec un nouveau flux
        $this->client->submitForm('Ajouter', [
            'nom' => 'test',
            'url' => 'https://www.free.com/',
        ]);
        $this->client->followRedirect();
        $nbThemeAfterAdd = count($this->themeRepository->findAll());
        $fluxAfteradd = count($this->fluxRepository->findAll());
        self::assertEquals($nbTheme, $nbThemeAfterAdd);
        self::assertEquals($nbFlux + 1, $fluxAfteradd);
    }
    function testRemoveFlux(): void
    {
        $this->testAddThemeAndFlux();
        $nbFlux = count($this->fluxRepository->findAll());
        $crawler = $this->client->request('GET', $this->theme);
        //on sélectionne le premier flux puis le premer input dedans
        $buttonflux = $crawler->filter('.supprimerFlux')->first();
        $idFlux = $buttonflux->attr('value');
        //on envoie le form de l'input
        $this->client->submit($buttonflux->form());
        $this->client->followRedirect();
        self::assertEquals($nbFlux - 1, count($this->fluxRepository->findAll()));
    }
    public function testGetArticles(): void
    {
        $this->testAddThemeAndFlux();
        $theme=$this->themeRepository->findAll()[0];
        $flux=$theme->getFluxs()[0];
        $nbArticles = count($flux->getArticles());
        $flux->LoadArticles();
        self::assertLessThan(count($flux->getArticles()),$nbArticles);
    }
   
}
