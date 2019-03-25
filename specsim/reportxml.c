// Вывод результатов боя через XML.
#include <stdio.h>
#include <time.h>
#include "sim.h"
#include "loca.h"

static char *longnum (u64 n)
{
	static char retbuf [32];
	char *p = &retbuf [sizeof (retbuf) - 1];
	int i = 0;

    if (n == 0) return "0";
	*p = '\0';
	for (i = 0; n; i++)
	{
		*--p = '0' + n % 10;
		n /= 10;
	}
	return p;
}

void GenFormation (Unit *units, int objnum, int slot, int attacker)
{
    int i, n;
    Unit *u;
    Slot coll;
    unsigned long sum = 0;
    static char *ptype[] = { "Defender", "Attacker" };

    memset (&coll, 0, sizeof(Slot));

    // Собрать всё в один слот.
    for (i=0; i<objnum; i++) {
        u = &units[i];
        if (u->slot_id == slot) {
            if (u->obj_type < 200) { coll.fleet[u->obj_type-100]++; sum++; }
            else { coll.def[u->obj_type-200]++; sum++; }
        }
    }

    printf ("    <FormationInfo PlayerType=\"%s\" aNumber=\"%i\">\n", ptype[attacker], slot);
    if (sum > 0) {
        printf ("        <Fleet>\n");
        for (n=0; n<14; n++) {
            if (coll.fleet[n] > 0) {
                printf ("            <FleetType>%s</FleetType>\n", FleetNames[n]);
                printf ("            <FleetCount> %i </FleetCount>\n", coll.fleet[n]);
            }
        }
        printf ("        </Fleet>\n");
        if (!attacker && slot == 0) {
            printf ("        <Defense>\n");
            for (n=0; n<8; n++) {
                if (coll.def[n] > 0) {
                    printf ("            <DefenseType>%s</DefenseType>\n", DefenseNames[n]);
                    printf ("            <DefenseCount> %i </DefenseCount>\n", coll.def[n]);
                }
            }
            printf ("        </Defense>\n");
        }
    }
    printf ("    </FormationInfo>\n\n");
}

void XMLReport (BattleState *bst, Slot *a, int anum, Slot *d, int dnum)
{
    int i, n, round, slot;
    time_t rawtime;

    printf("Content-type: text/xml\n");
    printf("Pragma: no-cache\n");
    printf("\n");
    printf ("<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n\n");

    // Заголовок.
    time (&rawtime);
    rawtime += 60*60;
    printf ("<Battle>\n");
    printf ("    <ServerTime> %i </ServerTime>\n\n", rawtime );

    // Список атакующих и обороняющихся.
    for (i=0; i<anum; i++) {
        printf ("     <PlayerInfo PlayerType=\"Attacker\" aNumber=\"%i\">\n", i);
        printf ("             <Name> %s </Name>\n", a[i].name);
        printf ("             <WeaponTech> %i </WeaponTech>\n", a[i].weap);
        printf ("             <ShieldTech> %i </ShieldTech>\n", a[i].shld);
        printf ("             <ArmourTech> %i </ArmourTech>\n", a[i].armor);
        printf ("             <Galaxy> %i </Galaxy>\n", a[i].g);
        printf ("             <System> %i </System>\n", a[i].s);
        printf ("             <Planet> %i </Planet>\n", a[i].p);
        printf ("     </PlayerInfo>\n\n");
    }
    for (i=0; i<dnum; i++) {
        printf ("     <PlayerInfo PlayerType=\"Defender\" aNumber=\"%i\">\n", i);
        printf ("             <Name> %s </Name>\n", d[i].name);
        printf ("             <WeaponTech> %i </WeaponTech>\n", d[i].weap);
        printf ("             <ShieldTech> %i </ShieldTech>\n", d[i].shld);
        printf ("             <ArmourTech> %i </ArmourTech>\n", d[i].armor);
        printf ("             <Galaxy> %i </Galaxy>\n", d[i].g);
        printf ("             <System> %i </System>\n", d[i].s);
        printf ("             <Planet> %i </Planet>\n", d[i].p);
        printf ("     </PlayerInfo>\n\n");
    }

    // Список юнитов атакующих и обороняющихся.
    for (i=0; i<anum; i++) {
        printf ("    <FormationInfo PlayerType=\"Attacker\" aNumber=\"%i\">\n", i);
        printf ("        <Fleet>\n");
        for (n=0; n<14; n++) {
            if (a[i].fleet[n] > 0) {
                printf ("            <FleetType>%s</FleetType>\n", FleetNames[n]);
                printf ("            <FleetCount> %i </FleetCount>\n", a[i].fleet[n]);
            }
        }
        printf ("        </Fleet>\n");
        printf ("    </FormationInfo>\n\n");
    }
    for (i=0; i<dnum; i++) {
        printf ("    <FormationInfo PlayerType=\"Defender\" aNumber=\"%i\">\n", i);
        printf ("        <Fleet>\n");
        for (n=0; n<14; n++) {
            if (d[i].fleet[n] > 0) {
                printf ("            <FleetType>%s</FleetType>\n", FleetNames[n]);
                printf ("            <FleetCount> %i </FleetCount>\n", d[i].fleet[n]);
            }
        }
        printf ("        </Fleet>\n");
        if (i == 0) {
            printf ("        <Defense>\n");
            for (n=0; n<8; n++) {
                if (d[i].def[n] > 0) {
                    printf ("            <DefenseType>%s</DefenseType>\n", DefenseNames[n]);
                    printf ("            <DefenseCount> %i </DefenseCount>\n", d[i].def[n]);
                }
            }
            printf ("        </Defense>\n");
        }
        printf ("    </FormationInfo>\n\n");
    }
    
    // Раунды.
    for (round=0; round<bst->rounds; round++) {
        printf ("<Round Number = \"%i\">\n", round);
        printf ("    <RoundInfo>\n");
        printf ("        <Attacker>\n");
        printf ("            <ShotsCount> %s </ShotsCount>\n", longnum (bst->round[round].shoots[0]));
        printf ("            <ShotsDamage> %s </ShotsDamage>\n", longnum (bst->round[round].spower[0]));
        printf ("            <ShotsAbsorbed> %s </ShotsAbsorbed>\n", longnum (bst->round[round].absorbed[1]));
        printf ("        </Attacker>\n");
        printf ("        <Defender>\n");
        printf ("            <ShotsCount> %s </ShotsCount>\n", longnum (bst->round[round].shoots[1]));
        printf ("            <ShotsDamage> %s </ShotsDamage>\n", longnum (bst->round[round].spower[1]));
        printf ("            <ShotsAbsorbed> %s </ShotsAbsorbed>\n", longnum (bst->round[round].absorbed[0]));
        printf ("        </Defender>\n");
        printf ("    </RoundInfo>\n\n");

        for (slot=0; slot<anum; slot++) {
            GenFormation (bst->round[round].aunits, bst->round[round].aunum, slot, 1);
        }
        for (slot=0; slot<dnum; slot++) {
            GenFormation (bst->round[round].dunits, bst->round[round].dunum, slot, 0);
        }

        printf ("</Round>\n\n");
    }

    // Результаты.
    printf ("<Result>\n\n");

    if (bst->result == SPECSIM_BATTLE_WON) printf ("   <Winner> Attacker </Winner>\n\n");
    else if (bst->result == SPECSIM_BATTLE_LOST) printf ("   <Winner> Defender </Winner>\n\n");
    else  printf ("   <Winner> Draw </Winner>\n\n");

    printf ("    <Plunder>\n");                                                 // Захвачено
    printf ("        <Metal> %s </Metal>\n", longnum (bst->cm));
    printf ("        <Crystal> %s </Crystal>\n", longnum (bst->ck));
    printf ("        <Deuterium> %s </Deuterium>\n", longnum (bst->cd));
    printf ("    </Plunder>\n\n");

    printf ("    <AttackerLost> %s </AttackerLost>\n", longnum (bst->aloss));       // Потери атакующего и обороняющегося
    printf ("    <DefenderLost> %s </DefenderLost>\n\n", longnum (bst->dloss));

    printf ("    <Debris>\n");                                                  // Поле обломков
    printf ("        <Metal> %s </Metal>\n", longnum (bst->dm));
    printf ("        <Crystal> %s </Crystal>\n", longnum (bst->dk));
    printf ("    </Debris>\n\n");

    printf ("    <MoonChance> %i </MoonChance>\n\n", bst->moonchance);          // Шанс возникновения луны

    printf ("    <DefenseRepair Total = \"%i\">", bst->RepairDefenseTotal );        // Восстановленная оборона
    for (i=0; i<8; i++) {
        if (bst->RepairDefense[i]) {
            printf ("        <DefenceType> %s </DefenceType>\n", DefenseNames[i]);
            printf ("        <DefenceCount> %i </DefenceCount>\n", bst->RepairDefense[i]);
        }
    }
    printf ("    </DefenseRepair>\n\n");

    printf ("</Result>\n\n");

    printf ("</Battle>");
}
