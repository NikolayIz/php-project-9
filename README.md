### Hexlet tests and linter status:
[![Actions Status](https://github.com/NikolayIz/php-project-9/actions/workflows/hexlet-check.yml/badge.svg)](https://github.com/NikolayIz/php-project-9/actions)
### Project Status and Quality
[![PHP-project-9 workflow](https://github.com/NikolayIz/php-project-9/actions/workflows/main.yml/badge.svg)](https://github.com/NikolayIz/php-project-9/actions/workflows/main.yml)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=NikolayIz_php-project-9&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=NikolayIz_php-project-9)

# PHP Page analyzer
### Domen:
[page-analyze-main](https://php-project-9-mq68.onrender.com/)


### Start workink on a project
`git clone`

`make setup`

Создаем локальную базу данных - [инструкция](https://github.com/Hexlet/ru-instructions/blob/main/postgresql.md)

Для подключения к локальной БД нужно сделать в терминале:

```bash
export DATABASE_URL=postgresql://janedoe:mypassword@localhost:5432/mydb
```
У строки следующий формат: {provider}://{user}:{password}@{host}:{port}/{db}

Для запуска локального сервера:
`make start`
