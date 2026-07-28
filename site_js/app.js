const navigation = [
  { id: "home", label: "Notre Compagnie Libre", icon: "bi-stars" },
  { id: "members", label: "Nos Membres", icon: "bi-people" },
  { id: "housing", label: "Notre Maison", icon: "bi-house-heart" },
  { id: "agenda", label: "Planning", icon: "bi-calendar-event" },
  { id: "join", label: "Nous rejoindre", icon: "bi-person-plus" },
  { id: "space", label: "Espaces Membres", icon: "bi-grid-3x3-gap" },
  { id: "forum", label: "Forum", icon: "bi-chat-square-text", url: "./forum/" },
];

const members = [
  ["Onamo Ul'hamo", "https://img2.finalfantasyxiv.com/f/69e8b8f893ddb1753ff25fef922a5f36_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg", "https://eu.finalfantasyxiv.com/lodestone/character/60328674/"],
  ["Hise Nightmare", "https://img2.finalfantasyxiv.com/f/c045443f1b58c09dff8b871eb73a4212_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg", "https://eu.finalfantasyxiv.com/lodestone/character/37520294/"],
  ["IV Veis", "https://img2.finalfantasyxiv.com/f/eecaf28d94d86150fcd43484e12b3cee_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg", "https://eu.finalfantasyxiv.com/lodestone/character/57180826/"],
  ["Kiki Zoldik", "https://img2.finalfantasyxiv.com/f/4996154c9e7b40221ff5519b5064b714_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg", "https://eu.finalfantasyxiv.com/lodestone/character/58449896/"],
  ["Kryss Sleepymoon", "https://img2.finalfantasyxiv.com/f/45b31959515615d497d15ee9114f2f96_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg", "https://eu.finalfantasyxiv.com/lodestone/character/6157317/"],
  ["Lankhan Paddleclaw", "https://img2.finalfantasyxiv.com/f/7b4df2b11b181581f1870d2cdea978bf_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg"],
  ["Nini Fayniafer", "https://img2.finalfantasyxiv.com/f/cb1bdf4fcc683373f4b41de1458aa5e5_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg"],
  ["Rydia Mysidia", "https://img2.finalfantasyxiv.com/f/19b6b86df49ce54b44d1037691359886_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg"],
  ["Sharo Arimane", "https://img2.finalfantasyxiv.com/f/55ec4bc2587c611cd6ba2cf762558c82_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg"],
  ["Aezumin Sarera", "https://img2.finalfantasyxiv.com/f/7206411f536268c7441de8fb8c410d91_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg"],
  ["Alma Gade", "https://img2.finalfantasyxiv.com/f/e7c5b0ccd2ca29b1470fad5b9d9c84cc_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg"],
  ["Arcaan Rogwood", "https://img2.finalfantasyxiv.com/f/c05178a2bca5e987f44f98e15f2d0e19_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg"],
  ["Grimgen Menchi", "https://img2.finalfantasyxiv.com/f/a344360ba81db5f6d7b8aa693f2913a7_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg"],
  ["Kaly Sta", "https://img2.finalfantasyxiv.com/f/ed1f451c467886c3eccc21f95fa0b20d_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg"],
  ["Maldorn Shadowspire", "https://img2.finalfantasyxiv.com/f/d18606d2e48acd47ac1ef904d7b0f093_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg"],
  ["Mini' Moi", "https://img2.finalfantasyxiv.com/f/e9c1243c67d88b22f1e8ba5f761e1f60_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg"],
  ["Sertraline Scratou", "https://img2.finalfantasyxiv.com/f/42b517ef88e1e691200e6d9740b48fca_d7a9d5f85a29d6278ec1c7adc2c8d242fc0.jpg"],
];

const pages = {
  home: {
    kicker: "Accueil",
    title: "Une compagnie libre chill, active et francophone.",
    intro: "Lux Reginae organise ses grosses activités le dimanche soir, et de plus petites sorties le reste de la semaine.",
    render: () => `
      <div class="quick-facts">
        <div class="fact"><strong>Datacenter</strong> Chaos</div>
        <div class="fact"><strong>Serveur</strong> Moogle</div>
        <div class="fact"><strong>Création</strong> Mars 2026</div>
      </div>
      <div class="content-grid">
        ${story("À propos de nous", "./site_img/placeholder1.png", [
          "Lux Reginae est une compagnie libre chill et francophone réalisant les grosses activités tous les dimanches soirs, et les plus petites le reste de la semaine.",
          "La compagnie a été formée en mars 2026. Vous pouvez consulter notre profil officiel sur le Lodestone.",
          `<a class="btn btn-primary" href="https://eu.finalfantasyxiv.com/lodestone/freecompany/9233364398528184114/" target="_blank" rel="noreferrer">Voir le Lodestone</a>`,
        ])}
        ${story("Pourquoi rejoindre une Compagnie Libre ?", "./site_img/placeholder2.png", [
          "Une guilde est un bon moyen de progresser. Vous pouvez vous appuyer sur l'aide des joueurs plus expérimentés.",
          "Une compagnie libre repose sur l'entraide : équipements, donjons, stratégies, montures et conseils pour les combats difficiles.",
          "C'est aussi le bon moyen de découvrir les cartes aux trésors, les concours de glam, les donjons sans fond, et d'autres activités en équipe soudée.",
        ])}
        ${story("Nos activités", "./site_img/placeholder3.png", [
          "Nous sommes actifs presque tous les jours. C'est l'occasion de rencontrer d'autres membres et de profiter de l'entraide.",
          "Nous réalisons souvent des chasses aux trésors en groupe, parfois des concours de glam et quelques activités plus saugrenues.",
          `<a class="btn btn-primary" href="#join" data-route="join">Nous rejoindre</a>`,
        ])}
      </div>
    `,
  },
  members: {
    kicker: "Roster",
    title: "Nos membres",
    intro: "La petite cour de Lux Reginae. Les portraits liés mènent vers les profils Lodestone disponibles.",
    render: () => `<div class="members-grid">${members.map(memberCard).join("")}</div>`,
  },
  housing: {
    kicker: "Housing",
    title: "Notre maison : le Beerbrow",
    intro: "Notre Q.G. à Shirogane, avec ses espaces de détente, son bar et ses recoins plus secrets.",
    render: () => `
      <div class="content-grid">
        ${story("Le Beerbrow", "./site_img/qg0.png", [
          "Le Beerbrow est notre Q.G. Nous disposons de toutes les infrastructures nécessaires.",
          "La maison se situe à Shirogane, parcelle 36, secteur 1.",
          "À proximité immédiate, vous trouverez aussi un tableau des ventes, une sonnette ainsi que de très sympathiques voisins.",
        ])}
        ${story("Le Bar", "./site_img/qg1.png", [
          "Notre bar fusionne l'esthétique urbaine et la chaleur d'un cocon. Derrière une façade au style industriel affirmé se cache un refuge résolument cosy.",
          "Des canapés en cuir profond contrastent avec les lignes métalliques, le tout adouci par des cascades de plantes vertes.",
          "Un spot hybride et vivant, parfait pour un café en journée, un cocktail signature en soirée ou chanter Les Lacs du Connemara en fin de nuit.",
        ])}
        ${story("La Chambre Royale", "./site_img/qg2.png", [
          "Faute de château, notre reine a sa place au Beerbrow.",
          "Vous y trouverez l'allée avec plein de statues, que l'on appelle communément l'allée avec plein de statues, sans oublier le petit géranium.",
          "La chambre dispose aussi de tables et fauteuils confortables pour siroter votre champagne rosé dans une ambiance tamisée.",
        ])}
        ${story("Le Sourcil", "./site_img/bar0.png", [
          "Ce lieu secret se mérite. Passé la porte, le tumulte de la ville s'efface pour laisser place à une atmosphère feutrée et exclusive.",
          "À l'intérieur, le cadre mêle élégance et confort : fauteuils en velours, lumières tamisées et boiseries sombres.",
          "Au comptoir, des mixologues passionnés créent des cocktails sur mesure à partir de spiritueux rares.",
        ])}
        ${story("La scène / open-mic", "./site_img/bar1.png", [
          "Dans le sous-sol du bar Le Sourcil, vous trouverez une scène intimiste et pleine d'énergie.",
          "Artistes d'un soir et habitués se succèdent au micro dans une ambiance bienveillante et survoltée.",
          "Humour, poésie, rap ou chanson acoustique se mélangent dans un joyeux chaos créatif.",
        ])}
      </div>
    `,
  },
  agenda: {
    kicker: "Planning",
    title: "Agenda des sorties",
    intro: "Les événements de la compagnie libre, directement synchronisés avec notre calendrier.",
    render: () => `
      <iframe class="agenda-frame" title="Agenda des sorties Lux Reginae"
        src="https://calendar.google.com/calendar/embed?height=1024&wkst=1&ctz=Europe%2FParis&showPrint=0&showCalendars=0&showTabs=0&title=Agenda%20des%20Sorties&src=NGRiZWU4MjljZjMzMjJlMzEwZjE5Zjk1OThlOTUxYjAxNGIwMmIyNzA0NDZkZjVlYmVjMTdlZWQ1NmRiYTMwMEBncm91cC5jYWxlbmRhci5nb29nbGUuY29t&color=%237986cb"></iframe>
    `,
  },
  join: {
    kicker: "Recrutement",
    title: "Nous rejoindre",
    intro: "Le recrutement est actuellement ouvert. Passez nous voir, dites bonjour, et on discute simplement.",
    render: () => `
      <div class="content-grid">
        <article class="info-card p-4">
          <p class="pill"><i class="bi bi-check-circle-fill"></i> Recrutement ouvert</p>
          <h2>La procédure</h2>
          <ol class="join-steps">
            <li>Rejoindre notre serveur Discord.</li>
            <li>Faire un petit message dans #Recrutement pour vous faire connaître.</li>
            <li>Un membre prendra contact avec vous pour une courte discussion.</li>
            <li>Bienvenue parmi nous. Ou pas. Mais probablement bienvenue.</li>
          </ol>
          <div class="d-flex flex-wrap gap-2 mt-3">
            <a class="btn btn-primary" href="https://discord.gg/GqRbsTNQh2" target="_blank" rel="noreferrer"><i class="bi bi-discord"></i> Notre Discord</a>
            <a class="btn btn-outline-primary" href="https://eu.finalfantasyxiv.com/lodestone/freecompany/9233364398528184114/" target="_blank" rel="noreferrer">Forum de CL sur Lodestone</a>
          </div>
        </article>
        ${story("Ce que l'on demande", "./site_img/placeholder6.png", [
          "Il y a très peu d'obligations dans notre communauté. Une seule est en vigueur : la politesse. On demande de dire bonjour dans le canal de guilde lors de la connexion.",
          "Notre Discord est le canal privilégié de communication interne pour annoncer les événements à venir.",
          "Aucun événement n'est obligatoire. Quand vous participez à une activité organisée par la compagnie libre, nous demandons de rejoindre le vocal Discord, même sans parler.",
        ])}
        ${story("Notre charte", "./site_img/placeholder7.png", [
          "Nous fonctionnons à la manière d'une table ronde.",
          "Pas de grande charte gravée dans le marbre : du respect, de l'entraide et une bonne dose de bonne humeur.",
        ])}
      </div>
    `,
  },
  space: {
    kicker: "Liens utiles",
    title: "Espaces membres",
    intro: "Les outils communautaires de Lux Reginae : wiki, forum, fichiers et vocal.",
    render: () => `
      <div class="resources-grid">
        ${resourceCard("Elixir", "./site_img/elixir.png", "Elixir est notre wiki communautaire. Vous y trouverez plein d'infos utiles sur FFXIV.", "https://lux-reginae.duckdns.org/wiki/", "Y aller")}
        ${resourceCard("Forum", "./site_img/forum.png", "Notre forum maison pour les annonces, les sorties et les discussions de la compagnie libre.", "./forum/", "Ouvrir le forum")}
        ${resourceCard("Fichiers", "./site_img/files.png", "Grosse base de données de fichiers MIDI pour nos bardes.", "https://lux-reginae.duckdns.org/files/", "Y aller")}
        ${resourceCard("Mumble", "./site_img/mumble.png", "Adresse : lux-reginae.duckdns.org. Mot de passe : Lux-Reginae.", "https://dl.mumble.info/latest/stable/client-windows-x64", "Télécharger Mumble")}
      </div>
    `,
  },
};

const app = document.querySelector("#app");
const nav = document.querySelector("#main-navigation");

function initNavigation() {
  nav.innerHTML = navigation.map((item) => `
    <li class="nav-item">
      <a class="nav-link" href="${item.url || `#${item.id}`}" ${item.url ? "" : `data-route="${item.id}"`}>
        <i class="bi ${item.icon}"></i>
        ${item.label}
      </a>
    </li>
  `).join("");
}

function renderPage(routeId = "home", shouldScroll = true) {
  const route = pages[routeId] ? routeId : "home";
  const page = pages[route];

  app.innerHTML = `
    <section class="container" data-page="${route}">
      <div class="section-head">
        <div class="section-kicker">${page.kicker}</div>
        <h2 class="section-title">${page.title}</h2>
        <p class="section-intro">${page.intro}</p>
      </div>
      ${page.render()}
    </section>
  `;

  document.querySelectorAll("[data-route]").forEach((link) => {
    link.classList.toggle("active", link.dataset.route === route);
  });

  if (shouldScroll) {
    const top = route === "home" ? 0 : app.offsetTop - 72;
    app.focus({ preventScroll: true });
    window.scrollTo({ top, behavior: "smooth" });
  }
}

function getRouteFromHash() {
  return window.location.hash.replace("#", "") || "home";
}

function story(title, image, paragraphs) {
  return `
    <article class="story-block">
      <img class="story-image" src="${image}" alt="${title}">
      <div class="story-copy">
        <h2>${title}</h2>
        ${paragraphs.map((paragraph) => paragraph.startsWith("<") ? paragraph : `<p>${paragraph}</p>`).join("")}
      </div>
    </article>
  `;
}

function memberCard(member) {
  const [name, image, url] = member;
  const content = `<img src="${image}" alt="${name}"><span>${name}</span>`;

  if (!url) {
    return `<div class="member-card">${content}</div>`;
  }

  return `<a class="member-card" href="${url}" target="_blank" rel="noreferrer">${content}</a>`;
}

function resourceCard(title, image, text, url, action) {
  return `
    <article class="resource-card">
      <img src="${image}" alt="${title}">
      <div class="resource-card__body">
        <h2>${title}</h2>
        <p>${text}</p>
        <a class="btn btn-primary" href="${url}" target="_blank" rel="noreferrer">${action}</a>
      </div>
    </article>
  `;
}

document.addEventListener("click", (event) => {
  const routeLink = event.target.closest("[data-route]");

  if (!routeLink) {
    return;
  }

  const route = routeLink.dataset.route;

  if (!pages[route]) {
    return;
  }

  event.preventDefault();
  if (getRouteFromHash() === route) {
    renderPage(route);
  } else {
    window.location.hash = route;
  }

  const navbar = document.querySelector("#navbarNav");
  const collapse = window.bootstrap ? bootstrap.Collapse.getInstance(navbar) : null;

  if (collapse) {
    collapse.hide();
  }
});

window.addEventListener("hashchange", () => renderPage(getRouteFromHash()));

initNavigation();
renderPage(getRouteFromHash(), false);
