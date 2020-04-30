## Тестовое задание для Timeweb

- Время реализации: ~16 часов
- Задача: прототип конструктора сайтов (например Tilda)

### Настройка

- Установить зависимости через `composer install`
- Настроить конфигурацию хоста `localhost` на папку `public`
    - Настроить параметр `RewriteBase` в `public\.htaccess`
    - При использовании nginx сконфигурировать роутинг по аналогии с `public\.htaccess`
- Разрешить чтение и запись в папку `user_data`
- Открыть в браузере `http://localhost/index.php` для отображения пользовательского сайта
- Открыть в браузере `http://localhost/admin.php` для изменения пользовательского сайта

### Ограничения прототипа

- Нет валидации входящих данных от пользователя
- Нет авторизации, работа от лица авторизованного пользователя
- Нет контроллеров и какой-либо оптимизации работы кода
- Настройки пользователя хранятся в папке `user_data` в виде YAML-файлов
- Прототип позволяет управлять только одним сайтом и несколькими страницами

### Сущности конфигурации

- Snippet - маленькая часть шаблона, к примеру меню, кнопка, логотип, параграф, заголовок, изображение и т.п.
- Block - большая часть шаблона, к примеру шапка, контент или подвал; может содержать коллекцию сниппетов
- Template - страница которая состоит из блоков и имеет настройки: цвет текста, цвет фона, заголовок и т.п.
- User - переиспользованная конфигурация сниппета для вывода UI пользователю в админке
- Core - настройки приложения в виде ключ-значение

### Структура конфигурации

Применимо к файлам `configuration/{snippet,block,template,user}/*.yaml`:

```yaml
path: path\in\configuration\directory.yaml      # путь до текущего файла (особенность реализации на файлах)
name: Название конфигурации                     # название конфигурации для пользователя
template: path\in\template\directory.tpl        # путь до HTML шаблона в папке `template`
used_vars:                                      # массив используемых в шаблоне переменные в виде "ключ: значение"
  var: value
  some: '123'
variables:                                      # массив изменяемых пользователем переменных
  - key: name                                   # ключ переменной, он же используется в шаблоне
    caption: Текст                              # название переменной для пользователя
    description: Будет использован в логотипе   # описание переменной для пользователя
    type: text                                  # тип переменной, в зависимости от значения выводится соответствующий input
    validation: text                            # как валидировать значение от пользователя (text, integer, регулярное выражение)
    default: Очешуенный сайт                    # значение по умолчанию
    placeholder: Текст для логотипа             # placeholder для input-а
    prefix: '$'                                 # префикс для поля ввода
    suffix: 'руб'                               # суффикс для поля ввода
    required: true                              # флаг обязательности заполнения значения
    limit_min: 0                                # минимальное значение (если validation=integer)
    limit_max: 100                              # максимальное значение (если validation=integer)
blocks:                                         # массив используемых блоков в шаблоне
  ...                                           # рекурсивная структура по аналогии с эти файлом конфигурации
snippets:                                       # задействованные сниппеты в блоках
  - key: snippet_logotype                       # ключ сниппета, он же используется в шаблоне *.tpl
    name: Логотип                               # название сниппета отображаемое пользователю
    variants:                                   # массив возможных сниппетов, путь до конфигурационного файла от папки configuration
      - snippet\basic\pseudo_image.yaml
      - snippet\basic\header.yaml
    required: true                              # флаг обязательности использования сниппета в блоке
```
