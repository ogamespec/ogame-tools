// Строки для локализации.
// -- Используйте редактор для юникода. -- Use unicode editor.
#include <string.h>
#include "loca.h"

// Отладочные строки.
//#define LOCD_SIMARG "Параметр %i: %s = %s (%i как целое)<br/>"
//#define LOCD_NOBATTLE "Бой не состоялся, потому что не хватает атакующего или обороняющегося.<br/>"
//#define LOCD_REPAIR "%s восстанавливается %i %% обороны. Было %i, уничтожено %i, восстановлено %i<br/>"

char *FleetNames[14];
char *DefenseNames[8];
char *FleetShort[14];
char *DefenseShort[8];
char *SlotCaption[2];
char *loca[LOCA_MAX];

void InitLoca (char *lang)
{
    if ( !strcmp (lang, "ru") ){
        FleetNames[0] = "Малый транспорт";          FleetShort[0] = "М. трансп.";
        FleetNames[1] = "Большой транспорт";        FleetShort[1] = "Б. трансп.";
        FleetNames[2] = "Лёгкий истребитель";       FleetShort[2] = "Л. истр.";
        FleetNames[3] = "Тяжёлый истребитель";      FleetShort[3] = "Т. истр.";
        FleetNames[4] = "Крейсер";                  FleetShort[4] = "Крейсер";
        FleetNames[5] = "Линкор";                   FleetShort[5] = "Линк";
        FleetNames[6] = "Колонизатор";              FleetShort[6] = "Колонизатор";
        FleetNames[7] = "Переработчик";             FleetShort[7] = "Переработчик";
        FleetNames[8] = "Шпионский зонд";           FleetShort[8] = "Шп. зонд";
        FleetNames[9] = "Бомбардировщик";           FleetShort[9] = "Бомб.";
        FleetNames[10] = "Солнечный спутник";       FleetShort[10] = "Солн. спутник";
        FleetNames[11] = "Уничтожитель";            FleetShort[11] = "Уничт.";
        FleetNames[12] = "Звезда смерти";           FleetShort[12] = "ЗС";
        FleetNames[13] = "Линейный крейсер";        FleetShort[13] = "Лин. Кр.";
        DefenseNames[0] = "Ракетная установка";     DefenseShort[0] = "РУ";
        DefenseNames[1] = "Лёгкий лазер";           DefenseShort[1] = "Лёг. лазер";
        DefenseNames[2] = "Тяжёлый лазер";          DefenseShort[2] = "Тяж. лазер";
        DefenseNames[3] = "Пушка Гаусса";           DefenseShort[3] = "Гаусс";
        DefenseNames[4] = "Ионное орудие";          DefenseShort[4] = "Ион";
        DefenseNames[5] = "Плазменное орудие";      DefenseShort[5] = "Плазма";
        DefenseNames[6] = "Малый щитовой купол";    DefenseShort[6] = "М. купол";
        DefenseNames[7] = "Большой щитовой купол";  DefenseShort[7] = "Б. купол";
        SlotCaption[0] = "Обороняющийся";           SlotCaption[1] = "Флот атакующего";

        // Строки для доклада OGame 0.84
        loca[LOCA084_BATTLE_REPORT] = "Боевой доклад";
        loca[LOCA084_DATE_TIME] = "Дата/Время:";
        loca[LOCA084_ENCOUNTER] = "Произошёл бой между следующими флотами:";
        loca[LOCA084_TECHS] = "Вооружение: %i%% Щиты: %i%% Броня: %i%%";
        loca[LOCA084_SLOT_TYPE] = "Тип";
        loca[LOCA084_SLOT_AMOUNT] = "Кол-во.";
        loca[LOCA084_SLOT_WEAP] = "Воор.:";
        loca[LOCA084_SLOT_SHLD] = "Щиты";
        loca[LOCA084_SLOT_HULL] = "Броня";
        loca[LOCA084_SLOT_DESTROYED] = "уничтожен";
        loca[LOCA084_ASHOOT] = "Атакующий флот делает: %s выстрела(ов) общей мощностью %s по обороняющемуся. Щиты обороняющегося поглощают %s мощности выстрелов";
        loca[LOCA084_DSHOOT] = "Обороняющийся флот делает %s выстрела(ов) общей мощностью %s выстрела(ов) по атакующему. Щиты атакующего поглощают %s мощности выстрелов";
        loca[LOCA084_AWON] = "Атакующий выиграл битву!";
        loca[LOCA084_DWON] = "Обороняющийся выиграл битву!";
        loca[LOCA084_DRAW] = "Бой оканчивается вничью, оба флота возвращаются на свои планеты";
        loca[LOCA084_PLUNDER] = "Он получает<br>%s металла, %s кристалла и %s дейтерия.";
        loca[LOCA084_LOSSTATS] = "Атакующий потерял %s единиц.<br>Обороняющийся потерял %s единиц.";
        loca[LOCA084_DEBRIS] = "Теперь на этих пространственных координатах находится %s металла и %s кристалла.";
        loca[LOCA084_MOONCHANCE] = "Шанс появления луны составил %s %% ";
        loca[LOCA084_REPAIRED] = " были повреждены и находятся в ремонте.<br>";

        // Строки для доклада в редизайне.
        loca[LOCARED_BATTLE_REPORT] = "Боевой доклад";
        loca[LOCARED_DATE_TIME] = "Дата/Время:";
        loca[LOCARED_ENCOUNTER] = "Произошёл бой между следующими флотами::";
        loca[LOCARED_TECHS] = "Вооружение: %i%% Щиты: %i%% Броня: %i%%";
        loca[LOCARED_SLOT_TYPE] = "Тип";
        loca[LOCARED_SLOT_AMOUNT] = "Кол-во.";
        loca[LOCARED_SLOT_WEAP] = "Воор.:";
        loca[LOCARED_SLOT_SHLD] = "Щиты";
        loca[LOCARED_SLOT_HULL] = "Броня";
        loca[LOCARED_SLOT_DESTROYED] = "уничтожен.";
        loca[LOCARED_ASHOOT] = "Атакующий флот делает: %s выстрела(ов) общей мощностью %s по обороняющемуся. Щиты обороняющегося поглощают %s мощности выстрелов.";
        loca[LOCARED_DSHOOT] = "Обороняющийся флот делает %s выстрела(ов) общей мощностью %s  выстрела(ов) по атакующему. Щиты атакующего поглощают %s мощности выстрелов.";
        loca[LOCARED_AWON] = "Атакующий выиграл битву!";
        loca[LOCARED_DWON] = "Обороняющийся выиграл битву!";
        loca[LOCARED_DRAW] = "Бой оканчивается вничью, оба флота возвращаются на свои планеты";
        loca[LOCARED_PLUNDER] = "Он получает %s металла, %s кристалла и %s дейтерия.";
        loca[LOCARED_LOSSTATS] = "Атакующий потерял %s единиц.<br />    Обороняющийся потерял %s единиц.";
        loca[LOCARED_DEBRIS] = "Теперь на этих пространственных координатах находится %s металла и %s кристалла.";
        loca[LOCARED_MOONCHANCE] = "Шанс появления луны составил %s %% ";
        loca[LOCARED_REPAIRED] = " были повреждены и находятся в ремонте.<br>";
        
        // Отладка.
        loca[LOCD_NOBATTLE] = "Бой не состоялся, потому что не хватает атакующего или обороняющегося.<br/>";
    }
    else {
        FleetNames[0] = "Small Cargo";          FleetShort[0] = "S.Cargo";
        FleetNames[1] = "Large Cargo";          FleetShort[1] = "L.Cargo";
        FleetNames[2] = "Light Fighter";        FleetShort[2] = "L.Fighter";
        FleetNames[3] = "Heavy Fighter";        FleetShort[3] = "H.Fighter";
        FleetNames[4] = "Cruiser";              FleetShort[4] = "Cruiser";
        FleetNames[5] = "Battleship";           FleetShort[5] = "Battleship";
        FleetNames[6] = "Colony Ship";          FleetShort[6] = "Col.Ship";
        FleetNames[7] = "Recycler";             FleetShort[7] = "Recy.";
        FleetNames[8] = "Espionage Probe";      FleetShort[8] = "Esp.Probe";
        FleetNames[9] = "Bomber";               FleetShort[9] = "Bomber";
        FleetNames[10] = "Solar Satellite";     FleetShort[10] = "Sol. Sat";
        FleetNames[11] = "Destroyer";           FleetShort[11] = "Dest.";
        FleetNames[12] = "Deathstar";           FleetShort[12] = "Deathstar";
        FleetNames[13] = "Battlecruiser";       FleetShort[13] = "Battlecr.";
        DefenseNames[0] = "Rocket Launcher";    DefenseShort[0] = "R.Launcher";
        DefenseNames[1] = "Light Laser";        DefenseShort[1] = "L.Laser";
        DefenseNames[2] = "Heavy Laser";        DefenseShort[2] = "H.Laser";
        DefenseNames[3] = "Gauss Cannon";       DefenseShort[3] = "Gauss";
        DefenseNames[4] = "Ion Cannon";         DefenseShort[4] = "Ion C.";
        DefenseNames[5] = "Plasma Turret";      DefenseShort[5] = "Plasma";
        DefenseNames[6] = "Small Shield Dome";  DefenseShort[6] = "S.Dome";
        DefenseNames[7] = "Large Shield Dome";  DefenseShort[7] = "L.Dome";
        SlotCaption[0] = "Defender";            SlotCaption[1] = "Attacker";
    
        // 0.84 strings.
        loca[LOCA084_BATTLE_REPORT] = "Battle Report";
        loca[LOCA084_DATE_TIME] = "On";
        loca[LOCA084_ENCOUNTER] = "the following fleets met in battle::";
        loca[LOCA084_TECHS] = "Weapons: %i%% Shields: %i%% Armour: %i%%";
        loca[LOCA084_SLOT_TYPE] = "Type";
        loca[LOCA084_SLOT_AMOUNT] = "Total";
        loca[LOCA084_SLOT_WEAP] = "Weapons";
        loca[LOCA084_SLOT_SHLD] = "Shields";
        loca[LOCA084_SLOT_HULL] = "Armour";
        loca[LOCA084_SLOT_DESTROYED] = "destroyed";
        loca[LOCA084_ASHOOT] = "The attacking fleet fires %s times with a total firepower of %s at the defender. The defending shields absorb %s damage";
        loca[LOCA084_DSHOOT] = "In total, the defending fleet fires %s times with a total firepower of %s at the attacker. The attackers shields absorb %s damage";
        loca[LOCA084_AWON] = "The attacker has won the battle!";
        loca[LOCA084_DWON] = "The defender has won the battle!";
        loca[LOCA084_DRAW] = "The battle ended in a draw, both fleets withdraw to their home planets.";
        loca[LOCA084_PLUNDER] = "He captured<br>%s metal, %s crystal, and %s deuterium";
        loca[LOCA084_LOSSTATS] = "The attacker lost a total of %s units.<br>The defender lost a total of %s units.";
        loca[LOCA084_DEBRIS] = "At these space coordinates now float %s metal and %s crystal.";
        loca[LOCA084_MOONCHANCE] = "The chance for a moon to be created is %s %% ";
        loca[LOCA084_REPAIRED] = " could be repaired.<br>";
        
        // Redesign strings.
        loca[LOCARED_BATTLE_REPORT] = "Combat Report";
        loca[LOCARED_DATE_TIME] = "On";
        loca[LOCARED_ENCOUNTER] = "the following fleets met in battle:";
        loca[LOCARED_TECHS] = "Weapons: %i%% Shields: %i%% Armour: %i%%";
        loca[LOCARED_SLOT_TYPE] = "Type";
        loca[LOCARED_SLOT_AMOUNT] = "Total";
        loca[LOCARED_SLOT_WEAP] = "Weapons";
        loca[LOCARED_SLOT_SHLD] = "Shields";
        loca[LOCARED_SLOT_HULL] = "Armour";
        loca[LOCARED_SLOT_DESTROYED] = "destroyed.";
        loca[LOCARED_ASHOOT] = "The attacking fleet fires %s times at the defender, with a total firepower of %s. The defender's shields absorb %s damage points.";
        loca[LOCARED_DSHOOT] = "The defending fleet fires %s times at the attacker, with a total firepower of %s. The attacker's shields absorb %s damage points.";
        loca[LOCARED_AWON] = "The attacker has won the battle! ";
        loca[LOCARED_DWON] = "The defender has won the battle!";
        loca[LOCARED_DRAW] = "The battle ended in a draw, both fleets withdraw to their home planets.";
        loca[LOCARED_PLUNDER] = "He captured %s metal, %s crystal, and %s deuterium.";
        loca[LOCARED_LOSSTATS] = "The attacker lost a total of %s units.<br/>    The defender lost a total of %s units.";
        loca[LOCARED_DEBRIS] = "At these space coordinates now float %s metal and %s crystal.";
        loca[LOCARED_MOONCHANCE] = "The chance for a moon to be created is %s %% ";
        loca[LOCARED_REPAIRED] = " could be repaired.<br>";
        
        // Отладка.
        loca[LOCD_NOBATTLE] = "Battle cannot start. There is no attacker or defender.<br/>";
    }
}