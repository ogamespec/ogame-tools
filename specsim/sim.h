#ifndef __SIM_H__
#define __SIM_H__

#define SIM_VERSION "0.09"

// ������� �����.
#ifndef WIN32
#include <linux/types.h>
typedef __u64 u64;
#else
typedef unsigned __int64 u64;
#endif

// ���� ������.
#define SPECSIM_ERROR_NONE      0
#define SPECSIM_ERROR_NOMEM     1       // ������ ��������� ������.
#define SPECSIM_ERROR_NOATT     2       // ��� ����������
#define SPECSIM_ERROR_NODEF     3       // ��� ��������������

// ��������� �����.
#define SPECSIM_BATTLE_WON      0       // ��������� �������
#define SPECSIM_BATTLE_LOST     1       // ��������� ��������
#define SPECSIM_BATTLE_DRAW     2       // �����

typedef struct TechParam {
    long    structure;
    long    shield;
    long    attack;
    long    cargo;  // ������ ��� �����.
} TechParam;

// ������ �����.
typedef struct Slot
{
    unsigned    long fleet[14];         // ����
    unsigned    long def[8];            // �������
    int         weap, shld, armor;      // ����������
    int         g, s, p;                // ����������
    unsigned    char name[128];         // ��� ������
} Slot;

// ������ �����.
typedef struct Unit {
    unsigned char slot_id;
    unsigned char obj_type;
    unsigned char exploded;
    unsigned char dummy;                // ��� ������������ ��������� �� 4 �����.
    long    hull, hullmax;
    long    shield, shieldmax;
} Unit;

// ������ �� ������.
typedef struct RoundInfo {
    Unit        *aunits, *dunits;       // ������ ������ �� ����� ������
    int         aunum, dunum;
    u64         shoots[2], spower[2], absorbed[2]; // ����� ���������� �� ���������.    
    unsigned    long memload;
} RoundInfo;

// ��������� �����.
typedef struct BattleState {
    int         error;              // ��� ������, ������ SPECSIM_ERROR_*
    int         result;             // ��������� ���, ������ SPECSIM_BATTLE_*
    int         rounds;
    RoundInfo   *round;
    u64         aloss, dloss;       // ������ ���������� � ��������������
    u64         dm, dk;             // ���� ��������
    u64         cm, ck, cd;         // ��������� �������, ���������, ��������
    int         moonchance;         // ���� ����������� ����
    // ���������� � ��������������� �������.
    unsigned long ExplodedDefense[8], ExplodedDefenseTotal;
    unsigned long RepairDefense[8], RepairDefenseTotal;
} BattleState;

extern TechParam fleetParam[14];
extern TechParam defenseParam[8];

void SetDebrisOptions (int did, int fid);
void SetRapidfire (int enable);

// �� ������ ��������� �����.
BattleState * SimulateBattle (Slot *a, int anum, Slot *d, int dnum, u64 met, u64 crys, u64 deut);

// �������� ��������� ����� (���������� ������)
void CleanupBattle (BattleState *bst);

void SimSrand (unsigned long seed);
unsigned long SimRand (unsigned long a, unsigned long b);

#endif  // __SIM_H__