CREATE TABLE IF NOT EXISTS urls (
  id SERIAL PRIMARY KEY,
  name VARCHAR(255) NOT NULL UNIQUE,
  created_at TIMESTAMP NOT NULL
);

CREATE TABLE IF NOT EXISTS url_checks (
  id SERIAL PRIMARY KEY,
  url_id bigint NOT NULL REFERENCES urls(id) ON DELETE CASCADE, --ON DELETE CASCADE, чтобы при удалении сайта удалялись проверки:
  status_code integer,
  h1 VARCHAR(255),
  title VARCHAR(255),
  description VARCHAR(255),
  created_at TIMESTAMP NOT NULL
);
