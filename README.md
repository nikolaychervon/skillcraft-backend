# GradeUP 🧠⚡ (в разработке)

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

Структура показана только для нескольких модулей, но она распространяется на все. Показано только для понимания.

```bash
  gradeup-backend/
    ├── 🐳 docker/                          # Настройки контейнеров
    ├── 🐘 docker-compose.yml               # Docker-compose окружение (dev)
    ├── 📜 Makefile                         # Команды для управления проектом
    ├── 📁 src/
    │   ├── app/
    │   │   ├── Domain/                     # Домен (агрегат User)
    │   │   │   └── User/
    │   │   │       ├── Repositories/       # Интерфейсы репозиториев
    │   │   │       ├── Exceptions/         # Общие исключения
    │   │   │       ├── Auth/               # Модуль аутентификации: вход, регистрация, верификация, сброс пароля
    │   │   │       │   ├── Actions/        # Экшены (бизнес логика)
    │   │   │       │   ├── Cache/          # Интерфейсы сервисов кеша
    │   │   │       │   ├── Constants/      # Константы
    │   │   │       │   ├── DTO/            # DTO домена
    │   │   │       │   ├── Exceptions/     # Исключения
    │   │   │       │   ├── Services/       # Сервисы
    │   │   │       │   └── Specifications/ # Спецификации
    │   │   │       └── Profile/            # Модуль профиля: профиль, смена email/пароля
    │   │   │           ├── Actions/        # Экшены (бизнес логика)
    │   │   │           ├── Constants/      # Константы
    │   │   │           ├── DTO/            # DTO домена профиля
    │   │   │           ├── Exceptions/     # Исключения
    │   │   │           └── Services/       # Сервисы
    │   │   ├── Application/                # Слой приложения
    │   │   │   ├── User/
    │   │   │   │   ├── Auth/               # Классы для модуля аутентификации
    │   │   │   │   └── Profile/            # Классы для модуля профиля
    │   │   │   └── Shared/                 # Общие классы
    │   │   ├── Infrastructure/             # Реализации (Laravel/Eloquent/Cache/Mail)
    │   │   │   ├── User/
    │   │   │   │   ├── Repositories/       # Реализация интерфейсов
    │   │   │   │   ├── Auth/               # Реализация классов для модуля аутентификации
    │   │   │   │   └── Profile/            # Реализация классов для модуля профиля
    │   │   │   └── Notifications/          # Email-уведомления
    │   │   ├── Http/                       # Controllers
    │   │   ├── Models/                     # Eloquent-модели
    │   │   └── Providers/                  # Провайдеры
    │   ├── config/                         # Конфиги Laravel
    │   ├── database/                       # Миграции, сидеры, фабрики
    │   ├── lang/                           # Локализация (ru/en), в т.ч. exceptions
    │   ├── routes/                         # api.php роутинг
    │   ├── tests/                          # Unit
    │   └── .env / .env.example             # Окружение
    └── 📖 README.md                        # Ты тут :)
```
