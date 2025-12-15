<?php
# Developed by: Amin Mahmoudi (MasterkinG32)
# Date: 2025
# Github: https://github.com/masterking32
# Website: https://masterking32.com

# This is a simple World of Warcraft PHP dbc editor class.
# Definition file for DBC structures.
# Check TrinityCore DBC structure definitions for reference. (src/server/shared/DBCStructure.h)

$definition = [
    'Item' => [
        ['uint32', 'ID'], // 0
        ['uint32', 'ClassID'], // 1
        ['uint32', 'SubClassID'], // 2
        ['int32', 'SoundOverrideSubClassID'], // 3
        ['int32', 'Material'], // 4
        ['uint32', 'DisplayInfoID'], // 5
        ['uint32', 'InventoryType'], // 6
        ['uint32', 'SheathType'], // 7
    ],
    'Map' => [
        ['uint32', 'ID'], // 0
        ['char const*', 'Directory'], // 1
        ['uint32', 'InstanceType'], // 2
        ['uint32', 'Flags'], // 3
        ['uint32', 'MapType'], // 4 0 or 1 for battlegrounds (not arenas)
        ['char const*[16]', 'MapName'], // 5-20
        ['uint32', 'MapName_lang_mask'], // 21
        ['uint32', 'AreaTableID'], // 22 common zone for instance and continent map
        ['char const*[16]', 'MapDescription0'], // 23-38 text for PvP Zones (Horde)
        ['uint32', 'MapDescription0_lang_mask'], // 39
        ['char const*[16]', 'MapDescription1'], // 40-55 text for PvP Zones (Alliance)
        ['uint32', 'MapDescription1_lang_mask'], // 56
        ['uint32', 'LoadingScreenID'], // 57
        ['float', 'MinimapIconScale'], // 58
        ['int32', 'CorpseMapID'], // 59 MapID of entrance map
        ['DBCPosition2D', 'Corpse'], // 60-61 entrance coordinate (if exist single entry)
        ['uint32', 'TimeOfDayOverride'], // 62 -1, 0 and 720
        ['uint32', 'ExpansionID'], // 63
        ['uint32', 'RaidOffset'], // 64
        ['uint32', 'MaxPlayers'], // 65 max players, fallback if not present in MapDifficulty.dbc
    ],
    'Spell' => [
        ['uint32', 'ID'],                                                          // 0
        ['uint32', 'Category'],                                                    // 1
        ['uint32', 'DispelType'],                                                  // 2
        ['uint32', 'Mechanic'],                                                    // 3
        ['uint32', 'Attributes'],                                                  // 4
        ['uint32', 'AttributesEx'],                                                // 5
        ['uint32', 'AttributesExB'],                                               // 6
        ['uint32', 'AttributesExC'],                                               // 7
        ['uint32', 'AttributesExD'],                                               // 8
        ['uint32', 'AttributesExE'],                                               // 9
        ['uint32', 'AttributesExF'],                                               // 10
        ['uint32', 'AttributesExG'],                                               // 11
        ['std::array<uint32, 2>', 'ShapeshiftMask'],                               // 12-13
        ['std::array<uint32, 2>', 'ShapeshiftExclude'],                            // 14-15
        ['uint32', 'Targets'],                                                     // 16
        ['uint32', 'TargetCreatureType'],                                          // 17
        ['uint32', 'RequiresSpellFocus'],                                          // 18
        ['uint32', 'FacingCasterFlags'],                                           // 19
        ['uint32', 'CasterAuraState'],                                             // 20
        ['uint32', 'TargetAuraState'],                                             // 21
        ['uint32', 'ExcludeCasterAuraState'],                                      // 22
        ['uint32', 'ExcludeTargetAuraState'],                                      // 23
        ['uint32', 'CasterAuraSpell'],                                             // 24
        ['uint32', 'TargetAuraSpell'],                                             // 25
        ['uint32', 'ExcludeCasterAuraSpell'],                                      // 26
        ['uint32', 'ExcludeTargetAuraSpell'],                                      // 27
        ['uint32', 'CastingTimeIndex'],                                            // 28
        ['uint32', 'RecoveryTime'],                                                // 29
        ['uint32', 'CategoryRecoveryTime'],                                        // 30
        ['uint32', 'InterruptFlags'],                                              // 31
        ['uint32', 'AuraInterruptFlags'],                                          // 32
        ['uint32', 'ChannelInterruptFlags'],                                       // 33
        ['uint32', 'ProcTypeMask'],                                                // 34
        ['uint32', 'ProcChance'],                                                  // 35
        ['uint32', 'ProcCharges'],                                                 // 36
        ['uint32', 'MaxLevel'],                                                    // 37
        ['uint32', 'BaseLevel'],                                                   // 38
        ['uint32', 'SpellLevel'],                                                  // 39
        ['uint32', 'DurationIndex'],                                               // 40
        ['uint32', 'PowerType'],                                                   // 41
        ['uint32', 'ManaCost'],                                                    // 42
        ['uint32', 'ManaCostPerLevel'],                                            // 43
        ['uint32', 'ManaPerSecond'],                                               // 44
        ['uint32', 'ManaPerSecondPerLevel'],                                       // 45
        ['uint32', 'RangeIndex'],                                                  // 46
        ['float', 'Speed'],                                                        // 47
        ['uint32', 'ModalNextSpell'],                                            // 48
        ['uint32', 'CumulativeAura'],                                              // 49
        ['std::array<uint32, 2>', 'Totem'],                                        // 50-51
        ['std::array<int32, 8>', 'Reagent'],                      // 52-59
        ['std::array<uint32, 8>', 'ReagentCount'],                // 60-67
        ['int32', 'EquippedItemClass'],                                            // 68
        ['int32', 'EquippedItemSubclass'],                                         // 69
        ['int32', 'EquippedItemInvTypes'],                                         // 70
        ['std::array<uint32, 3>', 'Effect'],                       // 71-73
        ['std::array<int32, 3>', 'EffectDieSides'],                // 74-76
        ['std::array<float, 3>', 'EffectRealPointsPerLevel'],      // 77-79
        ['std::array<int32, 3>', 'EffectBasePoints'],              // 80-82
        ['std::array<uint32, 3>', 'EffectMechanic'],               // 83-85
        ['std::array<uint32, 3>', 'EffectImplicitTargetA'],        // 86-88
        ['std::array<uint32, 3>', 'EffectImplicitTargetB'],        // 89-91
        ['std::array<uint32, 3>', 'EffectRadiusIndex'],            // 92-94
        ['std::array<uint32, 3>', 'EffectAura'],                   // 95-97
        ['std::array<uint32, 3>', 'EffectAuraPeriod'],             // 98-100
        ['std::array<float, 3>', 'EffectAmplitude'],               // 101-103
        ['std::array<uint32, 3>', 'EffectChainTargets'],           // 104-106
        ['std::array<uint32, 3>', 'EffectItemType'],               // 107-109
        ['std::array<int32, 3>', 'EffectMiscValue'],               // 110-112
        ['std::array<int32, 3>', 'EffectMiscValueB'],              // 113-115
        ['std::array<uint32, 3>', 'EffectTriggerSpell'],           // 116-118
        ['std::array<float, 3>', 'EffectPointsPerCombo'],          // 119-121
        ['std::array<flag96, 3>', 'EffectSpellClassMask'],         // 122-130
        ['std::array<uint32, 2>', 'SpellVisualID'],                // 131-132
        ['uint32', 'SpellIconID'],                                 // 133
        ['uint32', 'ActiveIconID'],                                // 134
        ['uint32', 'SpellPriority'],                               // 135
        ['char const*[16]', 'Name'],                               // 136-151
        ['uint32', 'Name_lang_mask'],                              // 152
        ['char const*[16]', 'NameSubtext'],                        // 153-168
        ['uint32', 'NameSubtext_lang_mask'],                       // 169
        ['char const*[16]', 'Description'],                          // 170-185
        ['uint32', 'Description_lang_mask'],                         // 186
        ['char const*[16]', 'AuraDescription'],                      // 187-202
        ['uint32', 'AuraDescription_lang_mask'],                     // 203
        ['uint32', 'ManaCostPct'],                                 // 204
        ['uint32', 'StartRecoveryCategory'],                       // 205
        ['uint32', 'StartRecoveryTime'],                           // 206
        ['uint32', 'MaxTargetLevel'],                              // 207
        ['uint32', 'SpellClassSet'],                               // 208
        ['flag96', 'SpellClassMask'],                              // 209-211
        ['uint32', 'MaxTargets'],                                  // 212
        ['uint32', 'DefenseType'],                                 // 213
        ['uint32', 'PreventionType'],                              // 214
        ['uint32', 'StanceBarOrder'],                              // 215
        ['std::array<float, 3>', 'EffectChainAmplitude'],          // 216-218
        ['uint32', 'MinFactionID'],                                // 219
        ['uint32', 'MinReputation'],                               // 220
        ['uint32', 'RequiredAuraVision'],                          // 221
        ['std::array<uint32, 2>', 'RequiredTotemCategoryID'],      // 222-223
        ['int32', 'RequiredAreasID'],                              // 224
        ['uint32', 'SchoolMask'],                                  // 225
        ['uint32', 'RuneCostID'],                                  // 226
        ['uint32', 'SpellMissileID'],                              // 227
        ['uint32', 'PowerDisplayID'],                              // 228
        ['std::array<float, 3>', 'EffectBonusCoefficient'],        // 229-231
        ['uint32', 'DescriptionVariablesID'],                      // 232
        ['uint32', 'Difficulty'],                                  // 233
    ],
    // Add more DBC definitions as needed
];
