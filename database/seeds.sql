-- =============================================================================
-- Dados de referência: agentes e mapas do Valorant.
--
-- Os nomes de arquivo correspondem exatamente aos assets em
-- public/assets/images/{agents,maps}/.
--
-- Escrito em SQL portátil (PostgreSQL e SQLite): a lista vive numa subconsulta
-- com UNION ALL e o `WHERE NOT EXISTS` torna o seed idempotente, em vez de
-- depender de `ON CONFLICT` (Postgres) ou `INSERT OR IGNORE` (SQLite), que têm
-- sintaxes incompatíveis.
-- =============================================================================

INSERT INTO agents (name, photo)
SELECT v.name, v.photo
  FROM (
              SELECT 'Astra'     AS name, 'astra.png'     AS photo
    UNION ALL SELECT 'Breach',        'breach.png'
    UNION ALL SELECT 'Brimstone',     'brimstone.png'
    UNION ALL SELECT 'Chamber',       'chamber.png'
    UNION ALL SELECT 'Clove',         'clove.png'
    UNION ALL SELECT 'Cypher',        'cypher.png'
    UNION ALL SELECT 'Deadlock',      'deadlock.png'
    UNION ALL SELECT 'Fade',          'fade.png'
    UNION ALL SELECT 'Gekko',         'gekko.png'
    UNION ALL SELECT 'Harbor',        'harbor.png'
    UNION ALL SELECT 'Iso',           'iso.png'
    UNION ALL SELECT 'Jett',          'jett.png'
    UNION ALL SELECT 'KAY/O',         'kayo.png'
    UNION ALL SELECT 'Killjoy',       'killjoy.png'
    UNION ALL SELECT 'Neon',          'neon.png'
    UNION ALL SELECT 'Omen',          'omen.png'
    UNION ALL SELECT 'Phoenix',       'phoenix.png'
    UNION ALL SELECT 'Raze',          'raze.png'
    UNION ALL SELECT 'Reyna',         'reyna.png'
    UNION ALL SELECT 'Sage',          'sage.png'
    UNION ALL SELECT 'Skye',          'skye.png'
    UNION ALL SELECT 'Sova',          'sova.png'
    UNION ALL SELECT 'Tejo',          'tejo.png'
    UNION ALL SELECT 'Veto',          'veto.png'
    UNION ALL SELECT 'Viper',         'viper.png'
    UNION ALL SELECT 'Vyse',          'vyse.png'
    UNION ALL SELECT 'Waylay',        'waylay.png'
    UNION ALL SELECT 'Yoru',          'yoru.png'
  ) AS v
 WHERE NOT EXISTS (SELECT 1 FROM agents a WHERE a.name = v.name);

INSERT INTO maps (name, image)
SELECT v.name, v.image
  FROM (
              SELECT 'Abyss'    AS name, 'abyss.png'    AS image
    UNION ALL SELECT 'Ascent',       'ascent.png'
    UNION ALL SELECT 'Bind',         'bind.png'
    UNION ALL SELECT 'Breeze',       'breeze.png'
    UNION ALL SELECT 'Corrode',      'corrode.png'
    UNION ALL SELECT 'Fracture',     'fracture.png'
    UNION ALL SELECT 'Haven',        'haven.png'
    UNION ALL SELECT 'Icebox',       'icebox.png'
    UNION ALL SELECT 'Lotus',        'lotus.png'
    UNION ALL SELECT 'Pearl',        'pearl.png'
    UNION ALL SELECT 'Split',        'split.png'
    UNION ALL SELECT 'Sunset',       'sunset.png'
  ) AS v
 WHERE NOT EXISTS (SELECT 1 FROM maps m WHERE m.name = v.name);
