/*
 * Симулятор боевой системы браузерной игры OGame.
 * (c) 2009, Andorianin, OGame.ru Team.
*/

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <time.h>
#include "sim.h"
#include "loca.h"
#include "report084.h"
#include "reportred.h"
#include "reportxml.h"
#include "reportdebug.h"

/*
Спецификация передаваемых параметров боевого движка SpecSim:

Парараметры передаются стандартными средствами - через ?. Каждый параметр разделяется символом &.
Список параметров:
 m              Ресурсы на планете (металл)
 k              Ресурсы на планете (кристалл)
 d              Ресурсы на планете (дейтерий)
 anum           Количество слотов атакующего. Слоты нумеруются от 0 до anum-1. Количество атакующих не более 16.
 dnum           Количество слотов обороняющегося. Первый слот (номер 0) для самого обороняющегося, остальные для флотов находящихся на удержании.
                Слоты нумеруются от 0 до dnum-1. Количество обороняющихся не более 16.
 aN_weap        Технологии атакующего N (Уровень оружейной технологии)
 aN_shld        Технологии атакующего N (Уровень щитовой технологии)
 aN_hull        Технологии атакующего N (Уровень брони космических кораблей)
 dN_weap        Технологии обороняющегося N (Уровень оружейной технологии)
 dN_shld        Технологии обороняющегося N (Уровень щитовой технологии)
 dN_hull        Технологии обороняющегося N (Уровень брони космических кораблей)
 aN_name        Имя атакующего N (юникод)
 dN_name        Имя обороняющегося N (юникод)
 aN_g           Координаты атакующего N (галактика)
 aN_s           Координаты атакующего N (система)
 aN_p           Координаты атакующего N (планета)
 dN_g           Координаты обороняющегося N (галактика)
 dN_s           Координаты обороняющегося N (система)
 dN_p           Координаты обороняющегося N (планета)
 aN_fX          Количество флота класса X, у атакующего N (нумерацию флота смотри ниже)
 dN_fX          Количество флота класса X, у обороняющегося N (нумерацию флота смотри ниже)
 d_dX           Количество обороны класса X, у обороняющегося номер 0  (нумерацию обороны смотри ниже).
 did            Количество обороны в обломки (в процентах, 0 - отключено, если не указано, то принимается за 0)
 fid            Количество флота в обломки (в процентах, 0 - отключено, если не указано, то принимается за 30)
 rf             1: Включить скорострел, 0: отключить скорострел (если не указано, принимается за 1)
 gen            Схема работы симулятора (084: OGame 0.84, red: OGame 1.0+, xml: XML, debug: Отадочная информация, short: Краткий доклад)
 lang           Язык вывода докладов ISO-639-1 (если не указано, то "en")

 Обзозначение флота и обороны (нумерация):
 0: Малый транспорт, 1: Большой транспорт, 2: Лёгкий истребитель, 3: Тяжёлый истребитель, 
 4: Крейсер, 5: Линкор, 6: Колонизатор, 7: Переработчик, 8: Шпионский зонд, 
 9: Бомбардировщик, 10: Солнечный спутник, 11: Уничтожитель, 12: Звезда смерти, 13: Линейный крейсер,
 0: Ракетная установка, 1: Лёгкий лазер, 2: Тяжёлый лазер, 3: Пушка Гаусса, 4: Ионное орудие, 
 5: Плазменное орудие, 6: Малый щитовой купол, 7: Большой щитовой купол.
*/

typedef struct SimParam {
    char    name[32];
    char    string[64];
    unsigned long value;
} SimParam;
static SimParam *simargv;
static long simargc = 0;

// Преобразовать строку вида %EF%F0%E8%E2%E5%F2 в байтовую строку.
static void hexize (char *string)
{
    int hexnum;
    char *temp, c, *oldstring = string;
    long length = (long)strlen (string), p = 0, digit = 0;
    temp = (char *)malloc (length + 1);
    if (temp == NULL) return;
    while (length--) {
        c = *string++;
        if (c == 0) break;
        if (c == '%') { 
            digit = 1;
        }
        else {
            if (digit == 1) {
                if (c <= '9') hexnum = (c - '0') << 4;
                else hexnum = (10 +(c - 'A')) << 4;
                digit = 2;
            }
            else if (digit == 2) {
                if (c <= '9') hexnum |= (c - '0');
                else hexnum |= 10 + (c - 'A');
                temp[p++] = (unsigned char)hexnum;
                digit = 0;
            }
            else temp[p++] = c;
        }
    }
    temp[p++] = 0;
    memcpy (oldstring, temp, p);
    free (temp);
}

static void AddSimParam (char *name, char *string)
{
    long i;

    // Проверить, если такой параметр уже существует, просто обновить его значение.
    for (i=0; i<simargc; i++) {
        if (!strcmp (name, simargv[i].name)) {
            strncpy (simargv[i].string, string, sizeof(simargv[i].string));
            simargv[i].value = strtoul (simargv[i].string, NULL, 10);
            return;
        }
    }

    // Выделить место под новый параметр и записать значения.
    hexize (string);
    simargv = (SimParam *)realloc (simargv, (simargc + 1) * sizeof (SimParam) );
    strncpy (simargv[simargc].name, name, sizeof (simargv[simargc].name) );
    strncpy (simargv[simargc].string, string, sizeof (simargv[simargc].string) );
    simargv[simargc].value = strtoul (simargv[simargc].string, NULL, 10);
    simargc ++;
}

static void PrintSimParams (void)
{
    long i;
    SimParam *p;
    for (i=0; i<simargc; i++) {
        p = &simargv[i];
        printf ( loca[LOCD_SIMARG], i, p->name, p->string, p->value );
    }
    printf ("<hr/>");
}

// Разобрать параметры.
static void ParseQueryString (char *str)
{
    int collectname = 1;
    char namebuffer[100], stringbuffer[100], c;
    long length, namelen = 0, stringlen = 0;
    memset (namebuffer, 0, sizeof(namebuffer));
    memset (stringbuffer, 0, sizeof(stringbuffer));
    if (str == NULL) return;
    length = (long)strlen (str);
    while (length--) {
        c = *str++;
        if ( c == '=' ) {
            collectname = 0;
        }
        else if (c == '&') { // Добавить параметр.
            collectname = 1;
            if (namelen >0 && stringlen > 0) {
                AddSimParam (namebuffer, stringbuffer);
            }
            memset (namebuffer, 0, sizeof(namebuffer));
            memset (stringbuffer, 0, sizeof(stringbuffer));
            namelen = stringlen = 0;
        }
        else {
            if (collectname) {
                if (namelen < 31) namebuffer[namelen++] = c;
            }
            else {
                if (stringlen < 63) stringbuffer[stringlen++] = c;
            }
        }
    }
    // Добавить последний параметр.
    if (namelen > 0 && stringlen > 0) AddSimParam (namebuffer, stringbuffer);
}

static SimParam *ParamLookup (char *name)
{
    SimParam *p = NULL;
    long i;
    for (i=0; i<simargc; i++) {
        if (!strcmp (simargv[i].name, name)) return &simargv[i];
    }
    return p;
}

static int GetSimParamI (char *name, int def)
{
    SimParam *p = ParamLookup (name);
    if (p == NULL) return def;
    else return p->value;
}

static char *GetSimParamS (char *name, char *def)
{
    SimParam *p = ParamLookup (name);
    if (p == NULL) return def;
    else return p->string;
}

void main(int argc, char **argv)
{
    char temp[200], temp2[200], *gen;
    Slot *a, *d;
    int anum, dnum, i, id, simnum, simcase;
    void (*SimReport)(BattleState *bst, Slot *a, int anum, Slot *d, int dnum);
    BattleState *bst;
    u64 met, crys, deut;

    ParseQueryString (getenv ("QUERY_STRING"));
    
    // Установить язык.
    InitLoca ( GetSimParamS ("lang", "en") );    

    // Получить количество атакеров и дефов.
    anum = GetSimParamI ("anum", 0);
    dnum = GetSimParamI ("dnum", 0);
    if (anum == 0 || dnum == 0) {
        printf("Content-type: text/html\n");
        printf("Pragma: no-cache\n");
        printf("\n");
        printf ( "<html>\n<HEAD>\n<meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\" />\n</HEAD>\n<BODY>\n");
        printf (loca[LOCD_NOBATTLE]);
        printf ("</BODY></html>");
        return;
    }
    if (anum < 0) anum = 0;
    if (dnum < 0) dnum = 0;
    if (anum > 16) anum = 16;
    if (dnum > 16) dnum = 16;

    // Получить флоты, оборону и параметры атакеров и дефов.
    a = (Slot *)malloc (anum * sizeof (Slot));
    d = (Slot *)malloc (dnum * sizeof (Slot));
    memset (a, 0, anum * sizeof (Slot));
    memset (d, 0, dnum * sizeof (Slot));

    for (i=0; i<anum; i++) {    // Атакеры.
        sprintf (temp, "a%i_name", i);
        sprintf (temp2, "Attacker%i", i+1);
        strcpy (a[i].name, GetSimParamS (temp, temp2));
        sprintf (temp, "a%i_g", i);    a[i].g = GetSimParamI (temp, 1); a[i].g &= 0xfff;
        sprintf (temp, "a%i_s", i);    a[i].s = GetSimParamI (temp, 2); a[i].s &= 0xfff;
        sprintf (temp, "a%i_p", i);    a[i].p = GetSimParamI (temp, 3); a[i].p &= 0xfff;
        sprintf (temp, "a%i_weap", i); a[i].weap = GetSimParamI (temp, 0); a[i].weap &= 0xff;
        sprintf (temp, "a%i_shld", i); a[i].shld = GetSimParamI (temp, 0); a[i].shld &= 0xff;
        sprintf (temp, "a%i_hull", i); a[i].armor = GetSimParamI (temp, 0); a[i].armor &= 0xff;
        for (id=0; id<14; id++) {
            sprintf (temp, "a%i_f%i", i, id);
            a[i].fleet[id] = GetSimParamI (temp, 0);
            if (a[i].fleet[id] < 0) a[i].fleet[id] = 0;
            a[i].fleet[10] = 0;     // Солнечные спутники у атакеров.
        }
    }

    for (i=0; i<dnum; i++) {    // Дефы.
        sprintf (temp, "d%i_name", i);
        sprintf (temp2, "Defender%i", i+1);
        strcpy (d[i].name, GetSimParamS (temp, temp2));
        sprintf (temp, "d%i_g", i);    d[i].g = GetSimParamI (temp, 1); d[i].g &= 0xfff;
        sprintf (temp, "d%i_s", i);    d[i].s = GetSimParamI (temp, 2); d[i].s &= 0xfff;
        sprintf (temp, "d%i_p", i);    d[i].p = GetSimParamI (temp, 3); d[i].p &= 0xfff;
        sprintf (temp, "d%i_weap", i); d[i].weap = GetSimParamI (temp, 0); d[i].weap &= 0xff;
        sprintf (temp, "d%i_shld", i); d[i].shld = GetSimParamI (temp, 0); d[i].shld &= 0xff;
        sprintf (temp, "d%i_hull", i); d[i].armor = GetSimParamI (temp, 0); d[i].armor &= 0xff;
        for (id=0; id<14; id++) {
            sprintf (temp, "d%i_f%i", i, id);
            d[i].fleet[id] = GetSimParamI (temp, 0);
            if (d[i].fleet[id] < 0) d[i].fleet[id] = 0;
        }
        if (i == 0) {   // Только для обороняющегося номер 0.
            for (id=0; id<8; id++) {
                sprintf (temp, "d_d%i", id);
                d[i].def[id] = GetSimParamI (temp, 0);
                if (d[i].def[id] < 0) d[i].def[id] = 0;
            }
            if (d[0].def[6] > 1) d[0].def[6] = 1;   // Купола можно строить не более 1 ед.
            if (d[0].def[7] > 1) d[0].def[7] = 1;
        }
        else d[i].fleet[10] = 0;     // Солнечные спутники у дефов на удержании.
    }

    // Настройки симулятора.
    SetDebrisOptions ( GetSimParamI ("did", 0), GetSimParamI ("fid", 30) );
    SetRapidfire ( GetSimParamI ("rf", 1) );

    gen = GetSimParamS ("gen", "short");
    if (!strcmp (gen, "short")) SimReport = OGameRedReport;
    else if (!strcmp (gen, "084")) SimReport = OGame084Report;
    else if (!strcmp (gen, "red")) SimReport = OGameRedReport;
    else if (!strcmp (gen, "xml")) SimReport = XMLReport;
    else if (!strcmp (gen, "debug")) SimReport = DebugReport;
    else SimReport = OGameRedReport;

    // Симулировать.
    SimSrand ((unsigned long)time(NULL));
    met = GetSimParamI ("m", 0);
    crys = GetSimParamI ("k", 0);
    deut = GetSimParamI ("d", 0);
    
    bst = SimulateBattle (a, anum, d, dnum, met, crys, deut);
    SimReport (bst, a, anum, d, dnum);
    CleanupBattle (bst);
}