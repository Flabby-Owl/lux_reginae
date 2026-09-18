<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

const LR_CONTENT_FILE = __DIR__ . '/data/site_content.json';
const LR_WIKI_IMPORT_FILE = __DIR__ . '/data/wiki_content.json';

function lr_imported_wiki_content(): ?array
{
    if (!file_exists(LR_WIKI_IMPORT_FILE)) {
        return null;
    }

    $wiki = json_decode((string) file_get_contents(LR_WIKI_IMPORT_FILE), true);

    if (!is_array($wiki) || !is_array($wiki['articles'] ?? null)) {
        return null;
    }

    return $wiki;
}

function lr_default_content(): array
{
    $importedWiki = lr_imported_wiki_content();

    return [
        'home' => [
            'kicker' => 'Accueil',
            'title' => 'Une compagnie libre chill, active et francophone.',
            'intro' => 'Lux Reginae organise ses grosses activités le dimanche soir, et de plus petites sorties le reste de la semaine.',
            'facts' => [
                ['label' => 'Datacenter', 'value' => 'Chaos'],
                ['label' => 'Serveur', 'value' => 'Moogle'],
                ['label' => 'Création', 'value' => 'Mars 2026'],
            ],
            'blocks' => [
                ['title' => 'À propos de nous', 'image' => './site_img/placeholder1.png', 'paragraphs' => ['Lux Reginae est une compagnie libre chill et francophone réalisant les grosses activités tous les dimanches soirs, et les plus petites le reste de la semaine.', 'La compagnie a été formée en mars 2026.']],
                ['title' => 'Pourquoi rejoindre une Compagnie Libre ?', 'image' => './site_img/placeholder2.png', 'paragraphs' => ['Une guilde est un bon moyen de progresser et de profiter de l entraide.', 'Vous pourrez découvrir les cartes aux trésors, concours de glam et donjons sans fond en équipe.']],
                ['title' => 'Nos activités', 'image' => './site_img/placeholder3.png', 'paragraphs' => ['Nous sommes actifs presque tous les jours.', 'Nous réalisons souvent des chasses aux trésors, raids et activités plus saugrenues.']],
            ],
        ],
        'members' => [
            'kicker' => 'Roster',
            'title' => 'Nos membres',
            'intro' => 'La petite cour de Lux Reginae. Les portraits liés mènent vers les profils Lodestone disponibles.',
            'members' => [
                ['name' => "Onamo Ul'hamo", 'image' => 'https://img2.finalfantasyxiv.com/f/69e8b8f893ddb1753ff25fef922a5f36_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg', 'lodestone' => 'https://eu.finalfantasyxiv.com/lodestone/character/60328674/'],
                ['name' => 'Hise Nightmare', 'image' => 'https://img2.finalfantasyxiv.com/f/c045443f1b58c09dff8b871eb73a4212_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg', 'lodestone' => 'https://eu.finalfantasyxiv.com/lodestone/character/37520294/'],
                ['name' => 'IV Veis', 'image' => 'https://img2.finalfantasyxiv.com/f/eecaf28d94d86150fcd43484e12b3cee_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg', 'lodestone' => 'https://eu.finalfantasyxiv.com/lodestone/character/57180826/'],
                ['name' => 'Kiki Zoldik', 'image' => 'https://img2.finalfantasyxiv.com/f/4996154c9e7b40221ff5519b5064b714_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg', 'lodestone' => 'https://eu.finalfantasyxiv.com/lodestone/character/58449896/'],
                ['name' => 'Kryss Sleepymoon', 'image' => 'https://img2.finalfantasyxiv.com/f/45b31959515615d497d15ee9114f2f96_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg', 'lodestone' => 'https://eu.finalfantasyxiv.com/lodestone/character/6157317/'],
                ['name' => 'Lankhan Paddleclaw', 'image' => 'https://img2.finalfantasyxiv.com/f/7b4df2b11b181581f1870d2cdea978bf_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg', 'lodestone' => ''],
                ['name' => 'Nini Fayniafer', 'image' => 'https://img2.finalfantasyxiv.com/f/cb1bdf4fcc683373f4b41de1458aa5e5_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg', 'lodestone' => ''],
                ['name' => 'Rydia Mysidia', 'image' => 'https://img2.finalfantasyxiv.com/f/19b6b86df49ce54b44d1037691359886_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg', 'lodestone' => ''],
                ['name' => 'Sharo Arimane', 'image' => 'https://img2.finalfantasyxiv.com/f/55ec4bc2587c611cd6ba2cf762558c82_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg', 'lodestone' => ''],
                ['name' => 'Aezumin Sarera', 'image' => 'https://img2.finalfantasyxiv.com/f/7206411f536268c7441de8fb8c410d91_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg', 'lodestone' => ''],
                ['name' => 'Alma Gade', 'image' => 'https://img2.finalfantasyxiv.com/f/e7c5b0ccd2ca29b1470fad5b9d9c84cc_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg', 'lodestone' => ''],
                ['name' => 'Arcaan Rogwood', 'image' => 'https://img2.finalfantasyxiv.com/f/c05178a2bca5e987f44f98e15f2d0e19_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg', 'lodestone' => ''],
                ['name' => 'Grimgen Menchi', 'image' => 'https://img2.finalfantasyxiv.com/f/a344360ba81db5f6d7b8aa693f2913a7_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg', 'lodestone' => ''],
                ['name' => 'Kaly Sta', 'image' => 'https://img2.finalfantasyxiv.com/f/ed1f451c467886c3eccc21f95fa0b20d_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg', 'lodestone' => ''],
                ['name' => 'Maldorn Shadowspire', 'image' => 'https://img2.finalfantasyxiv.com/f/d18606d2e48acd47ac1ef904d7b0f093_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg', 'lodestone' => ''],
                ['name' => "Mini' Moi", 'image' => 'https://img2.finalfantasyxiv.com/f/e9c1243c67d88b22f1e8ba5f761e1f60_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg', 'lodestone' => ''],
                ['name' => 'Sertraline Scratou', 'image' => 'https://img2.finalfantasyxiv.com/f/42b517ef88e1e691200e6d9740b48fca_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg', 'lodestone' => ''],
            ],
        ],
        'housing' => [
            'kicker' => 'Housing',
            'title' => 'Notre maison : le Beerbrow',
            'intro' => 'Notre Q.G. à Shirogane, avec ses espaces de détente, son bar et ses recoins plus secrets.',
            'blocks' => [
                ['title' => 'Le Beerbrow', 'image' => './site_img/qg0.png', 'paragraphs' => ['Le Beerbrow est notre Q.G. Nous disposons de toutes les infrastructures nécessaires.', 'La maison se situe à Shirogane, parcelle 36, secteur 1.']],
                ['title' => 'Le Bar', 'image' => './site_img/qg1.png', 'paragraphs' => ['Notre bar fusionne l esthétique urbaine et la chaleur d un cocon.', 'Un spot parfait pour un café, un cocktail ou une soirée plus tardive.']],
                ['title' => 'La Chambre Royale', 'image' => './site_img/qg2.png', 'paragraphs' => ['Faute de château, notre reine a sa place au Beerbrow.', 'La chambre dispose de tables et fauteuils confortables.']],
                ['title' => 'Le Sourcil', 'image' => './site_img/bar0.png', 'paragraphs' => ['Ce lieu secret se mérite.', 'Le cadre mêle élégance, confort et ambiance feutrée.']],
                ['title' => 'La scène / open-mic', 'image' => './site_img/bar1.png', 'paragraphs' => ['Dans le sous-sol du bar Le Sourcil, vous trouverez une scène intimiste.', 'Humour, poésie, rap ou chanson acoustique se mélangent.']],
            ],
        ],
        'agenda' => [
            'kicker' => 'Planning',
            'title' => 'Agenda de la compagnie',
            'intro' => 'Les sorties, soirées et rendez-vous importants de Lux Reginae.',
        ],
        'join' => [
            'kicker' => 'Recrutement',
            'title' => 'Nous rejoindre',
            'intro' => 'Le recrutement est ouvert aux joueuses et joueurs francophones qui veulent avancer sans pression, avec une compagnie active et bien tenue.',
            'discordUrl' => 'https://discord.gg/luxreginae',
            'discordLabel' => 'Rejoindre le Discord',
            'steps' => [
                'Rejoins le Discord de Lux Reginae.',
                'Présente-toi dans le salon recrutement.',
                'Un membre de l’équipe te recontacte pour finaliser ton arrivée.',
            ],
            'blocks' => [
                [
                    'title' => 'Une candidature simple',
                    'image' => './site_img/placeholder5.png',
                    'paragraphs' => [
                        'Le recrutement passe par Discord afin de garder un premier contact clair et de repondre rapidement aux questions.',
                        'Tu peux venir pour discuter, poser tes disponibilités et voir si l ambiance de la compagnie te convient.',
                    ],
                ],
            ],
        ],
        'space' => ['kicker' => 'Liens utiles', 'title' => 'Espaces membres', 'intro' => 'Les outils communautaires de Lux Reginae.', 'resources' => [
            ['title' => 'Wiki', 'image' => './site_img/elixir.png', 'text' => 'Notre wiki communautaire intégré au site.', 'url' => '#wiki', 'action' => 'Ouvrir le wiki'],
            ['title' => 'Forum', 'image' => './site_img/forum.png', 'text' => 'Notre forum maison intégré au site.', 'url' => '#forum', 'action' => 'Ouvrir le forum'],
            ['title' => 'Fichiers', 'image' => './site_img/files.png', 'text' => 'Base de fichiers MIDI pour nos bardes.', 'url' => 'https://lux-reginae.duckdns.org/files/', 'action' => 'Y aller'],
            ['title' => 'Mumble', 'image' => './site_img/mumble.png', 'text' => 'Adresse : lux-reginae.duckdns.org.', 'url' => 'https://dl.mumble.info/latest/stable/client-windows-x64', 'action' => 'Télécharger'],
        ]],
        'wiki' => $importedWiki ?? [
            'kicker' => 'Wiki',
            'title' => 'Wiki Lux Reginae',
            'intro' => 'La base de connaissances de la compagnie, directement dans le site.',
            'categories' => ['Général', 'Final Fantasy XIV', 'Compagnie Libre'],
            'articles' => [
                [
                    'title' => 'Bienvenue sur le wiki',
                    'category' => 'Général',
                    'summary' => 'Point de depart pour centraliser les guides, consignes et informations utiles.',
                    'body' => [
                        'Cette section remplace le wiki externe par un espace intégré au one-page.',
                        'Les administrateurs peuvent ajouter, modifier et supprimer les articles depuis le mode Édition.',
                    ],
                ],
                [
                    'title' => 'Organisation des activités',
                    'category' => 'Compagnie Libre',
                    'summary' => 'Rappels sur les sorties, le planning et la vie de compagnie.',
                    'body' => [
                        'Les gros rendez-vous sont annonces dans le planning du site.',
                        'Les modérateurs et administrateurs peuvent ajouter les événements.',
                    ],
                ],
                [
                    'title' => 'Liens utiles',
                    'category' => 'Final Fantasy XIV',
                    'summary' => 'Regrouper ici les ressources de jeu, guides et informations recurrentes.',
                    'body' => [
                        'Ajoute les ressources importantes pour les membres : guides, macros, notes de raid, artisanat ou recolte.',
                    ],
                ],
            ],
        ],
    ];
}

function lr_load_content(): array
{
    $defaults = lr_default_content();

    if (!file_exists(LR_CONTENT_FILE)) {
        file_put_contents(LR_CONTENT_FILE, json_encode($defaults, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    $content = json_decode((string) file_get_contents(LR_CONTENT_FILE), true);

    if (!is_array($content)) {
        return $defaults;
    }

    foreach ($defaults as $pageId => $page) {
        if (!isset($content[$pageId])) {
            $content[$pageId] = $page;
        }
    }

    if (empty($content['members']['members'])) {
        $content['members']['members'] = $defaults['members']['members'];
    }

    $importedWiki = $defaults['wiki'] ?? null;
    $wikiArticles = $content['wiki']['articles'] ?? [];
    $hasPlaceholderWiki = is_array($wikiArticles)
        && count($wikiArticles) <= 3
        && (($wikiArticles[0]['title'] ?? '') === 'Bienvenue sur le wiki');
    $wikiPayload = json_encode($content['wiki'] ?? [], JSON_UNESCAPED_UNICODE);
    $hasLegacyWikiImport = is_string($wikiPayload)
        && (str_contains($wikiPayload, 'wiki-table-line')
            || str_contains($wikiPayload, '?nolink')
            || str_contains($wikiPayload, '{{')
            || (($content['wiki']['importVersion'] ?? '') !== ($importedWiki['importVersion'] ?? '')));
    if (($hasPlaceholderWiki || $hasLegacyWikiImport) && is_array($importedWiki) && count($importedWiki['articles'] ?? []) > 3) {
        $content['wiki'] = $importedWiki;
    }

    if (isset($content['join']) && is_array($content['join'])) {
        foreach (['discordUrl', 'discordLabel', 'steps'] as $joinKey) {
            if (!isset($content['join'][$joinKey])) {
                $content['join'][$joinKey] = $defaults['join'][$joinKey];
            }
        }
        $legacyJoinText = $content['join']['blocks'][0]['paragraphs'][0] ?? '';
        if (is_string($legacyJoinText) && str_contains($legacyJoinText, '#Recrutement')) {
            $content['join']['intro'] = $defaults['join']['intro'];
            $content['join']['blocks'] = $defaults['join']['blocks'];
        }
    }

    if (isset($content['space']['resources']) && is_array($content['space']['resources'])) {
        foreach ($content['space']['resources'] as &$resource) {
            $title = strtolower((string) ($resource['title'] ?? ''));
            $url = (string) ($resource['url'] ?? '');
            if ($title === 'elixir' || str_contains($url, '/wiki')) {
                $resource['title'] = 'Wiki';
                $resource['text'] = 'Notre wiki communautaire intégré au site.';
                $resource['url'] = '#wiki';
                $resource['action'] = 'Ouvrir le wiki';
            }
            if ($title === 'forum' || str_contains($url, '/forum')) {
                $resource['title'] = 'Forum';
                $resource['text'] = 'Notre forum maison intégré au site.';
                $resource['url'] = '#forum';
                $resource['action'] = 'Ouvrir le forum';
            }
        }
        unset($resource);
    }

    return $content;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    lr_json_response(['content' => lr_load_content(), 'auth' => lr_public_auth_context()]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    lr_require_role(['Administrateur', 'Modérateur', 'Utilisateur']);
    $payload = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($payload['content'] ?? null)) {
        lr_json_response(['error' => 'Invalid content'], 422);
    }
    if (lr_has_role(['Administrateur'])) {
        $content = $payload['content'];
    } else {
        if (!is_array($payload['content']['wiki'] ?? null)) {
            lr_json_response(['error' => 'Invalid wiki content'], 422);
        }
        $content = lr_load_content();
        $content['wiki'] = $payload['content']['wiki'];
    }
    file_put_contents(LR_CONTENT_FILE, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    lr_json_response(['content' => lr_load_content(), 'auth' => lr_public_auth_context()]);
}

lr_json_response(['error' => 'Method not allowed'], 405);
