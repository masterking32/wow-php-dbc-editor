<?php
# Developed by: Amin Mahmoudi (MasterkinG32)
# Date: 2025
# Github: https://github.com/masterking32
# Website: https://masterking32.com

# Example usage

include 'dbc.php';
try {
    // Load DBC file
    $dbcEditor = new MasterDBC_Editor('./Item.dbc');

    // Save to CSV If needed
    $dbcEditor->save_to_csv('./Item.csv');
    echo "DBC file has been successfully converted to CSV.<br>";

    // Getting records
    $records = $dbcEditor->dbc_records;
    // get record index when ID = 100
    $recordIndex = array_search(100, array_column($records, 'ID'));
    if ($recordIndex !== false) {
        echo "Record with ID 100 found at index: " . $recordIndex . "<br>";
        echo "Record details:<br>";
        print_r($records[$recordIndex]);
        echo "<br>";
        // Modify a field value
        $dbcEditor->dbc_records[$recordIndex]['DisplayInfoID'] = 99999;
        echo "Modified DisplayInfoID to 99999.<br>";
        echo "Updated Record details:<br>";
        print_r($dbcEditor->dbc_records[$recordIndex]);
        echo "<br>";
    }

    // Removing a record with ID = 200
    $recordIndexToRemove = array_search(200, array_column($records, 'ID'));
    if ($recordIndexToRemove !== false) {
        unset($dbcEditor->dbc_records[$recordIndexToRemove]);
        // Reindex array
        $dbcEditor->dbc_records = array_values($dbcEditor->dbc_records);
        echo "Record with ID 200 has been removed.<br>";
    }

    // Add a new record
    $newRecord = [
        'ID' => 999999,
        'ClassID' => 1,
        'SubClassID' => 0,
        'SoundOverrideSubClassID' => 0,
        'Material' => 1,
        'DisplayInfoID' => 12345,
        'InventoryType' => 13,
        'SheathType' => 0,
    ];
    $dbcEditor->dbc_records[] = $newRecord;
    echo "New record with ID 999999 has been added.<br>";

    // Save back to DBC
    $dbcEditor->save_to_dbc('./Item_new.dbc');
    echo "CSV file has been successfully saved back to DBC.<br>";
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "<br>";
}
