# 🎨 Цвета для вывода
RESET = \033[0m
BOLD = \033[1m

# Цвет текста
BLACK = \033[30m
RED = \033[31m
GREEN = \033[32m
YELLOW = \033[33m
BLUE = \033[34m
MAGENTA = \033[35m
CYAN = \033[36m
WHITE = \033[37m

# Конфигурация
APP = skillcraft_back
PHP = docker exec -it $(APP)
ARTISAN = $(PHP) php artisan

# 🚀 Полная установка проекта с нуля
setup:
	@echo "$(BOLD)$(CYAN)━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━$(RESET)"
	@echo "$(BOLD)$(BG_BLUE)$(WHITE)  🚀 SkillCraft Backend — полная установка  $(RESET)"
	@echo "$(BOLD)$(CYAN)━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━$(RESET)"
	make env
	make build
	make key
	make migrate
	@echo "$(BOLD)$(GREEN)━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━$(RESET)"
	@echo "$(BOLD)$(BG_GREEN)$(WHITE)  ✅ Установка завершена! Проект готов к работе  $(RESET)"
	@echo "$(BOLD)$(GREEN)━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━$(RESET)"

# 1️⃣ Создание .env файла
env:
	@echo "$(BOLD)$(YELLOW)━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━$(RESET)"
	@echo "$(BOLD)$(BG_YELLOW)$(WHITE)  📄 Проверка .env файла  $(RESET)"
	@if [ ! -f src/.env ]; then \
		echo "$(GREEN)  ✅ Создание .env из .env.example...$(RESET)"; \
		cp src/.env.example src/.env; \
		echo "$(BOLD)$(GREEN)  ✅ .env успешно создан!$(RESET)"; \
	else \
		echo "$(YELLOW)  ⚠️ .env уже существует, пропускаем...$(RESET)"; \
	fi
	@echo "$(BOLD)$(YELLOW)━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━$(RESET)\n"

# 2️⃣ Сборка и запуск контейнеров
build:
	@echo "$(BOLD)$(BLUE)━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━$(RESET)"
	@echo "$(BOLD)$(BG_BLUE)$(WHITE)  🐳 Сборка Docker-контейнеров  $(RESET)"
	@echo "$(BLUE)  → Запуск docker-compose up -d --build...$(RESET)"
	docker-compose up -d --build
	@echo "$(GREEN)  ✅ Контейнеры успешно собраны и запущены!$(RESET)"
	@echo "$(BOLD)$(BLUE)━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━$(RESET)\n"
	make composer-install

# 3️⃣ Генерация ключа приложения
key:
	@echo "$(BOLD)$(MAGENTA)━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━$(RESET)"
	@echo "$(BOLD)$(BG_MAGENTA)$(WHITE)  🔑 Генерация ключа приложения  $(RESET)"
	$(ARTISAN) key:generate
	@echo "$(GREEN)  ✅ Ключ приложения успешно сгенерирован!$(RESET)"
	@echo "$(BOLD)$(MAGENTA)━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━$(RESET)\n"

# 4️⃣ Установка Composer-зависимостей
composer-install:
	@echo "$(BOLD)$(CYAN)━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━$(RESET)"
	@echo "$(BOLD)$(BG_CYAN)$(WHITE)  📦 Установка Composer-зависимостей  $(RESET)"
	@echo "$(CYAN)  → Запуск composer install...$(RESET)"
	$(PHP) composer install
	@echo "$(GREEN)  ✅ Composer-зависимости установлены!$(RESET)"
	@echo "$(BOLD)$(CYAN)━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━$(RESET)\n"

# 5️⃣ Запуск миграций
migrate:
	@echo "$(BOLD)$(GREEN)━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━$(RESET)"
	@echo "$(BOLD)$(BG_GREEN)$(WHITE)  🗄️ Запуск миграций базы данных  $(RESET)"
	@echo "$(GREEN)  → Запуск php artisan migrate...$(RESET)"
	$(ARTISAN) migrate
	@echo "$(GREEN)  ✅ Миграции успешно выполнены!$(RESET)"
	@echo "$(BOLD)$(GREEN)━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━$(RESET)\n"

# 🔄 Полный сброс и переустановка
fresh:
	@echo "$(BOLD)$(RED)━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━$(RESET)"
	@echo "$(BOLD)$(BG_RED)$(WHITE)  ♻️ Полный сброс проекта  $(RESET)"
	@echo "$(RED)  → Остановка и удаление контейнеров с томами...$(RESET)"
	docker-compose down -v
	@echo "$(GREEN)  ✅ Контейнеры удалены$(RESET)"
	@echo "$(BOLD)$(RED)━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━$(RESET)\n"
	make setup

# 📦 Запуск Composer команд
composer:
	@echo "$(CYAN)  📦 Запуск composer $(filter-out $@,$(MAKECMDGOALS))...$(RESET)"
	$(PHP) composer $(filter-out $@,$(MAKECMDGOALS))

# 🛠️ Запуск artisan команд
artisan:
	@echo "$(MAGENTA)  🛠️ Запуск artisan $(filter-out $@,$(MAKECMDGOALS))...$(RESET)"
	$(ARTISAN) $(filter-out $@,$(MAKECMDGOALS))

# 📊 Логи контейнера
logs:
	@echo "$(YELLOW)  📊 Просмотр логов контейнера $(APP)...$(RESET)"
	docker logs $(APP) -f

# 🛑 Остановка контейнеров
down:
	@echo "$(YELLOW)  🛑 Остановка контейнеров...$(RESET)"
	docker-compose down
	@echo "$(GREEN)  ✅ Контейнеры остановлены$(RESET)"

# ▶️ Запуск контейнеров
up:
	@echo "$(GREEN)  ▶️ Запуск контейнеров...$(RESET)"
	docker-compose up -d
	@echo "$(GREEN)  ✅ Контейнеры запущены$(RESET)"

# 🔄 Перезапуск контейнеров
restart:
	@echo "$(YELLOW)  🔄 Перезапуск контейнеров...$(RESET)"
	docker-compose restart
	@echo "$(GREEN)  ✅ Контейнеры перезапущены$(RESET)"

# ❓ Помощь
help:
	@echo "$(BOLD)$(CYAN)━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━$(RESET)"
	@echo "$(BOLD)$(BG_CYAN)$(WHITE)  📋 SkillCraft — доступные команды  $(RESET)"
	@echo "$(BOLD)$(CYAN)━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━$(RESET)"
	@echo "$(BOLD)$(GREEN)  Установка и настройка:$(RESET)"
	@echo "    $(CYAN)make setup$(RESET)         - Полная установка проекта"
	@echo "    $(CYAN)make env$(RESET)           - Создать .env файл"
	@echo "    $(CYAN)make build$(RESET)         - Собрать контейнеры"
	@echo "    $(CYAN)make key$(RESET)           - Сгенерировать ключ"
	@echo "    $(CYAN)make migrate$(RESET)       - Запустить миграции"
	@echo "    $(CYAN)make fresh$(RESET)         - Полный сброс"
	@echo ""
	@echo "$(BOLD)$(YELLOW)  Управление контейнерами:$(RESET)"
	@echo "    $(CYAN)make up$(RESET)            - Запустить контейнеры"
	@echo "    $(CYAN)make down$(RESET)          - Остановить контейнеры"
	@echo "    $(CYAN)make restart$(RESET)       - Перезапустить контейнеры"
	@echo "    $(CYAN)make logs$(RESET)          - Просмотр логов"
	@echo ""
	@echo "$(BOLD)$(MAGENTA)  Утилиты:$(RESET)"
	@echo "    $(CYAN)make composer ...$(RESET)  - Composer команды"
	@echo "    $(CYAN)make artisan ...$(RESET)   - Artisan команды"
	@echo "$(BOLD)$(CYAN)━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━$(RESET)\n"

# Для поддержки аргументов в composer и artisan
%:
	@: