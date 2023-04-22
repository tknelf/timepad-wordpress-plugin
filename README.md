# TimePad Events (plugin for WordPress)

There are some changes and fixes to the original "timepad-wordpress-plugin" to make it work in WordPress v6.1.1 .

Main changes:
- Removed OAuth authorization (which was not working and was insecure because of redirect to "tpwprdr.tpdl.ru").
Now you only have to enter timepad auth token in the form to initialize the plugin.

- Synchronization now tries to load a few pages at one call, not all of them.
This change is important if you have many hundreds of events. The original version couldn't process that many events at one call and does not work correctly.


Important note.

In case of a recurring event, a call "/events.json" to API of timepad.ru return a cover image only to the first event in the serie. The support of timepad.ru answered me that it is how it works right now and they have no advice on how to get cover images of every event.

---

# TimePad Events (плагин для WordPress)

Здесь несколько изменений и исправлений в оригинальном плагине "timepad-wordpress-plugin", чтобы он работал в WordPress v6.1.1 .

Основные изменения:

- Удалена авторизация OAuth (она не работала и была небезопасна из-за перехода на сайт "tpwprdr.tpdl.ru")
Сейчас для инициализации плагина вам достаточно просто ввести авторизационный токен от API timepad.ru.

- Синхронизация теперь пытается загрузить несколько страниц за один запуск, а не все
Это изменение важно, если у вас очень много событий (сотни-тысячи). Оригинальный плагин не может обработать столько событий за один вызов.


Важная заметка.

В случае повторяющегося события, вызов "/events.json" в API timepad.ru возвращает картинку-обложку только для первого события из серии. Поддержка timepad.ru ответила мне, что так сейчас работает их API, и они не могут посоветовать как получить картинки для каждого события.
