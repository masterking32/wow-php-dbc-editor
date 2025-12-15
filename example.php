<?php
# Developed by: Amin Mahmoudi (MasterkinG32)
# Date: 2025
# Github: https://github.com/masterking32
# Website: https://masterking32.com

# Example usage

include 'dbc.php';
try {
    $dbcEditor = new MasterDBC_Editor('./Item.dbc');
    $dbcEditor->save_to_csv('./Item.csv');
    echo "DBC file has been successfully converted to CSV.\n";
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
