#ifndef __SIM_H__
#define __SIM_H__

#define SIM_VERSION "0.09"

// Длинное целое.
#ifndef WIN32
#include <linux/types.h>
typedef __u64 u64;
#else
typedef unsigned __int64 u64;
#endif

// Коды ошибок.
#define SPECSIM_ERROR_NONE      0
#define SPECSIM_ERROR_NOMEM     1       // Ошибка выделения памяти.
#define SPECSIM_ERROR_NOATT     2       // Нет атакующего
#define SPECSIM_ERROR_NODEF     3       // Нет обороняющегося

// Результат битвы.
#define SPECSIM_BATTLE_WON      0       // Атакующий выиграл
#define SPECSIM_BATTLE_LOST     1       // Атакующий проиграл
#define SPECSIM_BATTLE_DRAW     2       // Ничья

typedef struct TechParam {
    long    structure;
    long    shield;
    long    attack;
    long    cargo;  // только для флота.
} TechParam;

// Данные слота.
typedef struct Slot
{
    unsigned    long fleet[14];         // Флот
    unsigned    long def[8];            // Оборона
    int         weap, shld, armor;      // Технологии
    int         g, s, p;                // Координаты
    unsigned    char name[128];         // Имя игрока
} Slot;

// Данные юнита.
typedef struct Unit {
    unsigned char slot_id;
    unsigned char obj_type;
    unsigned char exploded;
    unsigned char dummy;                // Для выравнивания структуры на 4 байта.
    long    hull, hullmax;
    long    shield, shieldmax;
} Unit;

// Данные по раунду.
typedef struct RoundInfo {
    Unit        *aunits, *dunits;       // Данные юнитов на конец раунда
    int         aunum, dunum;
    u64         shoots[2], spower[2], absorbed[2]; // Общая статистика по выстрелам.    
    unsigned    long memload;
} RoundInfo;

// Состояние битвы.
typedef struct BattleState {
    int         error;              // Код ошибки, смотри SPECSIM_ERROR_*
    int         result;             // Результат боя, смотри SPECSIM_BATTLE_*
    int         rounds;
    RoundInfo   *round;
    u64         aloss, dloss;       // Потери атакующего и обороняющегося
    u64         dm, dk;             // Поле обломков
    u64         cm, ck, cd;         // Захвачено металла, кристалла, дейтерия
    int         moonchance;         // Шанс образования луны
    // Взорванная и восстановленная оборона.
    unsigned long ExplodedDefense[8], ExplodedDefenseTotal;
    unsigned long RepairDefense[8], RepairDefenseTotal;
} BattleState;

extern TechParam fleetParam[14];
extern TechParam defenseParam[8];

void SetDebrisOptions (int did, int fid);
void SetRapidfire (int enable);

// На выходе состояние битвы.
BattleState * SimulateBattle (Slot *a, int anum, Slot *d, int dnum, u64 met, u64 crys, u64 deut);

// Очистить состояние битвы (освободить память)
void CleanupBattle (BattleState *bst);

void SimSrand (unsigned long seed);
unsigned long SimRand (unsigned long a, unsigned long b);

#endif  // __SIM_H__