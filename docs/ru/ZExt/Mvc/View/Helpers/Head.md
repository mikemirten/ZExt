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
Head setBaseStaticUrl(string $url)
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

*Все элементы имеют действие по умолчанию при обращении к ним как к свойству помощника "Head"*

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

Добавить значение в конец. **Действие по умолчанию.**

```java
ElementTitle prependTitle(string $title)
```

Добавить значение в начало.


```java
ElementTitle resetTitle()
```

Удалить все заданные значения.

```java
ElementTitle setTitleDelimiter(string $delimiter)
```

Задать разделитель.

```java
string getTitleDelimiter()
```

Получить разделитель.

```java
string getTitle()
```

Получить конечное значение разделённое заданным разделителем.

```java
array getTitleRaw()
```

Получить список значений.


###Encoding

Элемент формирующий тег передающий кодировку страницы.

```php
$this->head->encoding = 'UTF-8';
```

```html
<meta charset="UTF-8" />
```

**Методы**

```java
ElementEncoding setEncoding(string $encoding)
```

Задать кодировку. **Действие по умолчанию.**

```java
string getEncoding();
```

Получить кодировку.

###Keywords

Элемент формирующий тэг передающий ключевые слова страницы.

```php
$this->head->keywords = 'keyword1, keyword2, keyword3';

// Или
$this->head->keywords = ['keyword1', 'keyword2', 'keyword3'];
```

```html
<meta name="keywords" content="keyword1,keyword2,keyword3" />
```

**Методы**

```java
ElementKeywords setKeywords(string | array $keywords)
```

Задать ключевые слова вместо текущих.

```java
ElementKeywords addKeywords(string | array $keywords)
```

Добавит ключевые слова к текущим. **Действие по умолчанию.**

```java
ElementKeywords addKeyword(string $keyword)
```

Добавить ключевое слово.

```java
ElementKeywords resetKeywords()
```

Удалить текущие ключевые слова.

```java
string getKeywords()
```

Получить текущие ключевые слова в виде строки.

```java
array getKeywordsRaw()
```

Получить текущие ключевые слова в виде массива.

###Description

Элемент отвечает за тег передающий описание к странице.

```php
$this->head->description = 'Some description for my application';
```

```html
<meta name="description" content="Some description for my application" />
```

**Методы**

```java
ElementDescription setDescription(string $description)
```

Задать описание. **Действие по умолчанию.**

```java
string getDescription()
```

Получить описание.

###Resources (Script, Style)

*Элементы "script" и "style" имеют одинаковые принцип работы и набор методов*

Элементы отвечают за теги передающие информацию о скриптах и стилях

```php
$this->head->setBaseStaticUrl('http://mydomain.com/static');

$this->head->style = 'bootstrap/bootstrap.min.css';
$this->head->style = 'main.css';

$this->head->script = 'jquery/jquery.min.js';
$this->head->script = 'bootstrap/bootstrap.min.js';
$this->head->script = 'myapp.js';
```

```html
<link rel="stylesheet" type="text/css" href="http://mydomain.com/static/bootstrap/bootstrap.min.css"></link>
<link rel="stylesheet" type="text/css" href="http://mydomain.com/static/main.css"></link>
<script type="text/javascript" src="http://mydomain.com/static/jquery/jquery.min.js"></script>
<script type="text/javascript" src="http://mydomain.com/static/bootstrap/bootstrap.min.js"></script>
<script type="text/javascript" src="http://mydomain.com/static/myapp.js"></script>
```

При передачи полного URL ресурса, заданные базовые URL и путь не будут использованы:

```php
$this->head->setBaseStaticUrl('http://mydomain.com/static');

$this->head->script = 'http://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js';
```

```html
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>
```

**Методы**

```java
ElementAbstract setBasePath(string $path)
```

Задать базовый путь для ресурсов.

```java
ElementAbstract setBaseUrl(string $url)
```

Задать базовый URL для ресурсов.

```java
ElementAbstract setHashAppend(boolean $enable = true)
```

Задать флаг требующий добавлять хеш содержимого ресурса к его URL. 
По умолчанию хеширование выключено.

```java
boolean isHashAppend()
```

Возвращает состояние флага о потребности в добавлении хеша к URL ресурса.

```java
Resource appendResource(string | Resource $resource)
```

Добавить ресурс. **Действие по умолчанию.**

```java
Resource prependResource(string | Resource $resource)
```

Добавить ресурс в начало.

```java
ElementAbstract resetResources()
```

Удалить текущие ресурсы.

```java
setResources(array $resources)
```

Задать ресурсы вместо текущих.

```java
Package appendPackage(string | Package $package)
```

Добавить пакет ресурсов

```java
Package prependPackage(string | Package $package)
```

Добавить пакет ресурсов в конец

```java
ElementAbstract resetPackages()
```

Удалтить текущие пакеты ресурсов
