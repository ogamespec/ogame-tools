// Генератор докладов OGame 0.84
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <time.h>
#include "sim.h"
#include "loca.h"

// Форматирование числа по тысячам. Спасибо Бонтчеву :)
// Функция является non-reentrant. Это означает что её нельзя использовать несколько раз в одном выражении, 
// потому что она всегда возвращает статический адрес, поэтому все значения будут одинаковыми.
// Для этого нужно копировать результат работы во временные буферы.
static char *nicenum (u64 n)
{
	static char retbuf [32];
	char *p = &retbuf [sizeof (retbuf) - 1];
	int i = 0;

    if (n == 0) return "0";
	*p = '\0';
	for (i = 0; n; i++)
	{
		if (((i % 3) == 0) && (i != 0))
			*--p = '.';
		*--p = '0' + n % 10;
		n /= 10;
	}
	return p;
}

// Сгенерировать HTML-код слота.
// Если techs = 1, то показать технологии (в раундах технологии показывать не надо).
static void GenSlot (Unit *units, int slot, int objnum, Slot *a, Slot *d, int attacker, int techs)
{
    Slot *s = attacker ? a : d;
    Unit *u;
    Slot coll;
    int n, i;
    unsigned long sum = 0;

    memset (&coll, 0, sizeof(Slot));
    coll.weap = s[slot].weap;
    coll.shld = s[slot].shld;
    coll.armor = s[slot].armor;

    // Собрать всё в один слот.
    if (techs) {
        for (i=0; i<14; i++) { coll.fleet[i] = s[slot].fleet[i]; sum += s[slot].fleet[i]; }
        for (i=0; i<8; i++) { coll.def[i] = s[slot].def[i]; sum += s[slot].def[i]; }
    }
    else {
        for (i=0; i<objnum; i++) {
            u = &units[i];
            if (u->slot_id == slot) {
                if (u->obj_type < 200) { coll.fleet[u->obj_type-100]++; sum++; }
                else { coll.def[u->obj_type-200]++; sum++; }
            }
        }
    }

    printf ("<th><br><center>%s %s (<a href=\"#\">[%i:%i:%i]</a>)<br>", SlotCaption[attacker], s[slot].name, s[slot].g, s[slot].s, s[slot].p);
    if (sum > 0) {
        if (techs) printf (loca[LOCA084_TECHS], s[slot].weap*10, s[slot].shld*10, s[slot].armor*10 );
        printf ("<table border=1>");
        printf ("<tr><th>%s</th>", loca[LOCA084_SLOT_TYPE]);
        for (n=0; n<14; n++) {
            if (coll.fleet[n] > 0) printf ("<th>%s</th>", FleetShort[n]);
        }
        for (n=0; n<8; n++) {
            if (coll.def[n] > 0) printf ("<th>%s</th>", DefenseShort[n]);
        }
        printf ("</tr>");
        printf ("<tr><th>%s</th>", loca[LOCA084_SLOT_AMOUNT]);
        for (n=0; n<14; n++) {
            if (coll.fleet[n] > 0) printf ("<th>%s</th>", nicenum((u64)coll.fleet[n]));
        }
        for (n=0; n<8; n++) {
            if (coll.def[n] > 0) printf ("<th>%s</th>", nicenum((u64)coll.def[n]));
        }
        printf ("</tr>");
        printf ("<tr><th>%s</th>", loca[LOCA084_SLOT_WEAP]);
        for (n=0; n<14; n++) {
            if (coll.fleet[n] > 0) printf ("<th>%s</th>", nicenum((u64)(fleetParam[n].attack * (10+coll.weap) / 10)));
        }
        for (n=0; n<8; n++) {
            if (coll.def[n] > 0) printf ("<th>%s</th>", nicenum((u64)(defenseParam[n].attack * (10+coll.weap) / 10)));
        }
        printf ("</tr>");
        printf ("<tr><th>%s</th>", loca[LOCA084_SLOT_SHLD]);
        for (n=0; n<14; n++) {
            if (coll.fleet[n] > 0) printf ("<th>%s</th>", nicenum((u64)(fleetParam[n].shield * (10+coll.shld) / 10)));
        }
        for (n=0; n<8; n++) {
            if (coll.def[n] > 0) printf ("<th>%s</th>", nicenum((u64)(defenseParam[n].shield * (10+coll.shld) / 10)));
        }
        printf ("</tr>");
        printf ("<tr><th>%s</th>", loca[LOCA084_SLOT_HULL]);
        for (n=0; n<14; n++) {
            if (coll.fleet[n] > 0) printf ("<th>%s</th>", nicenum((u64)(fleetParam[n].structure * (10+coll.armor) / 100)));
        }
        for (n=0; n<8; n++) {
            if (coll.def[n] > 0) printf ("<th>%s</th>", nicenum((u64)(defenseParam[n].structure * (10+coll.armor) / 100)));
        }
        printf ("</tr>");
        printf ("</table>");
    }
    else printf (loca[LOCA084_SLOT_DESTROYED]);
    printf ("</center></th>");
}

void OGame084Report (BattleState *bst, Slot *a, int anum, Slot *d, int dnum)
{
    char longstr1[32], longstr2[32], longstr3[32];  // Буферы для non-reentrant функции nicenum.
    int i, round, slot, sum = 0;
    struct tm *ptm;
    time_t rawtime;
    unsigned long RepairMap[8] = { 0, 1, 2, 3, 4, 6, 5, 7 };
    unsigned long memload = 0;

    time (&rawtime);
    rawtime += 60*60;
    ptm = gmtime (&rawtime);

    // Заголовок боевого доклада.
    printf("Content-type: text/html\n");
    printf("Pragma: no-cache\n");
    printf("\n");
    printf ("<html>\n<HEAD>\n<LINK rel=\"stylesheet\" type=\"text/css\" href=\"http://ogamespec.com/evolution/formate.css\">\n");
    printf ("  <meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\" />\n\n");
    printf ("  <TITLE>%s</TITLE>\n\n", loca[LOCA084_BATTLE_REPORT]);
    printf ("</HEAD>\n<BODY>\n<table width=\"99%%\">\n   <tr>\n    <td>\n\n");
    printf ("%s %02i-%02i %02i:%02i:%02i . %s<br>", loca[LOCA084_DATE_TIME], ptm->tm_mon+1, ptm->tm_mday, ptm->tm_hour, ptm->tm_min, ptm->tm_sec, loca[LOCA084_ENCOUNTER]);

    // Флоты перед боем.
    printf ("<table border=1 width=100%%><tr>");
    for (slot=0; slot<anum; slot++) {
        GenSlot (NULL, slot, 0, a, d, 1, 1);
    }
    printf ("</tr></table>");
    printf ("<table border=1 width=100%%><tr>");
    for (slot=0; slot<dnum; slot++) {
        GenSlot (NULL, slot, 0, a, d, 0, 1);
    }
    printf ("</tr></table>");
    
    // Раунды.
    for (round=0; round<bst->rounds; round++)
    {
        strcpy (longstr1, nicenum(bst->round[round].shoots[0]));
        strcpy (longstr2, nicenum(bst->round[round].spower[0]));
        strcpy (longstr3, nicenum(bst->round[round].absorbed[1]));
        printf ("<br><center>");
        printf (loca[LOCA084_ASHOOT], longstr1, longstr2, longstr3);
        printf ("<br>");
        strcpy (longstr1, nicenum(bst->round[round].shoots[1]));
        strcpy (longstr2, nicenum(bst->round[round].spower[1]));
        strcpy (longstr3, nicenum(bst->round[round].absorbed[0]));
        printf (loca[LOCA084_DSHOOT], longstr1, longstr2, longstr3);
        printf ("</center>");

        printf ("<table border=1 width=100%%><tr>");
        for (slot=0; slot<anum; slot++) {
            GenSlot (bst->round[round].aunits, slot, bst->round[round].aunum, a, d, 1, 0);
        }
        printf ("</tr></table>");
        printf ("<table border=1 width=100%%><tr>");
        for (slot=0; slot<dnum; slot++) {
            GenSlot (bst->round[round].dunits, slot, bst->round[round].dunum, a, d, 0, 0);
        }
        printf ("</tr></table>");
        memload += bst->round[round].memload;
    }    
    
    // Результаты.
    if (bst->result == SPECSIM_BATTLE_WON) { 
        strcpy (longstr1, nicenum(bst->cm));
        strcpy (longstr2, nicenum(bst->ck));
        strcpy (longstr3, nicenum(bst->cd));
        printf ("<p> %s<br>", loca[LOCA084_AWON]);
        printf (loca[LOCA084_PLUNDER], longstr1, longstr2, longstr3);
        printf ("<br>");
    }
    else if (bst->result == SPECSIM_BATTLE_LOST) printf ("<p> %s<br>", loca[LOCA084_DWON]);
    else printf ("<p> %s<br>", loca[LOCA084_DRAW]);

    strcpy (longstr1, nicenum(bst->aloss));
    strcpy (longstr2, nicenum(bst->dloss));
    printf ("<p><br>");
    printf (loca[LOCA084_LOSSTATS], longstr1, longstr2);
    strcpy (longstr1, nicenum(bst->dm));
    strcpy (longstr2, nicenum(bst->dk));
    printf ("<br>");
    printf (loca[LOCA084_DEBRIS], longstr1, longstr2);
    if (bst->moonchance) { 
        printf ("<br>");
        printf (loca[LOCA084_MOONCHANCE], nicenum(bst->moonchance));
    }

    // Восстановление обороны.
    // При выводе оригинального боевого доклада есть ошибка: Малый щитовой купол выводится не в свою очередь, а перед Плазменным орудием.
    // Чтобы быть максимально похожим на оригинальный доклад, при выводе восстановленной обороны используется таблица перестановки RepairMap.    
    if (bst->RepairDefenseTotal) {
        printf ("<br>");
        for (i=0; i<8; i++) {
            if (bst->RepairDefense[RepairMap[i]]) {
                if (sum > 0) printf (", ");
                printf ("%i %s", bst->RepairDefense[RepairMap[i]], DefenseNames[RepairMap[i]]);
                sum += bst->RepairDefense[RepairMap[i]];
            }
        }
        printf (loca[LOCA084_REPAIRED]);
    }

    printf ("\n<!-- Memory load: %ul -->\n", memload);
    printf ("    </td>\n   </tr>\n</table>\n</BODY>\n</html>");
}
