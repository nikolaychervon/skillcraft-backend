# GradeUP 🧠⚡

**GradeUP** — это AI-платформа для профессионального роста разработчиков.  
Персональный ментор, диагностика навыков и умный план развития — всё в одном месте.

---

## 🐳 Установка

1. Клонируй репозиторий:
```shell
  git clone https://github.com/nikolaychervon/gradeup-backend.git
```

2. Неожидано, не так ли:
```shell
  cd gradeup-backend
```

3. Запускай сборку:
```shell
  make setup
```

P.S. Все доступные команды make, можно посмотреть так:
```shell
  make help
```

Сайт: http://localhost

Telescope: http://localhost/telescope

Horizon: http://localhost/horizon

---
## 📂 Структура проекта

```bash
  gradeup-backend/
    ├── 🐳 docker/ # Настройки контейнеров
    ├── 🐘 docker-compose.yml # Docker-compose окружение (dev)
    ├── 📜 Makefile # Команды для управления проектом
    ├── 📁 src/
    │   ├── app/
    │   │   ├── Application/                # Слой приложения (use-cases, сборка DTO)
    │   │   │   ├── Auth/                   # Аутентификация
    │   │   │   └── Shared/                 # Общие компоненты (DTO, assemblers, exceptions, constants)
    │   │   ├── Domain/                     # Домен (бизнес-логика)
    │   │   │   └── Auth/
    │   │   │       ├── Actions/            # Юзкейсы (Login/Register/Reset/etc)
    │   │   │       ├── Cache/              # Доменные интерфейсы кеша
    │   │   │       ├── Constants/          # Доменные константы
    │   │   │       ├── DTO/                # DTO домена
    │   │   │       ├── Exceptions/         # Доменные исключения
    │   │   │       ├── Repositories/       # Доменные интерфейсы репозиториев
    │   │   │       ├── Services/           # Доменные интерфейсы сервисов (hash/tokens/notify/tx)
    │   │   │       └── Specifications/     # Спецификации (правила)
    │   │   ├── Infrastructure/             # Инфраструктура (Laravel/Eloquent/Cache/Notify/DB)
    │   │   │   ├── Auth/
    │   │   │   │   ├── Cache/              # Реализации кеша
    │   │   │   │   ├── Repositories/       # Реализации репозиториев
    │   │   │   │   └── Services/           # Реализации доменных сервисов
    │   │   │   └── Notifications/          # Email-уведомления (queued)
    │   │   ├── Http/                       # Controllers, Requests, Responses, Middlewares
    │   │   ├── Models/                     # Eloquent модели
    │   │   └── Providers/                  # Service providers (bindings)
    │   ├── config/                         # Конфиги Laravel
    │   ├── database/                       # Миграции, сидеры, фабрики
    │   ├── lang/                           # Локализация (ru/en)
    │   ├── routes/                         # Роуты (api.php v1 и т.д.)
    │   ├── tests/                          # Unit/Feature тесты
    │   └── .env / .env.example             # Окружение (не коммитим .env)
    └── 📖 README.md # Ты тут :)
```