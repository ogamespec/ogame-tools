// ������ ������ ���������� ���� OGame.

/*
������� ���

������� ��� ������ ���������� � ��� �������, ����� ��������� ����� ����������� � �����-���� ������� ��� ����.
��� ��������� � �������� ��� ���������, �� � ��� ����� ��������, ��� ������� ���� ��� ���������.
� ���� ������ ������ ������� ������������� � �������� ������ ���� �� �����. ��� ���������� 6 ��� (6 �������).
��� � ����� ������� � ���������, ��� ����������. ���� � ����� �� ����� �������� �������� �����, �� ��� ������������� ������ � ���������� ������������ �����.
� ������ ������ ������� � �������� ���������� �������� ���� �� �����. ��� ���� ������ ���� �������� ���� ��� (����������: ����������) �� �������� ��������� ����.
������� ���� �������� ������������ ������� �����. ��� ���� ����������� �������� ��� ��������� ������. ���� ����� ����� ��� ���-�� ������� �� ������ ����, �� ��� ���������� �� ����� �������.
�������� ����� �������� ���� �� �������� � ��������� ������������ ������. � ����� ������ ���������� �������, � ������� ������ �� �������� �����.
�� � �� 30% ����������� ����� ���� ������� ���� ������, ������� ����� ������ �� �������� �����������.
���� ��������:
������ ���� ����� ��������� ���� ��������. Ÿ ����� ��������� ������������� ��������� ������� �� 10% �� �������.
��������: ������ ����������� ����� ������ ����� 150. ��������� ������� ������ 10 �������� ��� �� 100% �� ���� �� 300.
��� ��� ������ ���� ���� ������ ������������ ������.
����:
���� ������� ��������� ���� �� ����, ����������� ����� �� �����������. ������ ����� ��� ���� ����������, ���������� ����������� �����.
���� ����� ���� �������� ������������� ������� ���������� �� 10% �� ������� ������������.
���� ��������� ����������������� ����� ������� ������. ������ ������ ��� ������������ ������ �������� �� 1%, � ������� ���� ����� ����� 1% ����������� ��� �����-���� ������.
��������, ���� ���� �������� ������� �� 3.7% ����, ����� ���������� ���� 3%, � 0.7% ����������. ������� �������� � ����� ����� 1%, ����������� �� �����, �� �������� �� ���� � �� ������ ����� �����.
����������� ������ � ���� ������ ����� �� �������������.
������: ����� ����������� (����� 50) �������� �� �������� ������ (��� 10000). ����� �������� � ������ �� ��� 10000 �����, ��� ��� ������� ������� ���� � ��� ��� ��������� ���������.
������ �� ����������� ����� ���� �������� 150. ��� 1.5% �� ����, ������� ������� ������������� � �� ���� ��������� ����� ����� ����� - 1%.
����� ����� � ������ ������� ���� ����� ������ 9900, ��� ��� 0.5% = 50 ����� ��������� ����� ��� ������.
�����:
����� ���������, ����� ����� ������� ����� ���������, ������ ��� �� ����� ���������. ����� ����� ������ ���������� 10% ���������.
�� ����� ��������� ��� ��� ��������� �����. �� ������ 10 ������� ��� ��������� (���� ����� �� ���������) ���������� 1 ����� �����.
���� ����� ����� ������������� ������������� ����� ����������� �������� �� 10% �� �������. � ���������, ������� ������� � ��������� ��� �� 30% ����������� �����.
����� ��� ������������ ������ ������. ������������ ����������: ���� ����� ����������� ���� ���������� � ������ 2-� �������.
����� ���������� �������� ������ �������� ���������. �������� �������� ������ ������
������� ���������
���� ������� ��������� ����� ��������, � ������ ����� ������� � ������ ������� �� ��������: ����� �������, ������ �������, ����� ������, ������ ������.
����� ����
���� ���������� ��������� ��������. ����� ����, ��� ��� ����� ����� �� ����� ����, ���� ���� � ������ ����, �� ��� ������������.
������ ������ ���� ���, ��� �����, ������� ������ �����, �������� ������ ���� ���������. ��� ���� � ������� ������� � ��������� ���������� ���� �����������, ��� � ���� �������, ������ 1/(���-�� ���� ������)
�������������� ������:
�������� ���������� ����� ����������� �� �������������� ����� ��� � 70%.
��� ��������� ���������� ������ (������ ��� 10) ��� ����������� ������������� ��� ������� ���������� ��������.
��� ������� ���������� ����������� ������������� ��� ������� ���� ������. ��� ���� ����������������� ������ 70% +/-10% ����������� ������.
��� 10 �������� ���������� ��� ������� 6 � �������� 8 ��������������� ��. ������� ����� ��������� �����������. ��� ������� ���� ������ ����������� ������������� ��������.
�� ����, ��������, �� � ������ �� ������������ ������. 

����������              
������ ���������� �������� ����������� ��������� ����� �������� ����������� �� ����� ����� ������������ �������� ��� ������ ��������. 
����������� ���������� �������� ���������� � ������� �� ����� ����������� ������� � ���� ��������. ������ ����������� ����������� � �������������� ����������� ���������� �������� ��� - ��� � ���� - ������� ����������� ��������� �� ���� �����.
� ������� ���������� �������� ��������� �������:
�������, ������� �� �����, ������ �������� � ���� ���� ����������, � ����������� ������������ �������� ��� ���, � ������������ � �������� ��� - �� ��������� ������� ����.
��� ��������� ��������� � ����� ������ �������, ����� "��������� ������" � ���� ������, ���������� ��� ���� �������. 
<�������� � ���������� ������� �������� ����������� �� http://board.ogame.ru/index.php?page=Thread&threadID=47130 >
*/

/*
���������� ������.

������ ������������ ����� ������ ����. �� ���� �������� ��������� �������:
- ������ ��������� � �������������
- ���������� �������, ��������� � �������� �� �������
- ��������� ������ ������� (������� ������� � ����� � �������, ����������)

�� ������ ������ ����������:
- ��� ������
- ���������� ���
- ��������� ���������� �� ������� (������� ������� ���������)

���������� ������ ���������� ��������� � ������ ��������� ������� �������.
*/

// ��� ���� ����� ������ �������� ��������� � ���� ���� (��� �������� ������), ��������� ����� ���������� �� 100 (������ 202), � ������� �� 200 (������ 401).

#include <stdlib.h>
#include <math.h>
#include "sim.h"
#include "loca.h"

// ��������� ��������� ����.
int DefenseInDebris = 0, FleetInDebris = 30;
int Rapidfire = 1;  // 1: ��� �������� ���������.

// ������� ���������.
typedef struct UnitPrice { long m, k, d; } UnitPrice;
static UnitPrice FleetPrice[] = {
 { 2000, 2000, 0 }, { 6000, 6000, 0 }, { 3000, 1000, 0 }, { 6000, 4000, 0 },
 { 20000, 7000, 2000 }, { 45000, 15000, 0 }, { 10000, 20000, 10000 }, { 10000, 6000, 2000 },
 { 0, 1000, 0 }, { 50000, 25000, 15000 }, { 0, 2000, 500 }, { 60000, 50000, 15000 },
 { 5000000, 4000000, 1000000 }, { 30000, 40000, 15000 }
};
static UnitPrice DefensePrice[] = {
 { 2000, 0, 0 }, { 1500, 500, 0 }, { 6000, 2000, 0 }, { 20000, 15000, 2000 },
 { 2000, 6000, 0 }, { 50000, 50000, 30000 }, { 10000, 10000, 0 }, { 50000, 50000, 0 }
};

TechParam fleetParam[14] = { // ��� �����.
 { 4000, 10, 5, 5000 },
 { 12000, 25, 5, 25000 },
 { 4000, 10, 50, 50 },
 { 10000, 25, 150, 100 },
 { 27000, 50, 400, 800 },
 { 60000, 200, 1000, 1500 },
 { 30000, 100, 50, 7500 },
 { 16000, 10, 1, 20000 },      
 { 1000, 0, 0, 0 },
 { 75000, 500, 1000, 500 },
 { 2000, 1, 1, 0 },
 { 110000, 500, 2000, 2000 }, 
 { 9000000, 50000, 200000, 1000000 }, 
 { 70000, 400, 700, 750 }
};

TechParam defenseParam[8] = { // ��� �������.
 { 2000, 20, 80, 0 },
 { 2000, 25, 100, 0 },
 { 8000, 100, 250, 0 },
 { 35000, 200, 1100, 0 },
 { 8000, 500, 150, 0 },
 { 100000, 300, 3000, 0 },
 { 20000, 2000, 1, 0 },
 { 100000, 10000, 1, 0 },
};

// ��������� �����������.
static long FleetRapid[][14] = {
 { 0, 0, 0, 0, 0, 0, 0, 0, 800, 0, 800, 0, 0, 0 },
 { 0, 0, 0, 0, 0, 0, 0, 0, 800, 0, 800, 0, 0, 0 },
 { 0, 0, 0, 0, 0, 0, 0, 0, 800, 0, 800, 0, 0, 0 },
 { 667, 0, 0, 0, 0, 0, 0, 0, 800, 0, 800, 0, 0, 0 },
 { 0, 0, 833, 0, 0, 0, 0, 0, 800, 0, 800, 0, 0, 0 },
 { 0, 0, 0, 0, 0, 0, 0, 0, 800, 0, 800, 0, 0, 0 },
 { 0, 0, 0, 0, 0, 0, 0, 0, 800, 0, 800, 0, 0, 0 },
 { 0, 0, 0, 0, 0, 0, 0, 0, 800, 0, 800, 0, 0, 0 },
 { 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0 },
 { 0, 0, 0, 0, 0, 0, 0, 0, 800, 0, 800, 0, 0, 0 },
 { 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0 },
 { 0, 0, 0, 0, 0, 0, 0, 0, 800, 0, 800, 0, 0, 500 },
 { 996, 996, 995, 990, 970, 966, 996, 996, 999, 960, 999, 800, 0, 933 },
 { 667, 667, 0, 750, 750, 857, 0, 0, 800, 0, 800, 0, 0, 0 }
};
static long DefenseRapid[][8] = {
 { 0, 0, 0, 0, 0, 0, 0, 0 },
 { 0, 0, 0, 0, 0, 0, 0, 0 },
 { 0, 0, 0, 0, 0, 0, 0, 0 },
 { 0, 0, 0, 0, 0, 0, 0, 0 },
 { 900, 0, 0, 0, 0, 0, 0, 0 },
 { 0, 0, 0, 0, 0, 0, 0, 0 },
 { 0, 0, 0, 0, 0, 0, 0, 0 },
 { 0, 0, 0, 0, 0, 0, 0, 0 },
 { 0, 0, 0, 0, 0, 0, 0, 0 },
 { 955, 955, 900, 0, 900, 0, 0, 0 },
 { 0, 0, 0, 0, 0, 0, 0, 0 },
 { 0, 900, 0, 0, 0, 0, 0, 0 },
 { 955, 955, 999, 980, 999, 0, 0, 0 },
 { 0, 0, 0, 0, 0, 0, 0, 0 }
};

// ��������� ��������� �����.
// Mersenne Twister.

#define N 624
#define M 397
#define MATRIX_A 0x9908b0dfUL
#define UPPER_MASK 0x80000000UL
#define LOWER_MASK 0x7fffffffUL

static unsigned long mt[N];
static int mti=N+1;

void init_genrand(unsigned long s)
{
    mt[0]= s & 0xffffffffUL;
    for (mti=1; mti<N; mti++) {
        mt[mti] = 
	    (1812433253UL * (mt[mti-1] ^ (mt[mti-1] >> 30)) + mti); 
        mt[mti] &= 0xffffffffUL;
    }
}

unsigned long genrand_int32(void)
{
    unsigned long y;
    static unsigned long mag01[2]={0x0UL, MATRIX_A};

    if (mti >= N) {
        int kk;

        if (mti == N+1)
            init_genrand(5489UL);

        for (kk=0;kk<N-M;kk++) {
            y = (mt[kk]&UPPER_MASK)|(mt[kk+1]&LOWER_MASK);
            mt[kk] = mt[kk+M] ^ (y >> 1) ^ mag01[y & 0x1UL];
        }
        for (;kk<N-1;kk++) {
            y = (mt[kk]&UPPER_MASK)|(mt[kk+1]&LOWER_MASK);
            mt[kk] = mt[kk+(M-N)] ^ (y >> 1) ^ mag01[y & 0x1UL];
        }
        y = (mt[N-1]&UPPER_MASK)|(mt[0]&LOWER_MASK);
        mt[N-1] = mt[M-1] ^ (y >> 1) ^ mag01[y & 0x1UL];

        mti = 0;
    }
  
    y = mt[mti++];

    y ^= (y >> 11);
    y ^= (y << 7) & 0x9d2c5680UL;
    y ^= (y << 15) & 0xefc60000UL;
    y ^= (y >> 18);

    return y;
}

double genrand_real1(void) { return genrand_int32()*(1.0/4294967295.0); }
double genrand_real2(void) { return genrand_int32()*(1.0/4294967296.0); }

// ������������ ��������������� ������������������.
void SimSrand (unsigned long seed)
{
    init_genrand (seed);
    //srand (seed);
}

// ���������� ��������� ����� � ��������� �� a �� b (������� a � b)
unsigned long SimRand (unsigned long a, unsigned long b)
{
    return a + (unsigned long)(genrand_real1 () * (b - a + 1));
    //return a + (unsigned long)((rand ()*(1.0/RAND_MAX)) * (b - a + 1));
}

// ���������� ��������� ��������� ����.
void SetDebrisOptions (int did, int fid)
{
    if (did < 0) did = 0;
    if (fid < 0) fid = 0;
    if (did > 100) did = 100;
    if (fid > 100) fid = 100;
    DefenseInDebris = did;
    FleetInDebris = fid;
}

void SetRapidfire (int enable) { Rapidfire = enable & 1; }

// �������� ������ ��� ������ � ���������� ��������� ��������.
Unit *InitBattleAttackers (Slot *a, int anum, int objs)
{
    Unit *u;
    int aid = 0;
    int i, n, ucnt = 0, obj;
    u = (Unit *)malloc (objs * sizeof(Unit));
    if (u == NULL) return u;
    memset (u, 0, objs * sizeof(Unit));
    
    for (i=0; i<anum; i++, aid++) {
        for (n=0; n<14; n++)
        {
            for (obj=0; obj<a[i].fleet[n]; obj++) {
                u[ucnt].hull = u[ucnt].hullmax = fleetParam[n].structure * 0.1 * (10+a[i].armor) / 10;
                u[ucnt].obj_type = 100 + n;
                u[ucnt].slot_id = aid;
                ucnt++;
            }
        }
    }

    return u;
}

Unit *InitBattleDefenders (Slot *d, int dnum, int objs)
{
    Unit *u;
    int did = 0;
    int i, n, ucnt = 0, obj;
    u = (Unit *)malloc (objs * sizeof(Unit));
    if (u == NULL) return u;
    memset (u, 0, objs * sizeof(Unit));

    for (i=0; i<dnum; i++, did++) {
        for (n=0; n<14; n++)
        {
            for (obj=0; obj<d[i].fleet[n]; obj++) {
                u[ucnt].hull = u[ucnt].hullmax = fleetParam[n].structure * 0.1 * (10+d[i].armor) / 10;
                u[ucnt].obj_type = 100 + n;
                u[ucnt].slot_id = did;
                ucnt++;
            }
        }
        for (n=0; n<8; n++)
        {
            for (obj=0; obj<d[i].def[n]; obj++) {
                u[ucnt].hull = u[ucnt].hullmax = defenseParam[n].structure * 0.1 * (10+d[i].armor) / 10;
                u[ucnt].obj_type = 200 + n;
                u[ucnt].slot_id = did;
                ucnt++;
            }
        }
    }

    return u;
}

// ������� a => b. ���������� ����. aweap - ������� ��������� ���������� ��� ����� "a".
// absorbed - ���������� ������������ ������ ����� (��� ����, ���� �������, �� ���� ��� ����� "b").
// loss - ���������� ������ (��������� ����� ������+��������).
long UnitShoot (Unit *a, int aweap, Unit *b, u64 *absorbed, u64 *loss, BattleState *bst )
{
    float prc, depleted;
    long apower, adelta = 0;
    if (a->obj_type < 200) apower = fleetParam[a->obj_type-100].attack * (10+aweap) / 10;
    else apower = defenseParam[a->obj_type-200].attack * (10+aweap) / 10;

    if (b->exploded) return apower; // ��� �������.
    if (b->shield == 0) {  // ����� ���.
        if (apower >= b->hull) b->hull = 0;
        else b->hull -= apower;
    }
    else { // �������� �� �����, � ���� ������� �����, �� � �� �����.
        prc = (float)b->shieldmax * 0.01;
        depleted = floor ((float)apower / prc);
        if (b->shield < (depleted * prc)) {
            *absorbed += (u64)b->shield;
            adelta = apower - b->shield;
            if (adelta >= b->hull) b->hull = 0;
            else b->hull -= adelta;
            b->shield = 0;
        }
        else {
            b->shield -= depleted * prc;
            *absorbed += (u64)apower;
        }
    }
    if (b->hull <= b->hullmax * 0.7 && b->shield == 0) {    // �������� � �������� ����.
        if (SimRand (0, 99) >= ((b->hull * 100) / b->hullmax) || b->hull == 0) {
            if (b->obj_type > 200) {
                bst->dm += (u64)(ceil(DefensePrice[b->obj_type-200].m * ((float)DefenseInDebris/100.0F)));
                bst->dk += (u64)(ceil(DefensePrice[b->obj_type-200].k * ((float)DefenseInDebris/100.0F)));
                *loss += (u64)(DefensePrice[b->obj_type-200].m + DefensePrice[b->obj_type-200].k);
                bst->ExplodedDefense[b->obj_type-200]++;
                bst->ExplodedDefenseTotal++;
            }
            else {
                bst->dm += (u64)(ceil(FleetPrice[b->obj_type-100].m * ((float)FleetInDebris/100.0F)));
                bst->dk += (u64)(ceil(FleetPrice[b->obj_type-100].k * ((float)FleetInDebris/100.0F)));
                *loss += (u64)(FleetPrice[b->obj_type-100].m + FleetPrice[b->obj_type-100].k);
            }
            b->exploded = 1;
        }
    }
    return apower;
}

// ��������� ���������� ������� � �������. ���������� ���������� ���������� ������.
int WipeExploded (Unit **slot, int amount)
{
    Unit *src = *slot, *tmp;
    int i, p = 0, exploded = 0;
    tmp = (Unit *)malloc (sizeof(Unit) * amount);
    for (i=0; i<amount; i++) {
        if (!src[i].exploded) tmp[p++] = src[i];
        else exploded++;
    }
    free (src);
    *slot = tmp;
    return exploded;
}

// ��������� ���������������� �����.
u64 CalcCargo (Unit *units, int amount)
{
    int i;
    u64 cargo = 0;
    for (i=0; i<amount; i++) {
        if (units[i].obj_type < 200) cargo += fleetParam[units[i].obj_type - 100].cargo;
    }
    return cargo;
}

// ������ ������.
void Plunder (u64 cargo, u64 m, u64 k, u64 d, u64 *mcap, u64 *kcap, u64 *dcap )
{
    u64 total, mc, kc, dc, half, bonus;
    m /=2; k/=2; d /= 2;
    total = m+k+d;
    
    mc = cargo / 3;
    if (m < mc) mc = m;
    cargo = cargo - mc;
    kc = cargo / 2;
    if (k < kc) kc = k;
    cargo = cargo - kc;
    dc = cargo;
    if (d < dc)
    {
        dc = d;
        cargo = cargo - dc;
        m = m - mc;
        half = cargo / 2;
        bonus = half;
        if (m < half) bonus = m;
        mc += bonus;
        cargo = cargo - bonus;
        k = k - kc;
        if (k < cargo) kc += k;
        else kc += cargo;
    }    
    
    *mcap = mc; *kcap = kc; *dcap = dc;
}

// �������� ����� �����.
int AddRound (BattleState *bst)
{
    bst->round = (RoundInfo *)realloc (bst->round, (bst->rounds + 1) * sizeof (RoundInfo) );
    if (bst->round == NULL) {
        bst->error = SPECSIM_ERROR_NOMEM;
        return 0;
    }
    memset (&bst->round[bst->rounds], 0, sizeof (RoundInfo));
    return 1;
}

// �������� � ����� ��������� ������.
void AddUnits (BattleState *bst, Unit *aunits, int aobjs, Unit *dunits, int dobjs)
{
    if (bst->round[bst->rounds].aunits) free (bst->round[bst->rounds].aunits);
    bst->round[bst->rounds].aunits = (Unit *)malloc (aobjs * sizeof (Unit));
    memcpy (bst->round[bst->rounds].aunits, aunits, aobjs * sizeof (Unit));
    if (bst->round[bst->rounds].dunits) free (bst->round[bst->rounds].dunits);
    bst->round[bst->rounds].dunits = (Unit *)malloc (dobjs * sizeof (Unit));
    memcpy (bst->round[bst->rounds].dunits, dunits, dobjs * sizeof (Unit));
    bst->round[bst->rounds].aunum = aobjs;
    bst->round[bst->rounds].dunum = dobjs;
    bst->round[bst->rounds].memload = ( aobjs * sizeof (Unit) + dobjs * sizeof (Unit) );
}

// ��������� ��� �� ������� �����. ���� �� � ������ ����� ����� �� ����������, �� ��� ������������� ������ ��������.
int CheckFastDraw (Unit *aunits, int aobjs, Unit *dunits, int dobjs)
{
    int i;
    for (i=0; i<aobjs; i++) {
        if (aunits[i].hull != aunits[i].hullmax) return 0;
    }
    for (i=0; i<dobjs; i++) {
        if (dunits[i].hull != dunits[i].hullmax) return 0;
    }
    return 1;
}

BattleState * SimulateBattle (Slot *a, int anum, Slot *d, int dnum, u64 met, u64 crys, u64 deut)
{
    long slot, i, n, aobjs = 0, dobjs = 0, idx;
    long apower, rapidfire, rapidchance, repairchance, fastdraw;
    Unit *aunits, *dunits, *unit;
    BattleState *bst;

    bst = (BattleState *)malloc (sizeof (BattleState));
    if (anum == 0) {
        bst->error = SPECSIM_ERROR_NOATT;
        return bst;
    }
    if (dnum == 0) {
        bst->error = SPECSIM_ERROR_NODEF;
        return bst;
    }
    if (anum > 16) anum = 16;   // ����������� �� 16 ������.
    if (dnum > 16) dnum = 16;
    
    // �������� ���������.
    memset (bst, 0, sizeof(BattleState));

    // ��������� ����������� ������ �� ���.
    for (i=0; i<anum; i++) {
        for (n=0; n<14; n++) aobjs += a[i].fleet[n];
    }
    for (i=0; i<dnum; i++) {
        for (n=0; n<14; n++) dobjs += d[i].fleet[n];
        if (i == 0) {
            for (n=0; n<8; n++) dobjs += d[i].def[n];
        }
    }

    // ����� ����� ����.
    aunits = InitBattleAttackers (a, anum, aobjs);
    if (aunits == NULL) {
        bst->error = SPECSIM_ERROR_NOMEM;
        return bst;
    }
    dunits = InitBattleDefenders (d, dnum, dobjs);
    if (dunits == NULL) {
        bst->error = SPECSIM_ERROR_NOMEM;
        return bst;
    }

    for (bst->rounds=0; bst->rounds<6; bst->rounds++)
    {
        if (aobjs == 0 || dobjs == 0) break;
        
        // �������� ����� ����� � �������� ���� ��������� ������.
        if ( AddRound (bst) == 0 ) return bst;        
        
        // �������� ����������.
        bst->round[bst->rounds].shoots[0] = bst->round[bst->rounds].shoots[1] = 0;
        bst->round[bst->rounds].spower[0] = bst->round[bst->rounds].spower[1] = 0;
        bst->round[bst->rounds].absorbed[0] = bst->round[bst->rounds].absorbed[1] = 0;

        // �������� ����.
        for (i=0; i<aobjs; i++) {
            if (aunits[i].exploded) aunits[i].shield = aunits[i].shieldmax = 0;
            else aunits[i].shield = aunits[i].shieldmax = fleetParam[aunits[i].obj_type-100].shield * (10+a[aunits[i].slot_id].shld) / 10;
        }
        for (i=0; i<dobjs; i++) {
            if (dunits[i].exploded) dunits[i].shield = dunits[i].shieldmax = 0;
            else {
                if (dunits[i].obj_type > 200) dunits[i].shield = dunits[i].shieldmax = defenseParam[dunits[i].obj_type-200].shield * (10+d[dunits[i].slot_id].shld) / 10;
                else dunits[i].shield = dunits[i].shieldmax = fleetParam[dunits[i].obj_type-100].shield * (10+d[dunits[i].slot_id].shld) / 10;
            }
        }
        
        // ���������� ��������.
        for (slot=0; slot<anum; slot++)     // ���������
        {
            for (i=0; i<aobjs; i++) {
                rapidfire = 1;
                unit = &aunits[i];
                if (unit->slot_id == slot) {
                    // �������.
                    while (rapidfire) {
                        idx = SimRand (0, dobjs-1);
                        apower = UnitShoot (unit, a[slot].weap, &dunits[idx], &bst->round[bst->rounds].absorbed[1], &bst->dloss, bst);
                        bst->round[bst->rounds].shoots[0]++;
                        bst->round[bst->rounds].spower[0] += apower;
                        if (unit->obj_type < 200) { // ������ ���� �������� ��������� ���������.
                            if (dunits[idx].obj_type < 200) rapidchance = FleetRapid[unit->obj_type-100][dunits[idx].obj_type-100];
                            else rapidchance = DefenseRapid[unit->obj_type-100][dunits[idx].obj_type-200];
                            rapidfire = SimRand (0, 999) < rapidchance;
                        }
                        else rapidfire = 0;
                        if (Rapidfire == 0) rapidfire = 0;
                    }
                }
            }
        }
        for (slot=0; slot<dnum; slot++)     // �������������
        {
            for (i=0; i<dobjs; i++) {
                rapidfire = 1;
                unit = &dunits[i];
                if (unit->slot_id == slot) {
                    // �������.
                    while (rapidfire) {
                        idx = SimRand (0, aobjs-1);
                        apower = UnitShoot (unit, d[slot].weap, &aunits[idx], &bst->round[bst->rounds].absorbed[0], &bst->aloss, bst);
                        bst->round[bst->rounds].shoots[1]++;
                        bst->round[bst->rounds].spower[1] += apower;
                        if (unit->obj_type < 200) { // ������ ���� �������� ��������� ���������.
                            if (aunits[idx].obj_type < 200) rapidchance = FleetRapid[unit->obj_type-100][aunits[idx].obj_type-100];
                            else rapidchance = DefenseRapid[unit->obj_type-100][aunits[idx].obj_type-200];
                            rapidfire = SimRand (0, 999) < rapidchance;
                        }
                        else rapidfire = 0;
                        if (Rapidfire == 0) rapidfire = 0;
                    }
                }
            }
        }

        // ������� �����?
        fastdraw = CheckFastDraw (aunits, aobjs, dunits, dobjs);

        // ��������� ���������� ������� � �������.
        aobjs -= WipeExploded (&aunits, aobjs);
        dobjs -= WipeExploded (&dunits, dobjs);
        
        AddUnits (bst, aunits, aobjs, dunits, dobjs);
        if (fastdraw) { bst->rounds=1; break; }
    }
    
    // �������� ��������� �����.
    if ( AddRound (bst) == 0 ) return bst;
    AddUnits (bst, aunits, aobjs, dunits, dobjs);

    // ���������� ���.
    if (aobjs > 0 && dobjs == 0){ 
        bst->result = SPECSIM_BATTLE_WON;
        Plunder (CalcCargo (aunits, aobjs), met, crys, deut, &bst->cm, &bst->ck, &bst->cd);
    }
    else if (dobjs > 0 && aobjs == 0) bst->result = SPECSIM_BATTLE_LOST;
    else bst->result = SPECSIM_BATTLE_DRAW;

    bst->moonchance = (int)((bst->dm + bst->dk) / 100000);
    if (bst->moonchance > 20) bst->moonchance = 20;

    // �������������� �������.
    if (bst->ExplodedDefenseTotal) {
        for (i=0; i<8; i++) {
            if (bst->ExplodedDefense[i]) {
                if (d[0].def[i] < 10) {
                    for (n=0; n<bst->ExplodedDefense[i]; n++) {
                        if ( SimRand (0, 99) < 70 ) { 
                            bst->RepairDefense[i]++;
                            bst->RepairDefenseTotal++;
                        }
                    }
                }
                else {
                    repairchance = SimRand (60, 80);
                    bst->RepairDefense[i] = repairchance * bst->ExplodedDefense[i] / 100;
                    bst->RepairDefenseTotal += bst->RepairDefense[i];
                }
            }
        }
    }
    
    free (aunits);
    free (dunits);        

    bst->error = SPECSIM_ERROR_NONE;
    return bst;
}

void CleanupBattle (BattleState *bst)
{
    int round;
    if (bst == NULL) return;
    if (bst->round) {
        for (round=0; round<bst->rounds; round++) {
            if (bst->round[round].aunits) free (bst->round[round].aunits);
            if (bst->round[round].dunits) free (bst->round[round].dunits);
        }
        free (bst->round);
    }
}
