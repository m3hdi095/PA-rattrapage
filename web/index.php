<?php
require_once __DIR__ . '/includes/i18n.php';
?>
<!DOCTYPE html>
<html lang="<?= currentLang() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= t('site_title') ?></title>
    <link rel="stylesheet" href="public/css/styles.css">
</head>
<body>
    <div class="header">
        <h1><?= t('site_title') ?></h1>
    </div>

    <nav class="home-navbar">
        <div class="navbar-links">
            <a href="#histoire"><?= t('nav_histoire') ?></a>
            <a href="#benevoles"><?= t('nav_benevoles') ?></a>
            <a href="#adherents"><?= t('nav_adherents') ?></a>
        </div>
        <div class="navbar-right">
            <a class="lang-link" href="?lang=<?= otherLang() ?>"><?= otherLangLabel() ?></a>
            <a class="button" href="admin/index.php"><?= t('nav_admin_connexion') ?></a>
        </div>
    </nav>

    <div class="container">
        <div class="hero">
            <div class="hero-text">
                <p class="hero-tagline"><?= t('home_tagline') ?></p>
                <div class="hero-stats">
                    <div class="hero-stat">
                        <span class="hero-stat-number">14</span>
                        <span class="hero-stat-label"><?= t('home_stat_salaries') ?></span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-number">200+</span>
                        <span class="hero-stat-label"><?= t('home_stat_benevoles') ?></span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-number">7</span>
                        <span class="hero-stat-label"><?= t('home_stat_villes') ?></span>
                    </div>
                </div>
            </div>
            <div class="hero-visual">
                <svg viewBox="0 0 400 320" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Illustration d'une caisse de produits redistribues">
                    <circle cx="200" cy="160" r="150" fill="var(--card-bg)" />
                    <path d="M 90 150 A 110 110 0 1 1 100 210" fill="none" stroke="var(--button-bg)" stroke-width="6" stroke-linecap="round" stroke-dasharray="14 12" />
                    <path d="M 95 205 L 100 225 L 118 214 Z" fill="var(--button-bg)" />
                    <g transform="translate(120,150)">
                        <rect x="0" y="40" width="160" height="90" rx="10" fill="var(--navbar-bg)" stroke="var(--border-color)" stroke-width="3" />
                        <line x1="0" y1="70" x2="160" y2="70" stroke="var(--border-color)" stroke-width="3" />
                        <line x1="0" y1="100" x2="160" y2="100" stroke="var(--border-color)" stroke-width="3" />
                        <line x1="40" y1="40" x2="40" y2="130" stroke="var(--border-color)" stroke-width="3" />
                        <line x1="120" y1="40" x2="120" y2="130" stroke="var(--border-color)" stroke-width="3" />
                        <circle cx="30" cy="25" r="22" fill="#e07a3f" />
                        <circle cx="80" cy="15" r="26" fill="#c9a227" />
                        <circle cx="130" cy="25" r="20" fill="#e07a3f" />
                        <path d="M 30 3 Q 36 -8 46 -2" fill="none" stroke="#74c69d" stroke-width="4" stroke-linecap="round" />
                    </g>
                </svg>
            </div>
        </div>

        <section id="histoire" class="card anchor-section">
            <h2><?= t('histoire_heading') ?></h2>
            <p><?= t('histoire_text') ?></p>
        </section>

        <div class="how-it-works">
            <h2 class="section-title"><?= t('home_how_heading') ?></h2>
            <div class="steps-grid">
                <div class="step">
                    <span class="step-icon">🚚</span>
                    <h3><?= t('home_step1_title') ?></h3>
                    <p><?= t('home_step1_desc') ?></p>
                </div>
                <div class="step">
                    <span class="step-icon">📦</span>
                    <h3><?= t('home_step2_title') ?></h3>
                    <p><?= t('home_step2_desc') ?></p>
                </div>
                <div class="step">
                    <span class="step-icon">🤲</span>
                    <h3><?= t('home_step3_title') ?></h3>
                    <p><?= t('home_step3_desc') ?></p>
                </div>
            </div>
        </div>

        <section id="benevoles" class="card anchor-section">
            <h2><?= t('benevoles_heading') ?></h2>
            <p><?= t('benevoles_text') ?></p>
            <a class="button" href="benevole/index.php"><?= t('login_button') ?></a>
        </section>

        <section id="adherents" class="card anchor-section">
            <h2><?= t('adherents_heading') ?></h2>
            <p><?= t('adherents_text') ?></p>
            <a class="button" href="adherent/index.php"><?= t('login_button') ?></a>
        </section>
    </div>
</body>
</html>
