// Генератор отладочной информации.
#include <stdio.h>
#include "sim.h"
#include "loca.h"

void DebugReport (BattleState *bst, Slot *a, int anum, Slot *d, int dnum)
{
    int i, round;
    unsigned long memload = 0;
    
    printf("Content-type: text/html\n");
    printf("Pragma: no-cache\n");
    printf("\n");
    printf ("Error: %i, Result: %i<br/>", bst->error, bst->result);

    for (round=0; round<bst->rounds; round++) {
        printf ("ROUND: %i (A: %i, D:%i)<br/>", round+1, bst->round[round].aunum, bst->round[round].dunum);
        
        // Перечислить атакеров.
        for (i=0; i<bst->round[round].aunum; i++) {
            printf ("%i: %i<br/>", i, bst->round[round].aunits[i].obj_type);
        }

        // Перечислить дефов.
        for (i=0; i<bst->round[round].dunum; i++) {
            printf ("%i: %i<br/>", i, bst->round[round].dunits[i].obj_type);
        }

        memload += bst->round[round].memload;
    }

    printf ("MEMORY LOAD: %ul bytes <br/>", memload);
}


/*
    // Восстановление обороны.
    if (ExplodedDefenseTotal) {
        for (i=0; i<8; i++) {
            if (ExplodedDefense[i]) {
                if (d[0].def[i] < 10) {
                    for (n=0; n<ExplodedDefense[i]; n++) {
                        if ( rand () % 100 < 70 ) { 
                            RepairDefense[i]++;
                            RepairDefenseTotal++;
                        }
                    }
                    if(debug) printf (LOCD_REPAIR, DefenseNames[i], 70, d[0].def[i], ExplodedDefense[i], RepairDefense[i]);
                }
                else {
                    repairchance = 60 + (rand() % (80-60+1));
                    RepairDefense[i] = repairchance * ExplodedDefense[i] / 100;
                    if(debug) printf (LOCD_REPAIR, DefenseNames[i], repairchance, d[0].def[i], ExplodedDefense[i], RepairDefense[i]);
                    RepairDefenseTotal += RepairDefense[i];
                }
            }
        }
    }
*/