const auth = window.LR_AUTH || { user: null, permissions: {} };
const navigation = [
  { id: "home", label: "Compagnie", icon: "bi-stars" },
  { id: "members", label: "Membres", icon: "bi-people" },
  { id: "housing", label: "Maison", icon: "bi-house-heart" },
  { id: "agenda", label: "Planning", icon: "bi-calendar-event" },
  { id: "join", label: "Recrutement", icon: "bi-person-plus" },
  { id: "space", label: "Espaces", icon: "bi-grid-3x3-gap" },
  { id: "wiki", label: "Wiki", icon: "bi-book" },
  { id: "forum", label: "Forum", icon: "bi-chat-square-text" },
];

let siteContent = {};
let agendaEvents = [];
let currentRoute = "home";
let currentWikiCategory = "Tous";
let currentWikiArticle = 0;
let forumState = { categories: [], topics: [], replies: [], csrf: "" };
let currentForumCategory = "";
let currentForumTopic = null;
let editing = false;

const app = document.querySelector("#app");
const nav = document.querySelector("#main-navigation");

init();

async function init() {
  initNavigation();
  await loadContent();
  renderPage(getRouteFromHash(), false);
  document.querySelector("#edit-toggle")?.addEventListener("click", toggleEditing);
}

function initNavigation() {
  nav.innerHTML = navigation.map((item) => `
    <li class="nav-item"><a class="nav-link" href="${item.url || `#${item.id}`}" ${item.url ? "" : `data-route="${item.id}"`}><i class="bi ${item.icon}"></i> ${item.label}</a></li>
  `).join("");
}

async function loadContent() {
  const response = await fetch("./site_backend/content.php", { headers: { Accept: "application/json" } });
  const data = await response.json();
  siteContent = data.content;
  ensureRuntimePages();
}

function ensureRuntimePages() {
  siteContent.agenda = siteContent.agenda || {
    kicker: "Planning",
    title: "Agenda de la compagnie",
    intro: "Les sorties, soirées et rendez-vous importants de Lux Reginae.",
  };
  siteContent.wiki = siteContent.wiki || {
    kicker: "Wiki",
    title: "Wiki Lux Reginae",
    intro: "La base de connaissances de la compagnie, directement dans le site.",
    categories: ["Général"],
    articles: [],
  };
  siteContent.wiki.categories = siteContent.wiki.categories || ["Général"];
  siteContent.wiki.articles = siteContent.wiki.articles || [];
  siteContent.forum = siteContent.forum || {
    kicker: "Forum",
    title: "Forum Lux Reginae",
    intro: "Les discussions de la compagnie, directement intégrées au site.",
  };
}

function isSpecialRoute(routeId) {
  return ["agenda", "wiki", "forum"].includes(routeId);
}

function getPage(routeId) {
  return siteContent[routeId] || siteContent.home || { kicker: "", title: "", intro: "", blocks: [] };
}

function canEditCurrentRoute() {
  return Boolean(auth.permissions.editSite || (auth.permissions.editWiki && currentRoute === "wiki"));
}

function updateEditToggle() {
  const button = document.querySelector("#edit-toggle");
  if (!button) return;
  const canEdit = canEditCurrentRoute();
  button.hidden = !canEdit;
  button.innerHTML = editing ? `<i class="bi bi-x-lg"></i> Quitter` : `<i class="bi bi-pencil-square"></i> Édition`;
}

function renderPage(routeId = "home", shouldScroll = true) {
  currentRoute = (siteContent[routeId] || isSpecialRoute(routeId)) ? routeId : "home";
  if (editing && !canEditCurrentRoute()) editing = false;
  const page = getPage(currentRoute);
  app.classList.toggle("editing", editing);
  app.innerHTML = `
    <section class="container page-shell page-${currentRoute}" data-page="${currentRoute}">
      ${editBar()}
      <div class="section-head">
        ${editableText("kicker", page.kicker, "section-kicker")}
        ${editableText("title", page.title, "section-title", "h2")}
        ${editableText("intro", page.intro, "section-intro", "p")}
      </div>
      ${renderRoute(currentRoute, page)}
    </section>`;
  document.querySelectorAll("[data-route]").forEach((link) => link.classList.toggle("active", link.dataset.route === currentRoute));
  updateEditToggle();
  if (currentRoute === "agenda") initAgenda();
  if (currentRoute === "forum") initForum();
  if (shouldScroll) window.scrollTo({ top: currentRoute === "home" ? 0 : app.offsetTop - 72, behavior: "smooth" });
}

function renderRoute(route, page) {
  if (route === "agenda") return renderAgenda();
  if (route === "members") return renderMembers(page);
  if (route === "wiki") return renderWiki(page);
  if (route === "forum") return renderForumShell();
  if (route === "join") return renderJoin(page);
  if (route === "space") return `<div class="resources-grid">${(page.resources || []).map((item, index) => resourceCard(item, index)).join("")}</div>${addResourceButton()}`;
  const facts = page.facts ? `<div class="quick-facts">${page.facts.map((fact) => `<div class="fact"><strong>${escapeHtml(fact.label)}</strong>${escapeHtml(fact.value)}</div>`).join("")}</div>` : "";
  return `${facts}<div class="content-grid">${(page.blocks || []).map((block, index) => story(block, index)).join("")}</div>${addBlockButton()}`;
}

function editBar() {
  if (!editing || !canEditCurrentRoute()) return "";
  const label = auth.permissions.editSite ? "Enregistrer le site" : "Enregistrer le wiki";
  return `<div class="edit-bar"><strong>Mode édition</strong><button class="btn btn-primary" type="button" data-edit-action="save">${label}</button><button class="btn btn-outline-primary" type="button" data-edit-action="cancel">Quitter</button></div>`;
}

function editableText(field, value, className, tag = "div") {
  if (!editing || !auth.permissions.editSite) return `<${tag} class="${className}">${escapeHtml(value || "")}</${tag}>`;
  return `<label class="w-100">${field}<input class="editable-field" data-page-field="${field}" value="${escapeHtml(value || "")}"></label>`;
}

function story(block, index) {
  return `<article class="story-block">
    <img class="story-image" src="${escapeHtml(block.image || "./site_img/placeholder1.png")}" alt="${escapeHtml(block.title || "")}">
    <div class="story-copy"><h2>${escapeHtml(block.title || "")}</h2>${(block.paragraphs || []).map((p) => `<p>${escapeHtml(p)}</p>`).join("")}${blockEditor(index, block)}</div>
  </article>`;
}

function blockEditor(index, block) {
  if (!editing) return "";
  return `<div class="edit-panel" data-block-index="${index}">
    <label>Titre<input class="editable-field" data-block-field="title" value="${escapeHtml(block.title || "")}" placeholder="Titre"></label>
    <input type="hidden" data-block-field="image" value="${escapeHtml(block.image || "")}">
    <div class="upload-control" data-upload-control>
      <div class="upload-preview">
        <img src="${escapeHtml(block.image || "./site_img/placeholder1.png")}" alt="">
      </div>
      <label class="btn btn-outline-primary upload-button">
        Choisir une image
        <input type="file" accept="image/png,image/jpeg,image/webp" data-upload-image="${index}">
      </label>
      <small data-upload-status>${escapeHtml(block.image || "Aucune image envoyée")}</small>
    </div>
    <label>Texte<textarea class="editable-area" data-block-field="paragraphs">${escapeHtml((block.paragraphs || []).join("\n"))}</textarea></label>
    <button class="btn btn-outline-danger btn-sm" type="button" data-edit-action="remove-block" data-index="${index}">Supprimer ce bloc</button>
  </div>`;
}

function resourceCard(item, index) {
  const url = item.url || "#";
  const route = url.startsWith("#") ? url.slice(1) : "";
  return `<article class="resource-card"><img src="${escapeHtml(item.image)}" alt="${escapeHtml(item.title)}"><div class="resource-card__body"><h2>${escapeHtml(item.title)}</h2><p>${escapeHtml(item.text)}</p><a class="btn btn-primary" href="${escapeHtml(url)}" ${route ? `data-route="${escapeHtml(route)}"` : ""}>${escapeHtml(item.action)}</a>${editing ? `<div class="edit-panel"><input class="editable-field" data-resource-index="${index}" data-resource-field="title" value="${escapeHtml(item.title)}"><input class="editable-field" data-resource-index="${index}" data-resource-field="image" value="${escapeHtml(item.image)}"><textarea class="editable-area" data-resource-index="${index}" data-resource-field="text">${escapeHtml(item.text)}</textarea><input class="editable-field" data-resource-index="${index}" data-resource-field="url" value="${escapeHtml(item.url)}"><input class="editable-field" data-resource-index="${index}" data-resource-field="action" value="${escapeHtml(item.action)}"></div>` : ""}</div></article>`;
}

function renderMembers(page) {
  const members = page.members || [];
  const cards = members.map((member, index) => memberCard(member, index)).join("");
  return `
    <div class="members-grid">${cards || `<div class="agenda-empty">Aucun membre pour le moment.</div>`}</div>
    ${editing ? `<button class="btn btn-primary mt-3" type="button" data-edit-action="add-member">Ajouter un membre</button>` : ""}
  `;
}

function memberCard(member, index) {
  const image = member.image || "./site_img/placeholder4.png";
  const name = member.name || "Nouveau membre";
  const content = `<img src="${escapeHtml(image)}" alt="${escapeHtml(name)}"><span>${escapeHtml(name)}</span>`;
  const card = member.lodestone
    ? `<a class="member-card" href="${escapeHtml(member.lodestone)}" target="_blank" rel="noreferrer">${content}</a>`
    : `<div class="member-card">${content}</div>`;

  if (!editing) {
    return card;
  }

  return `
    <article class="member-card editable-member">
      <img src="${escapeHtml(image)}" alt="${escapeHtml(name)}">
      <div class="member-edit" data-member-index="${index}">
        <label>Nom du personnage<input class="editable-field" data-member-field="name" value="${escapeHtml(name)}" placeholder="Nom du personnage"></label>
        <label>Lien profil Lodestone<input class="editable-field" data-member-field="lodestone" value="${escapeHtml(member.lodestone || "")}" placeholder="Lien profil Lodestone"></label>
        <input type="hidden" data-member-field="image" value="${escapeHtml(image)}">
        <div class="upload-control compact" data-upload-control>
          <label class="btn btn-outline-primary upload-button">
            Choisir une image
            <input type="file" accept="image/png,image/jpeg,image/webp" data-upload-member-image="${index}">
          </label>
          <small data-upload-status>${escapeHtml(image || "Aucune image envoyée")}</small>
        </div>
        <button class="btn btn-outline-danger btn-sm" type="button" data-edit-action="remove-member" data-index="${index}">Supprimer</button>
      </div>
    </article>
  `;
}

function renderWiki(page) {
  const articles = page.articles || [];
  const knownCategories = [...new Set([...(page.categories || []), ...articles.map((article) => article.category).filter(Boolean)])];
  page.categories = knownCategories;
  const categories = ["Tous", ...knownCategories];
  const categoryCounts = articles.reduce((counts, article) => {
    const key = article.category || "Général";
    counts[key] = (counts[key] || 0) + 1;
    return counts;
  }, { Tous: articles.length });
  const filtered = currentWikiCategory === "Tous"
    ? articles
    : articles.filter((article) => article.category === currentWikiCategory);
  const selectedArticle = articles[currentWikiArticle];
  const activeArticle = filtered.includes(selectedArticle) ? selectedArticle : filtered[0];
  const activeIndex = activeArticle ? articles.indexOf(activeArticle) : 0;
  if (activeArticle && activeIndex !== currentWikiArticle) currentWikiArticle = activeIndex;

  return `
    <div class="wiki-command">
      <div>
        <span class="wiki-category">Base de connaissances</span>
        <h2>${escapeHtml(page.title || "Wiki Lux Reginae")}</h2>
        <p>${escapeHtml(page.intro || "Guides, outils et notes de compagnie.")}</p>
      </div>
      <div class="wiki-command__stats">
        <span><strong>${articles.length}</strong> articles</span>
        <span><strong>${knownCategories.length}</strong> catégories</span>
        <span><strong>${filtered.length}</strong> affichés</span>
      </div>
    </div>
    <div class="wiki-shell">
      <aside class="wiki-sidebar">
        <div class="wiki-sidebar-head">
          <strong>Explorer</strong>
          ${editing ? `<button class="btn btn-primary btn-sm" type="button" data-edit-action="add-wiki-article"><i class="bi bi-plus-lg"></i> Article</button>` : `<span>${filtered.length}/${articles.length}</span>`}
        </div>
        <div class="wiki-search">
          <i class="bi bi-search"></i>
          <input type="search" placeholder="Chercher dans le wiki" data-wiki-search>
        </div>
        <div class="wiki-categories">
          ${categories.map((category) => `<button class="wiki-chip ${category === currentWikiCategory ? "active" : ""}" type="button" data-wiki-category="${escapeHtml(category)}">${escapeHtml(category)} <span>${Number(categoryCounts[category] || 0)}</span></button>`).join("")}
        </div>
        <div class="wiki-list" data-wiki-list>
          ${renderWikiList(filtered, articles)}
        </div>
      </aside>
      <section class="wiki-article">
        ${activeArticle ? renderWikiArticle(activeArticle, activeIndex, page.categories || [], articles) : `<div class="agenda-empty">Aucun article dans le wiki.</div>`}
      </section>
    </div>
  `;
}

function renderWikiList(filtered, articles) {
  return filtered.map((article) => {
    const index = articles.indexOf(article);
    const isActive = index === currentWikiArticle;
    const summary = getWikiDisplaySummary(article, 135);
    return `<button class="wiki-list-item ${isActive ? "active" : ""}" type="button" data-wiki-article="${index}" ${isActive ? `aria-current="true"` : ""}>
      <span>${escapeHtml(article.category || "Général")}</span>
      <strong>${escapeHtml(article.title || "Article sans titre")}</strong>
      <small>${escapeHtml(summary)}</small>
    </button>`;
  }).join("") || `<div class="agenda-empty">Aucun article trouvé.</div>`;
}

function renderWikiArticle(article, index, categories, articles = []) {
  if (editing) {
    const categoryList = [...new Set([...(categories || []), article.category].filter(Boolean))];
    const categoryOptions = categoryList.map((category) => `<option ${category === article.category ? "selected" : ""}>${escapeHtml(category)}</option>`).join("");
    const contentField = "bodyHtml";
    const contentValue = article.bodyHtml || (article.body || []).map((paragraph) => `<p>${escapeHtml(paragraph)}</p>`).join("");
    return `
      <div class="edit-panel wiki-editor" data-wiki-index="${index}">
        <div class="wiki-editor-head">
          <div>
            <span class="wiki-category">Édition wiki</span>
            <h2>${escapeHtml(article.title || "Article sans titre")}</h2>
          </div>
          <div class="wiki-editor-actions">
            <button class="btn btn-outline-primary btn-sm" type="button" data-edit-action="add-wiki-category"><i class="bi bi-folder-plus"></i> Catégorie</button>
            <button class="btn btn-outline-danger btn-sm" type="button" data-edit-action="remove-wiki-article" data-index="${index}">Supprimer</button>
          </div>
        </div>
        <div class="wiki-editor-grid">
          <section class="wiki-editor-main">
            <div class="wiki-editor-fields">
              <label>Titre<input class="editable-field" data-wiki-field="title" value="${escapeHtml(article.title || "")}"></label>
              <label>Catégorie<select class="editable-field" data-wiki-field="category">${categoryOptions}</select></label>
              <div class="wiki-cover-field field-span">
                <label>Image de couverture<input class="editable-field" data-wiki-field="image" value="${escapeHtml(article.image || "")}" placeholder="./site_img/wiki/image.png"></label>
                <label class="btn btn-outline-primary upload-button">
                  <i class="bi bi-upload"></i> Envoyer une image
                  <input type="file" accept="image/png,image/jpeg,image/webp" data-upload-wiki-image="${index}">
                </label>
                <small data-upload-status>${escapeHtml(article.image || "Aucune image envoyée")}</small>
              </div>
            </div>
            <div class="wiki-editor-content">
              <div class="wiki-editor-content-head">
                <strong>Contenu de l'article</strong>
                <span>Tu écris directement dans le rendu final.</span>
              </div>
              <span class="wiki-format-toolbar" aria-label="Outils de mise en forme">
                <button type="button" data-wiki-format="h2"><i class="bi bi-type-h2"></i> Grand titre</button>
                <button type="button" data-wiki-format="h3"><i class="bi bi-type-h3"></i> Sous-titre</button>
                <button type="button" data-wiki-format="p"><i class="bi bi-text-paragraph"></i> Texte</button>
                <button type="button" data-wiki-format="bold"><i class="bi bi-type-bold"></i> Gras</button>
                <button type="button" data-wiki-format="list"><i class="bi bi-list-ul"></i> Liste</button>
                <button type="button" data-wiki-format="quote"><i class="bi bi-quote"></i> Note</button>
                <button type="button" data-wiki-format="link"><i class="bi bi-link-45deg"></i> Lien</button>
                <button type="button" data-wiki-format="table"><i class="bi bi-table"></i> Tableau</button>
                <button type="button" data-wiki-format="image"><i class="bi bi-image"></i> Image</button>
                <button type="button" data-wiki-format="hr"><i class="bi bi-dash-lg"></i> Séparer</button>
              </span>
              <div class="wiki-rich-editor wiki-body" contenteditable="true" spellcheck="true" data-wiki-rich-editor>${renderWikiBody(article)}</div>
              <textarea class="wiki-content-storage" data-wiki-field="${contentField}" hidden>${escapeHtml(contentValue)}</textarea>
            </div>
            <div class="wiki-editor-help">
              <strong>Conseil de structure</strong>
              <span>Un article lisible commence par un grand titre, une courte intro, puis des sections courtes.</span>
              <span>Pour une image dans l'article, envoie-la d'abord avec l'outil d'image puis colle le chemin généré.</span>
            </div>
          </section>
          <aside class="wiki-editor-side">
            <strong>Fiche article</strong>
            ${article.image ? `<img data-wiki-cover-preview src="${escapeHtml(article.image)}" alt="">` : `<div class="wiki-cover-placeholder" data-wiki-cover-preview>Aucune image</div>`}
            <dl>
              <div><dt>Catégorie</dt><dd>${escapeHtml(article.category || "Général")}</dd></div>
              <div><dt>Sections</dt><dd>${getWikiHeadings(article).length}</dd></div>
              <div><dt>Images</dt><dd>${countWikiMedia(article)}</dd></div>
            </dl>
          </aside>
        </div>
      </div>
    `;
  }

  const isDirectory = article.id === "sidebar";

  return `
    <article class="wiki-article-layout${isDirectory ? " wiki-article-layout--directory" : ""}">
      <div class="wiki-reading">
        <span class="wiki-category">${escapeHtml(article.category || "Général")}</span>
        <h2>${escapeHtml(article.title || "Article sans titre")}</h2>
        ${article.image ? `<img class="wiki-article-cover" src="${escapeHtml(article.image)}" alt="${escapeHtml(article.title || "Article du wiki")}">` : ""}
        <div class="wiki-body">${isDirectory ? renderWikiDirectory(article, articles) : renderWikiBody(article, { skipArticleHeader: true })}</div>
      </div>
      ${isDirectory ? "" : renderWikiToc(article)}
    </article>
  `;
}
function renderWikiDirectory(article, articles) {
  const template = document.createElement("template");
  template.innerHTML = renderWikiBody(article);
  const groups = [];
  let current = null;
  [...template.content.children].forEach((node) => {
    if (/^H[2-3]$/.test(node.tagName)) {
      current = { title: node.textContent.trim(), links: [] };
      groups.push(current);
      return;
    }
    if (!current || node.tagName !== "UL") return;
    node.querySelectorAll("[data-wiki-id]").forEach((link) => {
      const id = normalizeWikiId(link.dataset.wikiId);
      const target = articles.find((item) => normalizeWikiId(item.id || item.title) === id);
      current.links.push({
        id,
        label: link.textContent.trim(),
        category: target?.category || "",
        summary: getWikiDisplaySummary(target || {}, 90),
      });
    });
  });
  return `<div class="wiki-directory">
    ${groups.map((group) => `<section class="wiki-directory-section">
      <h3>${escapeHtml(group.title)}</h3>
      <div class="wiki-directory-links">
        ${group.links.map((link) => `<button class="wiki-directory-link" type="button" data-wiki-id="${escapeHtml(link.id)}">
          <strong>${escapeHtml(link.label)}</strong>
          ${link.summary ? `<span>${escapeHtml(link.summary)}</span>` : ""}
        </button>`).join("") || `<p>Aucun lien dans cette section.</p>`}
      </div>
    </section>`).join("")}
  </div>`;
}

function getWikiHeadings(article, options = {}) {
  if (!article.bodyHtml) return [];
  const template = document.createElement("template");
  template.innerHTML = article.bodyHtml;
  if (options.skipArticleHeader) removeDuplicateWikiIntro(template.content, article);
  return [...template.content.querySelectorAll("h2, h3")]
    .map((heading, index) => ({
      id: `wiki-section-${normalizeWikiId(article.id || article.title)}-${index}`,
      level: heading.tagName.toLowerCase(),
      text: heading.textContent.trim(),
    }))
    .filter((heading) => heading.text);
}

function countWikiMedia(article) {
  if (!article.bodyHtml) return article.image ? 1 : 0;
  const template = document.createElement("template");
  template.innerHTML = article.bodyHtml;
  return template.content.querySelectorAll("img, video").length + (article.image ? 1 : 0);
}

function getWikiDisplaySummary(article, limit = 0) {
  const importedSummary = cleanWikiSummaryText(article.summary || "", article.title || "");
  const intro = extractWikiIntro(article);
  const shouldReplace = !importedSummary || /[.]{3}$/.test(importedSummary) || startsWithSameWords(importedSummary, article.title || "");
  const text = shouldReplace ? (intro || importedSummary) : importedSummary;
  if (!limit || text.length <= limit) return text;
  const clipped = text.slice(0, limit).replace(/\s+\S*$/, "").trim();
  return clipped || text.slice(0, limit).trim();
}

function extractWikiIntro(article) {
  if (!article.bodyHtml) return "";
  const template = document.createElement("template");
  template.innerHTML = article.bodyHtml;
  const candidates = [...template.content.querySelectorAll("p:not(.wiki-table-line), li")]
    .map((node) => cleanWikiSummaryText(node.textContent || "", article.title || ""))
    .filter((text) => text && !/^https?:\/\//i.test(text) && !/^[|^]/.test(text));
  return candidates[0] || "";
}

function cleanWikiSummaryText(value, title) {
  let text = String(value || "").replace(/\s+/g, " ").replace(/^-+\s*/, "").trim();
  text = text.replace(/\s*[.]{3}$/, "").trim();
  if (title) {
    const escapedTitle = title.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    text = text.replace(new RegExp(`^${escapedTitle}\\s*-*\\s*`, "i"), "").trim();
  }
  return text;
}

function startsWithSameWords(value, title) {
  const left = normalizeText(value).split(" ").slice(0, 3).join(" ");
  const right = normalizeText(title).split(" ").slice(0, 3).join(" ");
  return Boolean(left && right && left === right);
}

function makeWikiArticleId(title, articles = []) {
  const base = normalizeText(title || "nouvel’article").replace(/\s+/g, "-") || "nouvel-article";
  let candidate = base;
  let suffix = 2;
  const existing = new Set(articles.map((article) => normalizeWikiId(article.id || article.title)));
  while (existing.has(candidate)) {
    candidate = `${base}-${suffix}`;
    suffix += 1;
  }
  return candidate;
}

function renderWikiBody(article, options = {}) {
  if (!article.bodyHtml) {
    return (article.body || []).map((paragraph) => `<p>${escapeHtml(paragraph)}</p>`).join("");
  }
  const template = document.createElement("template");
  template.innerHTML = convertWikiTableLines(article.bodyHtml);
  if (options.skipArticleHeader) removeDuplicateWikiIntro(template.content, article);
  template.content.querySelectorAll("h2, h3").forEach((heading, index) => {
    heading.id = `wiki-section-${normalizeWikiId(article.id || article.title)}-${index}`;
  });
  enhanceWikiTables(template.content);
  autoLinkWikiUrls(template.content);
  return template.innerHTML;
}

function removeDuplicateWikiIntro(root, article) {
  const title = normalizeText(article.title || "");
  const firstHeading = root.querySelector("h2");
  if (firstHeading && title && normalizeText(firstHeading.textContent || "") === title) {
    let cursor = firstHeading.nextElementSibling;
    firstHeading.remove();
    while (cursor?.tagName === "HR") {
      const next = cursor.nextElementSibling;
      cursor.remove();
      cursor = next;
    }
  }

  const cover = normalizeAssetPath(article.image || "");
  if (!cover) return;
  const firstElement = [...root.children].find((node) => node.tagName !== "HR");
  const image = firstElement?.matches?.("figure.wiki-media") ? firstElement.querySelector("img") : null;
  if (image && normalizeAssetPath(image.getAttribute("src") || "") === cover) {
    firstElement.remove();
  }
}

function normalizeAssetPath(value) {
  return String(value || "").replace(/\\/g, "/").replace(/^\.\//, "").trim().toLowerCase();
}

function convertWikiTableLines(html) {
  return String(html || "").replace(/(?:<p class="wiki-table-line">[\s\S]*?<\/p>\s*){2,}/g, (group) => {
    const rows = [...group.matchAll(/<p class="wiki-table-line">([\s\S]*?)<\/p>/g)].map((match) => match[1]);
    const renderedRows = rows.map((row) => {
      const isHeader = row.trim().startsWith("^") || row.includes("<strong>");
      const cells = splitWikiRawTableLine(row);
      if (!cells.length) return "";
      return `<tr>${cells.map((cell) => `<${isHeader ? "th" : "td"}>${cell.trim()}</${isHeader ? "th" : "td"}>`).join("")}</tr>`;
    }).join("");
    return renderedRows ? `<div class="wiki-table-wrap"><table class="wiki-table">${renderedRows}</table></div>` : group;
  });
}

function splitWikiRawTableLine(row) {
  const delimiter = row.trim().startsWith("^") ? "^" : "|";
  return row
    .trim()
    .replace(/^[|^]/, "")
    .replace(/[|^]$/, "")
    .split(delimiter)
    .map((cell) => cell.trim())
    .filter(Boolean);
}

function enhanceWikiTables(root) {
  const lines = [...root.querySelectorAll("p.wiki-table-line")];
  let index = 0;
  while (index < lines.length) {
    const group = [];
    let cursor = lines[index];
    while (cursor?.matches?.("p.wiki-table-line")) {
      group.push(cursor);
      cursor = cursor.nextElementSibling;
    }
    if (group.length >= 2) replaceWikiTableGroup(group);
    index += Math.max(group.length, 1);
  }
}

function replaceWikiTableGroup(group) {
  const wrapper = document.createElement("div");
  wrapper.className = "wiki-table-wrap";
  const table = document.createElement("table");
  table.className = "wiki-table";
  group.forEach((line) => {
    const tr = document.createElement("tr");
    const isHeader = line.textContent.trim().startsWith("^") || line.querySelector("strong");
    splitWikiTableLine(line).forEach((cellHtml) => {
      const cell = document.createElement(isHeader ? "th" : "td");
      cell.innerHTML = cellHtml.trim();
      tr.append(cell);
    });
    if (tr.children.length) table.append(tr);
  });
  wrapper.append(table);
  group[0].replaceWith(wrapper);
  group.slice(1).forEach((line) => line.remove());
}

function splitWikiTableLine(line) {
  const delimiter = line.textContent.trim().startsWith("^") ? "^" : "|";
  return line.innerHTML
    .trim()
    .replace(/^[|^]/, "")
    .replace(/[|^]$/, "")
    .split(delimiter)
    .filter((cell) => cell.trim());
}

function autoLinkWikiUrls(root) {
  const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
    acceptNode(node) {
      if (!node.nodeValue.match(/https?:\/\/\S+/i)) return NodeFilter.FILTER_REJECT;
      if (node.parentElement?.closest("a, button, script, style")) return NodeFilter.FILTER_REJECT;
      return NodeFilter.FILTER_ACCEPT;
    },
  });
  const nodes = [];
  while (walker.nextNode()) nodes.push(walker.currentNode);
  nodes.forEach((node) => {
    const fragment = document.createDocumentFragment();
    node.nodeValue.split(/(https?:\/\/[^\s<]+)/gi).forEach((part) => {
      if (/^https?:\/\//i.test(part)) {
        const link = document.createElement("a");
        const url = part.replace(/[),.;]+$/, "");
        const suffix = part.slice(url.length);
        link.href = url;
        link.textContent = url;
        link.target = "_blank";
        link.rel = "noreferrer";
        fragment.append(link);
        if (suffix) fragment.append(document.createTextNode(suffix));
      } else {
        fragment.append(document.createTextNode(part));
      }
    });
    node.replaceWith(fragment);
  });
}

function renderWikiToc(article) {
  const headings = getWikiHeadings(article, { skipArticleHeader: true }).slice(0, 14);
  if (headings.length < 3) return "";
  return `<nav class="wiki-toc" aria-label="Sommaire de l'article">
    <strong>Dans cet article</strong>
    ${headings.map((heading) => `<button class="${heading.level === "h3" ? "sub" : ""}" type="button" data-wiki-toc="${escapeHtml(heading.id)}">${escapeHtml(heading.text)}</button>`).join("")}
  </nav>`;
}

function renderJoin(page) {
  const steps = page.steps || [];
  const blocks = page.blocks || [];
  return `
    <div class="join-panel">
      <div class="join-main">
        <span class="pill"><i class="bi bi-check2-circle"></i> Recrutement ouvert</span>
        <h2>${escapeHtml(blocks[0]?.title || "Une candidature simple")}</h2>
        ${(blocks[0]?.paragraphs || []).map((paragraph) => `<p>${escapeHtml(paragraph)}</p>`).join("")}
        <div class="join-actions">
          <a class="btn btn-primary btn-lg" href="${escapeHtml(page.discordUrl || "#")}" target="_blank" rel="noreferrer">
            <i class="bi bi-discord"></i> ${escapeHtml(page.discordLabel || "Rejoindre le Discord")}
          </a>
          <a class="btn btn-outline-primary btn-lg" href="#forum" data-route="forum">
            <i class="bi bi-chat-square-text"></i> Voir le forum
          </a>
        </div>
        ${blocks[0] ? blockEditor(0, blocks[0]) : ""}
        ${joinEditor(page)}
      </div>
      <aside class="join-card">
        <img class="join-image" src="${escapeHtml(blocks[0]?.image || "./site_img/placeholder5.png")}" alt="">
        <h3>Comment ca se passe ?</h3>
        <ol class="join-steps">
          ${steps.map((step) => `<li>${escapeHtml(step)}</li>`).join("")}
        </ol>
      </aside>
    </div>
    ${blocks.slice(1).length ? `<div class="content-grid mt-4">${blocks.slice(1).map((block, index) => story(block, index + 1)).join("")}</div>` : ""}
    ${addBlockButton()}
  `;
}

function joinEditor(page) {
  if (!editing) return "";
  return `<div class="edit-panel join-editor">
    <label>Lien Discord<input class="editable-field" data-join-field="discordUrl" value="${escapeHtml(page.discordUrl || "")}"></label>
    <label>Texte du bouton<input class="editable-field" data-join-field="discordLabel" value="${escapeHtml(page.discordLabel || "")}"></label>
    <label>Etapes<textarea class="editable-area" data-join-field="steps">${escapeHtml((page.steps || []).join("\n"))}</textarea></label>
  </div>`;
}

function renderForumShell() {
  return `<div class="forum-shell forum-embedded" id="forum-root"><div class="forum-empty">Chargement du forum...</div></div>`;
}

async function initForum() {
  await loadForum();
  renderForum();
}

async function loadForum() {
  const response = await fetch("./site_backend/forum.php", { headers: { Accept: "application/json" } });
  forumState = await response.json();
}

function renderForum() {
  const root = document.querySelector("#forum-root");
  if (!root) return;
  if (currentForumTopic) {
    root.innerHTML = renderForumTopic(currentForumTopic);
    return;
  }
  if (currentForumCategory) {
    root.innerHTML = renderForumCategory(currentForumCategory);
    return;
  }
  root.innerHTML = renderForumHome();
}

function renderForumHome() {
  const topicTotal = forumState.topics.length;
  const replyTotal = forumState.replies.length;
  return `
    <section class="forum-overview">
      <div>
        <p class="forum-kicker">Accueil du forum</p>
        <h2>Choisis un espace de discussion</h2>
        <p>Les sujets sont classés par catégorie pour retrouver vite les annonces, les sorties et les demandes d'aide.</p>
      </div>
      <div class="forum-stats"><span><strong>${topicTotal}</strong> sujets</span><span><strong>${replyTotal}</strong> réponses</span></div>
    </section>
    <div class="forum-toolbar">
      <h2>Catégories</h2>
      ${auth.permissions.createForumTopic ? `<button class="btn btn-primary" type="button" data-forum-action="new-topic">Nouveau sujet</button>` : `<a class="btn btn-outline-primary" href="./login.php">Se connecter pour publier</a>`}
    </div>
    <div class="forum-category-grid">
      ${forumState.categories.map((category) => forumCategoryCard(category)).join("") || `<div class="forum-empty">Aucune catégorie.</div>`}
    </div>
    ${auth.permissions.manageAccounts ? forumCategoryForm() : ""}
  `;
}

function forumCategoryCard(category) {
  const topics = forumState.topics.filter((topic) => topic.category_id === category.id);
  const replies = topics.reduce((total, topic) => total + Number(topic.reply_count || 0), 0);
  return `<button class="forum-category" type="button" data-forum-category="${escapeHtml(category.id)}">
    <span class="forum-category__icon">${escapeHtml((category.name || "?").slice(0, 1).toUpperCase())}</span>
    <div><h2>${escapeHtml(category.name)}</h2><p>${escapeHtml(category.description)}</p><small>${topics.length} sujets · ${replies} réponses</small></div>
  </button>`;
}

function forumCategoryForm() {
  return `<form class="forum-form forum-form--compact mt-4" data-forum-form="category">
    <h2>Créer une catégorie</h2>
    <div class="forum-form-grid">
      <label>Nom<input class="form-control" name="name" placeholder="Ex : Raids, artisanat, annonces"></label>
      <label>Description<input class="form-control" name="description" placeholder="Courte description visible sur la carte"></label>
    </div>
    <button class="btn btn-primary">Ajouter</button>
  </form>`;
}

function renderForumCategory(categoryId) {
  const category = forumState.categories.find((item) => item.id === categoryId);
  const topics = forumState.topics.filter((topic) => topic.category_id === categoryId);
  return `
    <div class="forum-toolbar">
      <div><button class="forum-back" type="button" data-forum-action="home">Retour aux catégories</button><h2>${escapeHtml(category?.name || "Catégorie")}</h2><p>${escapeHtml(category?.description || "")}</p></div>
      ${auth.permissions.createForumTopic ? `<button class="btn btn-primary" type="button" data-forum-action="new-topic" data-category="${escapeHtml(categoryId)}">Nouveau sujet</button>` : ""}
    </div>
    <div class="forum-topic-list">${topics.map((topic) => forumTopicRow(topic)).join("") || `<div class="forum-empty">Aucun sujet dans cette catégorie pour le moment.</div>`}</div>
  `;
}

function forumTopicRow(topic) {
  return `<article class="forum-topic" data-forum-topic="${topic.id}">
    <div><h2><button type="button" data-forum-topic-open="${topic.id}">${escapeHtml(topic.title)}</button></h2><p>${escapeHtml(excerpt(topic.body, 150))}</p><small>Par ${escapeHtml(topic.author)} · créé le ${escapeHtml(formatForumDate(topic.created_at))}</small></div>
    <div class="forum-topic__meta"><strong>${Number(topic.reply_count || 0)}</strong><span>réponses</span><small>${escapeHtml(topic.latest_activity || "")}</small></div>
  </article>`;
}

function renderForumTopic(topicId) {
  const topic = forumState.topics.find((item) => Number(item.id) === Number(topicId));
  if (!topic) return `<div class="forum-empty">Sujet introuvable.</div>`;
  const category = forumState.categories.find((item) => item.id === topic.category_id);
  const replies = forumState.replies.filter((reply) => Number(reply.topic_id) === Number(topic.id));
  return `
    <div class="forum-toolbar">
      <div><button class="forum-back" type="button" data-forum-category="${escapeHtml(topic.category_id)}">Retour à ${escapeHtml(category?.name || "la catégorie")}</button><h2>${escapeHtml(topic.title)}</h2><p>${replies.length} réponses · dernière activité ${escapeHtml(topic.latest_activity || "")}</p></div>
      ${auth.permissions.moderateForum ? `<button class="btn btn-outline-danger" type="button" data-forum-action="delete-topic" data-topic-id="${topic.id}">Supprimer le sujet</button>` : ""}
    </div>
    <article class="forum-post forum-post--lead">${forumAuthor(topic.author, topic.created_at)}<div class="forum-post__body">${escapeHtml(topic.body)}</div></article>
    <section class="forum-replies"><h2>Réponses</h2>${replies.map((reply) => forumReply(reply)).join("") || `<div class="forum-empty">Aucune réponse pour le moment.</div>`}</section>
    ${auth.user ? forumReplyForm(topic.id) : `<div class="forum-empty"><a href="./login.php">Connectez-vous</a> pour repondre.</div>`}
  `;
}

function forumAuthor(author, date) {
  return `<aside class="forum-author"><span>${escapeHtml((author || "?").slice(0, 1).toUpperCase())}</span><strong>${escapeHtml(author)}</strong><small>${escapeHtml(formatForumDate(date))}</small></aside>`;
}

function forumReply(reply) {
  return `<article class="forum-post">${forumAuthor(reply.author, reply.created_at)}<div><div class="forum-post__body">${escapeHtml(reply.body)}</div>${auth.permissions.moderateForum ? `<button class="btn btn-outline-danger btn-sm forum-inline-action" type="button" data-forum-action="delete-reply" data-reply-id="${reply.id}">Supprimer la réponse</button>` : ""}</div></article>`;
}

function forumTopicForm(categoryId = "") {
  return `<form class="forum-form" data-forum-form="topic">
    <h2>Nouveau sujet</h2>
    <div class="forum-form-grid">
      <label>Catégorie<select class="form-select" name="category_id">${forumState.categories.map((cat) => `<option value="${escapeHtml(cat.id)}" ${cat.id === categoryId ? "selected" : ""}>${escapeHtml(cat.name)}</option>`).join("")}</select></label>
      <label>Titre<input class="form-control" name="title" placeholder="Ex : Sortie cartes vendredi soir"></label>
    </div>
    <label>Message<textarea class="form-control" name="body" rows="8" placeholder="Ajoute les infos importantes..."></textarea></label>
    <div class="forum-form-actions"><button class="btn btn-primary">Publier</button><button class="btn btn-outline-primary" type="button" data-forum-action="cancel-compose">Annuler</button></div>
  </form>`;
}

function forumReplyForm(topicId) {
  return `<form class="forum-form mt-4" data-forum-form="reply" data-topic-id="${topicId}"><h2>Répondre</h2><label>Message<textarea class="form-control" name="body" rows="6" placeholder="Écris ta réponse..."></textarea></label><div class="forum-form-actions"><button class="btn btn-primary">Publier la réponse</button></div></form>`;
}

function addBlockButton() {
  return editing ? `<button class="btn btn-primary mt-3" type="button" data-edit-action="add-block">Ajouter un bloc</button>` : "";
}

function addResourceButton() {
  return editing ? `<button class="btn btn-primary mt-3" type="button" data-edit-action="add-resource">Ajouter un lien</button>` : "";
}

function toggleEditing() {
  if (!canEditCurrentRoute()) return;
  editing = !editing;
  renderPage(currentRoute, false);
}

async function saveSiteContent() {
  collectEdits();
  const response = await fetch("./site_backend/content.php", { method: "POST", headers: { "Content-Type": "application/json", Accept: "application/json" }, body: JSON.stringify({ content: siteContent }) });
  const data = await response.json();
  siteContent = data.content;
  editing = false;
  renderPage(currentRoute, false);
}

function collectEdits() {
  syncAllWikiRichEditors();
  const page = getPage(currentRoute);
  document.querySelectorAll("[data-page-field]").forEach((field) => { page[field.dataset.pageField] = field.value; });
  document.querySelectorAll("[data-block-index]").forEach((panel) => {
    if (!page.blocks) return;
    const block = page.blocks[Number(panel.dataset.blockIndex)];
    if (!block) return;
    panel.querySelectorAll("[data-block-field]").forEach((field) => {
      block[field.dataset.blockField] = field.dataset.blockField === "paragraphs" ? field.value.split("\n").filter(Boolean) : field.value;
    });
  });
  document.querySelectorAll("[data-resource-index]").forEach((field) => {
    if (!page.resources) return;
    page.resources[Number(field.dataset.resourceIndex)][field.dataset.resourceField] = field.value;
  });
  document.querySelectorAll("[data-member-index]").forEach((panel) => {
    if (!page.members) return;
    const member = page.members[Number(panel.dataset.memberIndex)];
    if (!member) return;
    panel.querySelectorAll("[data-member-field]").forEach((field) => {
      member[field.dataset.memberField] = field.value;
    });
  });
  document.querySelectorAll("[data-wiki-index]").forEach((panel) => {
    if (!page.articles) return;
    const article = page.articles[Number(panel.dataset.wikiIndex)];
    if (!article) return;
    panel.querySelectorAll("[data-wiki-field]").forEach((field) => {
      article[field.dataset.wikiField] = field.dataset.wikiField === "body" ? field.value.split("\n").filter(Boolean) : field.value;
    });
  });
  document.querySelectorAll("[data-join-field]").forEach((field) => {
    page[field.dataset.joinField] = field.dataset.joinField === "steps" ? field.value.split("\n").filter(Boolean) : field.value;
  });
}

async function uploadImage(input) {
  const file = input.files?.[0];
  if (!file) return;
  const panel = input.closest("[data-block-index]");
  const status = panel?.querySelector("[data-upload-status]");
  if (status) status.textContent = "Envoi de l'image...";
  const data = new FormData();
  data.append("image", file);
  try {
    const response = await fetch("./site_backend/upload.php", { method: "POST", body: data });
    const result = await response.json();
    if (!response.ok || !result.path) throw new Error(result.error || "Upload impossible");
    panel.querySelector('[data-block-field="image"]').value = result.path;
    panel.querySelector(".upload-preview img").src = result.path;
    if (status) status.textContent = "Image stockée sur le site : " + result.path;
  } catch (error) {
    if (status) status.textContent = error.message;
  }
}

function renderAgenda() {
  return `<div class="agenda-shell">
    ${auth.permissions.manageEvents ? `<div class="agenda-toolbar"><span>Gestion du planning</span><button class="btn btn-primary" type="button" data-agenda-action="new">Ajouter un événement</button></div>` : ""}
    <div class="agenda-layout">
      <section class="agenda-panel agenda-list-panel">
        <div class="agenda-list" id="agenda-list"></div>
      </section>
      ${auth.permissions.manageEvents ? agendaForm() : ""}
    </div>
  </div>`;
}

function agendaForm() {
  const eventTypes = ["Cartes", "Sadiques", "Raid 24", "Farm monture", "Donjons à embranchement", "Défis Extrêmes", "Irréel", "Fun", "Social", "Autre"];
  return `<aside class="agenda-panel agenda-editor">
    <h2>Nouvel événement</h2>
    <form id="agenda-form" class="agenda-form">
      <input type="hidden" name="id">
      <label class="field-span">Titre<input class="form-control" name="title" required></label>
      <label>Date<input class="form-control" type="date" name="date" required></label>
      <label>Heure<input class="form-control" type="time" name="time" required></label>
      <label>Type<select class="form-select" name="type">${eventTypes.map((type) => `<option>${escapeHtml(type)}</option>`).join("")}</select></label>
      <label class="field-span">Description<textarea class="form-control" name="description" rows="5"></textarea></label>
      <button class="btn btn-primary field-span" type="submit">Enregistrer</button>
    </form>
  </aside>`;
}

async function initAgenda() {
  const response = await fetch("./site_backend/agenda.php", { headers: { Accept: "application/json" } });
  const data = await response.json();
  agendaEvents = data.events || [];
  renderAgendaEvents();
}

function renderAgendaEvents() {
  const list = document.querySelector("#agenda-list");
  if (!list) return;
  list.innerHTML = agendaEvents.map((event) => `<article class="agenda-event">
    <div class="agenda-date"><strong>${escapeHtml(event.time)}</strong><span>${escapeHtml(formatDate(event.date))}</span></div>
    <div class="agenda-event__body">
      <div class="agenda-event__top"><span class="agenda-badge">${escapeHtml(event.type)}</span>${auth.permissions.manageEvents ? `<button class="btn btn-outline-danger btn-sm" data-agenda-action="delete" data-id="${escapeHtml(event.id)}">Supprimer</button>` : ""}</div>
      <h2>${escapeHtml(event.title)}</h2>
      <p>${escapeHtml(event.description)}</p>
    </div>
  </article>`).join("") || `<div class="agenda-empty">Aucun événement.</div>`;
}

async function saveAgenda(event) {
  event.preventDefault();
  const form = event.target.closest("#agenda-form") || event.currentTarget;
  const fd = new FormData(form);
  const item = Object.fromEntries(fd.entries());
  item.id = item.id || `event-${Date.now()}`;
  const previousEvents = [...agendaEvents];
  agendaEvents = upsertAgendaEvent(agendaEvents, item);
  renderAgendaEvents();
  try {
    const response = await fetch("./site_backend/agenda.php", { method: "POST", headers: { "Content-Type": "application/json", Accept: "application/json" }, body: JSON.stringify({ action: "create", event: item }) });
    const data = await response.json();
    if (!response.ok) throw new Error(data.error || "Impossible d'enregistrer l'événement.");
    agendaEvents = data.events || agendaEvents;
    renderAgendaEvents();
    form.reset();
  } catch (error) {
    agendaEvents = previousEvents;
    renderAgendaEvents();
    window.alert(error.message);
  }
}

function upsertAgendaEvent(events, item) {
  const next = [...events];
  const index = next.findIndex((event) => event.id === item.id);
  if (index >= 0) next[index] = item;
  else next.push(item);
  return next.sort((left, right) => `${left.date || ""} ${left.time || ""}`.localeCompare(`${right.date || ""} ${right.time || ""}`));
}

document.addEventListener("click", async (event) => {
  const routeLink = event.target.closest("[data-route]");
  if (routeLink) {
    event.preventDefault();
    window.location.hash = routeLink.dataset.route;
    renderPage(routeLink.dataset.route);
    return;
  }
  const wikiArticle = event.target.closest("[data-wiki-article]");
  if (wikiArticle) {
    currentWikiArticle = Number(wikiArticle.dataset.wikiArticle);
    renderPage("wiki", false);
    return;
  }
  const wikiInlineLink = event.target.closest("[data-wiki-id]");
  if (wikiInlineLink) {
    const page = getPage("wiki");
    const targetId = normalizeWikiId(wikiInlineLink.dataset.wikiId);
    const targetIndex = (page.articles || []).findIndex((article) => normalizeWikiId(article.id || article.title) === targetId);
    if (targetIndex >= 0) {
      currentWikiArticle = targetIndex;
      currentWikiCategory = page.articles[targetIndex].category || "Tous";
      renderPage("wiki", false);
    }
    return;
  }
  const wikiTocLink = event.target.closest("[data-wiki-toc]");
  if (wikiTocLink) {
    document.getElementById(wikiTocLink.dataset.wikiToc)?.scrollIntoView({ behavior: "smooth", block: "start" });
    return;
  }
  const wikiFormat = event.target.closest("[data-wiki-format]");
  if (wikiFormat) {
    applyWikiFormat(wikiFormat);
    return;
  }
  const wikiCategory = event.target.closest("[data-wiki-category]");
  if (wikiCategory) {
    currentWikiCategory = wikiCategory.dataset.wikiCategory;
    currentWikiArticle = 0;
    renderPage("wiki", false);
    return;
  }
  const forumCategory = event.target.closest("[data-forum-category]");
  if (forumCategory) {
    currentForumCategory = forumCategory.dataset.forumCategory;
    currentForumTopic = null;
    renderForum();
    return;
  }
  const forumTopicOpen = event.target.closest("[data-forum-topic-open]");
  if (forumTopicOpen) {
    currentForumTopic = Number(forumTopicOpen.dataset.forumTopicOpen);
    renderForum();
    return;
  }
  const forumAction = event.target.closest("[data-forum-action]");
  if (forumAction) {
    await handleForumAction(forumAction);
    return;
  }
  const action = event.target.closest("[data-edit-action]")?.dataset.editAction;
  if (action === "save") await saveSiteContent();
  if (action === "cancel") toggleEditing();
  if (action === "add-block") { siteContent[currentRoute].blocks = siteContent[currentRoute].blocks || []; siteContent[currentRoute].blocks.push({ title: "Nouveau bloc", image: "", paragraphs: ["Nouveau texte."] }); renderPage(currentRoute, false); }
  if (action === "remove-block") { siteContent[currentRoute].blocks.splice(Number(event.target.dataset.index), 1); renderPage(currentRoute, false); }
  if (action === "add-resource") { siteContent[currentRoute].resources = siteContent[currentRoute].resources || []; siteContent[currentRoute].resources.push({ title: "Nouveau lien", image: "./site_img/forum.png", text: "Description", url: "#", action: "Ouvrir" }); renderPage(currentRoute, false); }
  if (action === "add-member") { siteContent.members.members.push({ name: "Nouveau membre", image: "./site_img/placeholder4.png", lodestone: "" }); renderPage(currentRoute, false); }
  if (action === "remove-member") { siteContent.members.members.splice(Number(event.target.dataset.index), 1); renderPage(currentRoute, false); }
  if (action === "add-wiki-article") {
    collectEdits();
    const title = "Nouvel’article";
    siteContent.wiki.articles.push({
      id: makeWikiArticleId(title, siteContent.wiki.articles || []),
      title,
      category: siteContent.wiki.categories[0] || "Général",
      image: "",
      bodyHtml: "<h2>Nouveau guide</h2><p>Présente ici le contexte, l'objectif ou les prérequis.</p><h3>Étapes</h3><ul><li>Premier point important.</li><li>Deuxième point important.</li></ul>",
    });
    currentWikiArticle = siteContent.wiki.articles.length - 1;
    renderPage("wiki", false);
  }
  if (action === "remove-wiki-article") {
    siteContent.wiki.articles.splice(Number(event.target.dataset.index), 1);
    currentWikiArticle = Math.max(0, currentWikiArticle - 1);
    renderPage("wiki", false);
  }
  if (action === "add-wiki-category") {
    collectEdits();
    const name = window.prompt("Nom de la catégorie");
    if (name) {
      siteContent.wiki.categories = [...new Set([...(siteContent.wiki.categories || []), name.trim()].filter(Boolean))];
    }
    renderPage("wiki", false);
  }
  const agendaAction = event.target.closest("[data-agenda-action]")?.dataset.agendaAction;
  if (agendaAction === "delete") {
    const response = await fetch("./site_backend/agenda.php", { method: "POST", headers: { "Content-Type": "application/json", Accept: "application/json" }, body: JSON.stringify({ action: "delete", event: { id: event.target.dataset.id } }) });
    const data = await response.json();
    agendaEvents = data.events || [];
    renderAgendaEvents();
  }
});

document.addEventListener("submit", async (event) => {
  const agendaFormElement = event.target.closest("#agenda-form");
  if (agendaFormElement) {
    await saveAgenda(event);
    return;
  }
  const form = event.target.closest("[data-forum-form]");
  if (!form) return;
  event.preventDefault();
  await submitForumForm(form);
});

document.addEventListener("change", (event) => {
  if (event.target.matches("[data-upload-image]")) uploadImage(event.target);
  if (event.target.matches("[data-upload-member-image]")) uploadMemberImage(event.target);
  if (event.target.matches("[data-upload-wiki-image]")) uploadWikiImage(event.target);
});

document.addEventListener("mousedown", (event) => {
  if (event.target.closest("[data-wiki-format]")) event.preventDefault();
});

document.addEventListener("input", (event) => {
  const wikiPanel = event.target.closest("[data-wiki-index]");
  if (wikiPanel && event.target.matches("[data-wiki-field], [data-wiki-rich-editor]")) {
    syncWikiRichEditor(wikiPanel);
  }
  if (!event.target.matches("[data-wiki-search]")) return;
  const query = event.target.value.trim().toLowerCase();
  const page = getPage("wiki");
  const articles = page.articles || [];
  const filtered = articles.filter((article) => {
    const haystack = [article.title, article.category, article.summary, stripHtml(article.bodyHtml || ""), ...(article.body || [])].join(" ").toLowerCase();
    const inCategory = currentWikiCategory === "Tous" || article.category === currentWikiCategory;
    return inCategory && haystack.includes(query);
  });
  const list = document.querySelector("[data-wiki-list]");
  if (list) list.innerHTML = renderWikiList(filtered, articles);
});

function applyWikiFormat(control) {
  const panel = control.closest("[data-wiki-index]");
  const editor = panel?.querySelector("[data-wiki-rich-editor]");
  if (!editor) return;
  editor.focus();
  const format = control.dataset.wikiFormat;
  if (format === "h2") document.execCommand("formatBlock", false, "h2");
  if (format === "h3") document.execCommand("formatBlock", false, "h3");
  if (format === "p") document.execCommand("formatBlock", false, "p");
  if (format === "bold") document.execCommand("bold", false);
  if (format === "list") document.execCommand("insertUnorderedList", false);
  if (format === "quote") {
    insertHtmlAtCursor(`<blockquote><p>Note importante...</p></blockquote><p><br></p>`);
  }
  if (format === "link") {
    const url = window.prompt("Adresse du lien");
    if (url) document.execCommand("createLink", false, url);
    editor.querySelectorAll("a").forEach((link) => {
      link.target = "_blank";
      link.rel = "noreferrer";
    });
  }
  if (format === "table") {
    insertHtmlAtCursor(`<div class="wiki-table-wrap"><table class="wiki-table"><tr><th>Nom</th><th>Lien</th></tr><tr><td>Nouveau contenu</td><td><a href="https://exemple.fr" target="_blank" rel="noreferrer">https://exemple.fr</a></td></tr></table></div><p><br></p>`);
  }
  if (format === "image") {
    const url = window.prompt("Chemin ou adresse de l'image");
    if (url) insertHtmlAtCursor(`<figure class="wiki-media"><img src="${escapeHtml(url)}" alt=""></figure><p><br></p>`);
  }
  if (format === "hr") insertHtmlAtCursor("<hr><p><br></p>");
  syncWikiRichEditor(panel);
}

function insertHtmlAtCursor(html) {
  const selection = window.getSelection();
  if (!selection || !selection.rangeCount) return;
  const range = selection.getRangeAt(0);
  range.deleteContents();
  const template = document.createElement("template");
  template.innerHTML = html;
  const fragment = template.content;
  const lastNode = fragment.lastChild;
  range.insertNode(fragment);
  if (lastNode) {
    range.setStartAfter(lastNode);
    range.collapse(true);
    selection.removeAllRanges();
    selection.addRange(range);
  }
}

function syncAllWikiRichEditors() {
  document.querySelectorAll("[data-wiki-index]").forEach((panel) => syncWikiRichEditor(panel));
}

function syncWikiRichEditor(panel) {
  const editor = panel.querySelector("[data-wiki-rich-editor]");
  const storage = panel.querySelector(".wiki-content-storage");
  if (!editor || !storage) return;
  const cloned = editor.cloneNode(true);
  cloned.querySelectorAll("[id^='wiki-section-']").forEach((node) => node.removeAttribute("id"));
  storage.value = cloned.innerHTML.trim();
}

async function uploadMemberImage(input) {
  const file = input.files?.[0];
  if (!file) return;
  const panel = input.closest("[data-member-index]");
  const status = panel?.querySelector("[data-upload-status]");
  if (status) status.textContent = "Envoi de l'image...";
  const data = new FormData();
  data.append("image", file);
  try {
    const response = await fetch("./site_backend/upload.php", { method: "POST", body: data });
    const result = await response.json();
    if (!response.ok || !result.path) throw new Error(result.error || "Upload impossible");
    panel.querySelector('[data-member-field="image"]').value = result.path;
    panel.closest(".member-card")?.querySelector("img")?.setAttribute("src", result.path);
    if (status) status.textContent = "Image stockée sur le site : " + result.path;
  } catch (error) {
    if (status) status.textContent = error.message;
  }
}

async function uploadWikiImage(input) {
  const file = input.files?.[0];
  if (!file) return;
  const panel = input.closest("[data-wiki-index]");
  const status = panel?.querySelector("[data-upload-status]");
  if (status) status.textContent = "Envoi de l'image...";
  const data = new FormData();
  data.append("image", file);
  try {
    const response = await fetch("./site_backend/upload.php", { method: "POST", body: data });
    const result = await response.json();
    if (!response.ok || !result.path) throw new Error(result.error || "Upload impossible");
    const imageField = panel.querySelector('[data-wiki-field="image"]');
    if (imageField) imageField.value = result.path;
    const preview = panel.querySelector("[data-wiki-cover-preview]");
    if (preview?.tagName === "IMG") {
      preview.src = result.path;
    } else if (preview) {
      preview.outerHTML = `<img data-wiki-cover-preview src="${escapeHtml(result.path)}" alt="">`;
    }
    if (status) status.textContent = "Image stockée sur le site : " + result.path;
    collectEdits();
  } catch (error) {
    if (status) status.textContent = error.message;
  }
}

window.addEventListener("hashchange", () => renderPage(getRouteFromHash()));

async function handleForumAction(control) {
  const action = control.dataset.forumAction;
  if (action === "home") {
    currentForumCategory = "";
    currentForumTopic = null;
    renderForum();
  }
  if (action === "new-topic") {
    const root = document.querySelector("#forum-root");
    const categoryId = control.dataset.category || currentForumCategory || forumState.categories[0]?.id || "";
    if (root) root.innerHTML = forumTopicForm(categoryId);
  }
  if (action === "cancel-compose") {
    renderForum();
  }
  if (action === "delete-topic") {
    await forumPost({ action: "delete_topic", topic_id: Number(control.dataset.topicId) });
    currentForumTopic = null;
    renderForum();
  }
  if (action === "delete-reply") {
    await forumPost({ action: "delete_reply", reply_id: Number(control.dataset.replyId) });
    renderForum();
  }
}

async function submitForumForm(form) {
  const kind = form.dataset.forumForm;
  const fields = Object.fromEntries(new FormData(form).entries());
  if (kind === "category") {
    await forumPost({ action: "create_category", name: fields.name, description: fields.description });
    currentForumCategory = "";
    currentForumTopic = null;
  }
  if (kind === "topic") {
    await forumPost({ action: "create_topic", category_id: fields.category_id, title: fields.title, body: fields.body });
    currentForumCategory = fields.category_id;
  }
  if (kind === "reply") {
    await forumPost({ action: "reply", topic_id: Number(form.dataset.topicId), body: fields.body });
  }
  renderForum();
}

async function forumPost(payload) {
  const response = await fetch("./site_backend/forum.php", {
    method: "POST",
    headers: { "Content-Type": "application/json", Accept: "application/json" },
    body: JSON.stringify({ ...payload, csrf_token: forumState.csrf }),
  });
  const data = await response.json();
  if (!response.ok) {
    window.alert(data.error || "Action impossible.");
    return;
  }
  forumState = data;
}

function getRouteFromHash() { return window.location.hash.replace("#", "") || "home"; }
function formatDate(value) {
  if (!value) return "";
  const [year, month, day] = String(value).split("-");
  return [day, month, year].filter(Boolean).join("/");
}
function excerpt(value, limit = 140) {
  const text = String(value || "").replace(/\s+/g, " ").trim();
  return text.length > limit ? text.slice(0, limit).trim() + "..." : text;
}
function formatForumDate(value) {
  if (!value) return "";
  const date = new Date(value);
  return Number.isNaN(date.getTime()) ? String(value) : date.toLocaleString("fr-FR", { dateStyle: "short", timeStyle: "short" });
}
function escapeHtml(value) { return String(value ?? "").replaceAll("&", "&amp;").replaceAll("<", "&lt;").replaceAll(">", "&gt;").replaceAll('"', "&quot;").replaceAll("'", "&#039;"); }
function stripHtml(value) {
  const template = document.createElement("template");
  template.innerHTML = value;
  return template.content.textContent || "";
}
function normalizeWikiId(value) {
  return String(value || "").trim().replace(/^:+/, "").replace(/.*:/, "").toLowerCase();
}
function normalizeText(value) {
  return String(value || "").toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/[^\w ]+/g, " ").replace(/\s+/g, " ").trim();
}
