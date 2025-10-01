### Hexlet tests and linter status:
[![Actions Status](https://github.com/NikolayIz/php-project-9/actions/workflows/hexlet-check.yml/badge.svg)](https://github.com/NikolayIz/php-project-9/actions)
### Project Status and Quality
[![PHP-project-9 workflow](https://github.com/NikolayIz/php-project-9/actions/workflows/main.yml/badge.svg)](https://github.com/NikolayIz/php-project-9/actions/workflows/main.yml)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=NikolayIz_php-project-9&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=NikolayIz_php-project-9)

# PHP Page analyzer
A simple service for analyzing web pages: checking headings, meta tags, and HTTP status codes.  
Stores sites and their checks in a database.

### Domen:
[page-analyze-main](https://php-project-9-mq68.onrender.com/)

### Getting Started
```bash
git clone https://github.com/NikolayIz/php-project-9.git`
cd php-project-9
make setup`
```
Create a local database - [instructions](https://github.com/Hexlet/ru-instructions/blob/main/postgresql.md)

Set the database connection in the terminal:

```bash
export DATABASE_URL=postgresql://janedoe:mypassword@localhost:5432/mydb
```
Format: {provider}://{user}:{password}@{host}:{port}/{db}

Start the local server:
`make start`

### Running Tests
Run all tests:
```bash
make test
```
Run tests with code coverage (required for SonarCloud):
```bash
make test-coverage
```

### Project Structure
src/ — source code

tests/ — unit tests

public/ — front-end

### License
MIT