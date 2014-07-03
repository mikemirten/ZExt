# Head

Помощник "Head" отвечает за создание тегов в секции "head" html-документа.

Пример простого использования:

В контроллере:
```php
$this->head->encoding    = 'UTF-8';
$this->head->title       = 'Main page of my application';
$this->head->keywords    = 'PHP, application';
$this->head->description = 'Some description for my application';

$this->head->style  = 'main.css';
$this->head->script = 'myapp.js';
```

Во view-скрипте:
```php
<head>
  <?= $this->head() ?>
</head>
```

Результат:
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
