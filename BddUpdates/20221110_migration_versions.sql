CREATE TABLE migration_versions (
  version varchar(191) COLLATE utf8_unicode_ci NOT NULL,
  executed_at datetime DEFAULT NULL,
  execution_time int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO migration_versions (version, executed_at, execution_time) VALUES
('App\\Migrations\\Base\\Version20220511132634', '2022-11-14 09:00:00', 0),
('App\\Migrations\\Base\\Version20220511134836', '2022-11-14 09:00:00', 0);
