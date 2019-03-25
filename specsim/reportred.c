// Боевой доклад OGame 1.0+ (редизайн)
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <time.h>
#include "sim.h"
#include "loca.h"

// В редизайне нет сокращенного названия для бомбардировщика.
// Пока разработчики не исправят эту ошибку, хак будет использоваться.
#define RED_BOMBER_HACK

typedef struct PlayerName {
    unsigned char name[128];
} PlayerName;
static PlayerName *aname, *dname;
static int anames, dnames;

void AddAttackerName (unsigned char *name)
{
    int i;
    for (i=0; i<anames; i++) {
        if (!strcmp (aname[i].name, name)) return;
    }
    aname = (PlayerName *)realloc (aname, (anames + 1) * sizeof (PlayerName) );
    strcpy (aname[anames++].name, name);
}

void AddDefenderName (unsigned char *name)
{
    int i;
    for (i=0; i<dnames; i++) {
        if (!strcmp (dname[i].name, name)) return;
    }
    dname = (PlayerName *)realloc (dname, (dnames + 1) * sizeof (PlayerName) );
    strcpy (dname[dnames++].name, name);
}

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

    printf ("    <td class=\"newBack\">\n");
    printf ("    <center>\n");
    if (sum > 0) {
        printf ("    <span class=\"name textBeefy\">%s %s <a  href=\"#\">[%i:%i:%i]</a></span>\n", SlotCaption[attacker], s[slot].name, s[slot].g, s[slot].s, s[slot].p);
        if (techs) { 
            printf ("    <span class=\"weapons textBeefy\">");
            printf (loca[LOCARED_TECHS], s[slot].weap*10, s[slot].shld*10, s[slot].armor*10 );
            printf ( "</span>\n" );
        }
        printf ("        <table cellpadding=\"0\" cellspacing=\"0\">\n");
        printf ("        <tr><th class=\"textGrow\">%s</th>", loca[LOCARED_SLOT_TYPE]);
        for (n=0; n<14; n++) {
#ifdef RED_BOMBER_HACK
            if (coll.fleet[n] > 0) {
                if (n == 9) printf ("<th class=\"textGrow\">%s</th>", FleetNames[n]);
                else printf ("<th class=\"textGrow\">%s</th>", FleetShort[n]);
            }
#else
            if (coll.fleet[n] > 0) printf ("<th class=\"textGrow\">%s</th>", FleetShort[n]);
#endif
        }
        for (n=0; n<8; n++) {
            if (coll.def[n] > 0) printf ("<th class=\"textGrow\">%s</th>", DefenseShort[n]);
        }
        printf ("</tr>\n");
        printf ("        <tr><td>%s</td>", loca[LOCARED_SLOT_AMOUNT]);
        for (n=0; n<14; n++) {
            if (coll.fleet[n] > 0) printf ("<td>%s</td>", nicenum((u64)coll.fleet[n]));
        }
        for (n=0; n<8; n++) {
            if (coll.def[n] > 0) printf ("<td>%s</td>", nicenum((u64)coll.def[n]));
        }
        printf ("</tr>\n");
        printf ("        <tr><td>%s</td>", loca[LOCARED_SLOT_WEAP]);
        for (n=0; n<14; n++) {
            if (coll.fleet[n] > 0) printf ("<td>%s</td>", nicenum((u64)(fleetParam[n].attack * (10+coll.weap) / 10)));
        }
        for (n=0; n<8; n++) {
            if (coll.def[n] > 0) printf ("<td>%s</td>", nicenum((u64)(defenseParam[n].attack * (10+coll.weap) / 10)));
        }
        printf ("</tr>\n");
        printf ("        <tr><td>%s</td>", loca[LOCARED_SLOT_SHLD]);
        for (n=0; n<14; n++) {
            if (coll.fleet[n] > 0) printf ("<td>%s</td>", nicenum((u64)(fleetParam[n].shield * (10+coll.shld) / 10)));
        }
        for (n=0; n<8; n++) {
            if (coll.def[n] > 0) printf ("<td>%s</td>", nicenum((u64)(defenseParam[n].shield * (10+coll.shld) / 10)));
        }
        printf ("</tr>\n");
        printf ("        <tr><td>%s</td>", loca[LOCARED_SLOT_HULL]);
        for (n=0; n<14; n++) {
            if (coll.fleet[n] > 0) printf ("<td>%s</td>", nicenum((u64)(fleetParam[n].structure * (10+coll.armor) / 100)));
        }
        for (n=0; n<8; n++) {
            if (coll.def[n] > 0) printf ("<td>%s</td>", nicenum((u64)(defenseParam[n].structure * (10+coll.armor) / 100)));
        }
        printf ("</tr>\n");
        printf ("        </table>\n");
    }
    else printf ("    <span class=\"destroyed textBeefy\">%s %s %s</span>\n", SlotCaption[attacker], s[slot].name, loca[LOCARED_SLOT_DESTROYED]);
    printf ("    </center>\n");
    printf ("    </td>\n");
}

void OGameRedReport (BattleState *bst, Slot *a, int anum, Slot *d, int dnum)
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

    // Найти имена всех атакующих и обороняющихся.
    anames = dnames = 0;
    for (i=0; i<anum; i++) AddAttackerName (a[i].name);
    for (i=0; i<dnum; i++) AddDefenderName (d[i].name);

    // Заголовок боевого доклада.
    printf("Content-type: text/html\n");
    printf("Pragma: no-cache\n");
    printf("\n");
    printf ("<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n");
    printf ("<html xmlns=\"http://www.w3.org/1999/xhtml\">\n");
    printf ("<head>\n");
    printf ("	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n");
    printf ("	<title>%s</title>\n", loca[LOCARED_BATTLE_REPORT]);
    printf ("		<link rel='stylesheet' type='text/css' href='http://ogamespec.com/redesign_css/reset.css' media='screen' />\n");
    printf ("<link rel='stylesheet' type='text/css' href='http://ogamespec.com/redesign_css/toolbox.css' media='screen' />\n");
    printf ("<link rel='stylesheet' type='text/css' href='http://ogamespec.com/redesign_css/combatreport.css' media='screen' />\n");
    printf ("</head>\n\n");
    printf ("<body id=\"combatreport\">\n\n");
    printf ("<div id=\"master\">\n\n");
    printf ("<div class=\"combat_round\">\n");
    printf ("    <div class=\"round_info\">\n");
    printf ("        <p class=\"start\">%s (%02i.%02i.%i %02i:%02i:%02i) . %s</p>\n", loca[LOCARED_DATE_TIME], ptm->tm_mon+1, ptm->tm_mday, 1900+ptm->tm_year, ptm->tm_hour, ptm->tm_min, ptm->tm_sec, loca[LOCARED_ENCOUNTER]);
    printf ("        <p class=\"start opponents\">");
    for (i=0; i<anames; i++) {
        if (i > 0) printf (", ");
        printf (aname[i].name);
    }
    printf (" vs. ");
    for (i=0; i<dnames; i++) {
        if (i > 0) printf (", ");
        printf (dname[i].name);
    }
    printf ("</p>\n");
    printf ("    </div>\n");

    if (aname) { free (aname); aname = NULL; }
    if (dname) { free (dname); dname = NULL; }
   
    // Флоты перед боем.
    printf ("<table cellpadding=\"0\" cellspacing=\"0\" style=\"width:100%;\">\n");     // Атакеры
    printf ("<tr><td class=\"round_attacker textCenter\">\n");
    printf ("    <table cellpadding=\"0\" cellspacing=\"0\"><tr>\n");
    for (slot=0; slot<anum; slot++) {
        GenSlot (NULL, slot, 0, a, d, 1, 1);
    }
    printf ("    </tr></table>\n");
    printf ("</td></tr></table>\n");

    printf ("<table cellpadding=\"0\" cellspacing=\"0\" style=\"width:100%;\">\n");     // Дефы
    printf ("<tr><td class=\"round_defender textCenter\">\n");
    printf ("    <table cellpadding=\"0\" cellspacing=\"0\"><tr>\n");
    for (slot=0; slot<dnum; slot++) {
        GenSlot (NULL, slot, 0, a, d, 0, 1);
    }
    printf ("    </tr></table>\n");
    printf ("</td></tr></table>\n");
    
    // Раунды.
    for (round=0; round<bst->rounds; round++)
    {
        printf ("<div class=\"combat_round\">\n");
        printf ("    <div class=\"round_info\">\n");
        printf ("	     <div class=\"battle\">\n");
        strcpy (longstr1, nicenum(bst->round[round].shoots[0]));
        strcpy (longstr2, nicenum(bst->round[round].spower[0]));
        strcpy (longstr3, nicenum(bst->round[round].absorbed[1]));
        printf ("        <p class=\"action\">");
        printf ( loca[LOCARED_ASHOOT], longstr1, longstr2, longstr3);
        printf ( "</p>\n");
        strcpy (longstr1, nicenum(bst->round[round].shoots[1]));
        strcpy (longstr2, nicenum(bst->round[round].spower[1]));
        strcpy (longstr3, nicenum(bst->round[round].absorbed[0]));
        printf ("		 <p class=\"action\">");
        printf ( loca[LOCARED_DSHOOT], longstr1, longstr2, longstr3);
        printf ("</p>\n");
        printf ("        </div>\n");
        printf ("    </div>\n");

        printf ("<table cellpadding=\"0\" cellspacing=\"0\" style=\"width:100%;\">\n");     // Атакеры
        printf ("<tr><td class=\"round_attacker textCenter\">\n");
        printf ("    <table cellpadding=\"0\" cellspacing=\"0\"><tr>\n");
        for (slot=0; slot<anum; slot++) {
            GenSlot (bst->round[round].aunits, slot, bst->round[round].aunum, a, d, 1, 0);
        }
        printf ("    </tr></table>\n");
        printf ("</td></tr></table>\n");

        printf ("<table cellpadding=\"0\" cellspacing=\"0\" style=\"width:100%;\">\n");     // Дефы
        printf ("<tr><td class=\"round_defender textCenter\">\n");
        printf ("    <table cellpadding=\"0\" cellspacing=\"0\"><tr>\n");
        for (slot=0; slot<dnum; slot++) {
            GenSlot (bst->round[round].dunits, slot, bst->round[round].dunum, a, d, 0, 0);
        }
        printf ("    </tr></table>\n");
        printf ("</td></tr></table>\n");

        printf ("</div>\n");
        memload += bst->round[round].memload;
    }
    
    // Результаты.
    printf ("<div id=\"combat_result\">\n");
    if (bst->result == SPECSIM_BATTLE_WON) { 
        strcpy (longstr1, nicenum(bst->cm));
        strcpy (longstr2, nicenum(bst->ck));
        strcpy (longstr3, nicenum(bst->cd));
        printf ("<p class=\"action\"> %s   ", loca[LOCARED_AWON]);
        printf ( loca[LOCARED_PLUNDER], longstr1, longstr2, longstr3);
        printf ("</p>\n");
    }
    else if (bst->result == SPECSIM_BATTLE_LOST) printf ("<p class=\"action\"> %s</p>\n", loca[LOCARED_DWON]);
    else printf ("<p class=\"action\"> %s</p>\n", loca[LOCARED_DRAW]);

    strcpy (longstr1, nicenum(bst->aloss));
    strcpy (longstr2, nicenum(bst->dloss));
    printf ("<p class=\"action\">\n    ");
    printf (loca[LOCARED_LOSSTATS], longstr1, longstr2);
    printf ( "<br />\n");
    strcpy (longstr1, nicenum(bst->dm));
    strcpy (longstr2, nicenum(bst->dk));
    printf ("    ");
    printf (loca[LOCARED_DEBRIS], longstr1, longstr2);
    printf ("<br />\n");
    if (bst->moonchance) {
        printf ("    " );
        printf (loca[LOCARED_MOONCHANCE], nicenum(bst->moonchance));
        printf ("<br />");
    }

    // Восстановление обороны
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
        printf (loca[LOCARED_REPAIRED]);
    }

    printf ("\n<!-- Memory load: %ul -->\n", memload);
    printf ("\n</div><!-- combat_result -->\n");
    printf ("</div><!-- master -->\n\n</body>\n\n</html>");
}
