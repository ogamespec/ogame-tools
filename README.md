# ogame-tools

Набор утилит для браузерной игры OGame, написанные мною в период 2008-2010, которые размещались на сайте http://ogamespec.com (сейчас сайт уже недоступен)

Стиль кода PHP у меня очень нубский, на базе процедурного программирования :-)

Возможно кое-где не хватает каких-то ресурсов (картинок, CSS и пр.), со временем перетащу из аккаунта на хостинге.

Evolution skin and Redesign background image, (c) Gameforge AG

## Состав директорий

- evolution: Оригинальный скин OGame 0.84
- redesign_css: CSS из редизайна (используются в генераторе боевых докладов)
- redesign_img: Картинки из редизайна (используются в генераторе боевых докладов)
- specsim: Ядро боевого симулятора, написанное на C
- tools: Собственно скрипты

## Скрипты

- capture: Подсчет добычи
- com: Командир
- empire: Империя
- mrakobes: Яйцехват (уже не помню для чего использовалась)
- ogstat: Разбор статистики
- phalanx: Скан фаланги
- ping: Опрос серверов OGame
- plunder: Захват добычи
- prices: Расчет стоимости
- queue: Симулятор строительства
- ripper: Программа для слежения за неактивными игроками в помощь Звездоводам (наиболее крутая утилита, imho) -- перенесена в отдельный репозиторий.
- salans: Как мы следили за онлайном игрока (может использоваться как шаблон для слежки актива)
- skins: Просмотрщик скинов
- specsim: Онлайн симулятор боя
- spy: Шпионский доклад
- trade: Транспортный Терминал

Скрипты используют MySQL, конфиги находятся в файлах xxx_config.php. Доступ к SQL через скрипт db.php

PS. Скрипты содержат дыры, я это знаю, так как на хостинге сайта ogamespec.com до сих пор хранятся всякие PHP-Shell и прочая малварь. Но меня это не парит :-)

Enjoy!
