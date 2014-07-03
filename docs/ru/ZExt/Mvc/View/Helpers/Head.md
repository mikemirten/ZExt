# Head

Помощник "Head" отвечает за создание тегов в секции "head" html-документа.

Пример простого использования:

*В контроллере:*
```php
$this->head->encoding    = 'UTF-8';
$this->head->title       = 'Main page of my application';
$this->head->keywords    = 'PHP, application';
$this->head->description = 'Some description for my application';

$this->head->style  = 'main.css';
$this->head->script = 'myapp.js';
```

*Во view-скрипте:*
```php
<head>
  <?= $this->head() ?>
</head>
```

*Результат:*
```html
<head>
  <meta charset="UTF-8" />
  <title>Main page of my application</title>
  <meta name="keywords" content="PHP,application" />
  <meta name="description" content="Some description for my application" />
  <link rel="stylesheet" type="text/css" href="/main.css"></link>
  <script type="text/javascript" src="/myapp.js"></script>
</head>
```

## Базовые методы

```java
void setBaseStaticUrl(string $url)
```

Задаёт базовый URL для всех статических ресурсов, т.е. скриптов, стилей, по умолчанию.

*В контроллере:*
```php
$this->head->setBaseStaticUrl('http://mydomain.com/static');

$this->head->style  = 'main.css';
$this->head->script = 'myapp.js';
```

*Результат:*
```html
<link rel="stylesheet" type="text/css" href="http://mydomain.com/static/main.css"></link>
<script type="text/javascript" src="http://mydomain.com/static/myapp.js"></script>
```

```java
string getBaseStaticUrl()
```

Возвращает заданный базовый URL для статических ресурсов.


```java
Head setBaseStaticPath(string $path)
```

Задаёт базовый путь для всех статических ресурсов на сервере.

```java
string getBaseStaticPath()
```

Возвращает заданный базовый путь для статических ресурсов на сервере.

```java
Head setStaticHashAppend(boolean $enable = true)
```

Задаёт флаг требующий добавлять текущий хеш содержимого статического ресурса к его URL. 
Может быть полезным для принудительного обновления содержимого ресурса браузером. 
По умолчанию хеширование выключено.

```java
boolean isStaticHashAppend()
```

Возвращает состояние флага о потребности в добавлении хеша к URL статического ресурса

```java
Head setMetadataManager(MetadataManagerInterface $manager);
```

Задаёт менеджер метаданных, хранящий информацию о статических файлах. По умолчанию используется идущий в составе помощника.

```java
MetadataManagerInterface getMetadataManager()
```

Возвращает менеджер метаданных, хранящий информацию о статических файлах.

```java
string render()
```

Возвращает готовый HTML-код на основе заданных параметров.

```java
ElementInterface getElement(string $name)
```

Возвращает элемент хелпера ответственный за определённую часть HTML-кода.

**К элементу можно так же обратиться как к свойству объекта**

## Элементы

###Title

Элемент формирующий тэг "title". Элемент может собирать конечное значение тэга из ряда переданных значений разделённых заданным разделителем.

```php
$this->head->title->setTitleDelimiter(' :: ')->setTitle('My application');

$this->head->title->appendTitle('Control panel');
$this->head->title->appendTitle('Users');

// Можно так же добавлять значения через обращение к свойству:
$this->head->title = 'Control panel';
$this->head->title = 'Users';
```

```html
<title>My application :: Control panel :: Users</title>
```

**Методы**

```java
ElementTitle setTitle(string $title)
```

Задать значение вместо текущих.

```java
ElementTitle appendTitle(string $title)
```

Добавить значение в конец

```java
ElementTitle prependTitle(string $title)
```

Добавить значение в начало


```java
ElementTitle resetTitle()
```

Удалить все заданные значения

```java
ElementTitle setTitleDelimiter(string $delimiter)
```

Задать разделитель

```java
string getTitleDelimiter()
```

Получить разделитель

```java
string getTitle()
```

Получить конечное значение разделённое заданным разделителем

```java
array getTitleRaw()
```

Получить список значений

```java
boolean isEmpty()
```

Проверить пустой ли элемент

```java
string assemble()
```

Собрать конечный тег со значениями

