# Тестовое задание: Экспорт контактов
Создать решение по экспорту контактов из CRM битрикс24 в xls/csv (Нельзя использовать REST API, интересует решение для коробки)

## Описание логики
- Инициализация класса:
  При создании объекта класса в конструкторе проверяется, что модуль CRM активен. Также создаётся или проверяется
- Получение контактов:
  Метод export использует D7 ORM (в данном случае ContactTable) для выборки всех активных контактов с минимально
- Дополнительные поля (телефоны и email):
  Используется класс FieldMultiTable, чтобы получить телефоны и email для каждого контакта.
- Генерация CSV файла:
  Результаты записываются в CSV файл, строки добавляются по одной.
- Обработка ошибок:
  При возникновении любых ошибок выбрасывается исключение, чтобы разработчик мог быстро найти причину.

## Что экспортируется
- ID контакта;
- Имя, фамилия, отчество;
- Телефон (первый найденный);
- Email (первый найденный);
- Дата создания контакта.

Вы можете легко добавить другие поля в массив select запроса ContactTable::getList или из связанных таблиц.

## Расположение файла
Файл CSV будет сохранён в директории /upload/ под именем contacts_export.csv. Вы можете изменить путь, передав другой аргумент в конструктор класса.

## Установка и управление модулем
Перейдите в админку Битрикс: Настройки -> Модули.
Вы увидите ваш модуль "Модуль для экспорта".
Нажмите "Установить", чтобы его активировать.
