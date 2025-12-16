<?php
# Developed by: Amin Mahmoudi (MasterkinG32)
# Date: 2025
# Github: https://github.com/masterking32
# Website: https://masterking32.com
# This is a simple World of Warcraft PHP dbc editor class.

class MasterDBC_Editor
{
    private $dbcFilePath;
    private $file_handle;
    public $dbc_header; // magic, record_count, field_count, record_size, string_block_size
    public $dbc_records;
    public $definition;
    public function __construct($dbcFilePath)
    {
        if (!file_exists($dbcFilePath)) {
            throw new Exception("DBC file not found: " . $dbcFilePath);
        }

        $this->dbcFilePath = $dbcFilePath;
        if (strtolower(substr($dbcFilePath, -4)) === '.dbc') {
            $this->load_dbc();
        } elseif (strtolower(substr($dbcFilePath, -4)) === '.csv') {
            $this->load_csv();
        } else {
            throw new Exception("Unsupported file format: " . $dbcFilePath);
        }
    }

    private function load_csv()
    {
        $fileName = str_replace('.csv', '', basename($this->dbcFilePath));
        include __DIR__ . '/definition.php';
        if (!isset($definition[$fileName])) {
            throw new Exception("No definition found for CSV file: " . $fileName);
        }

        $this->definition = $definition[$fileName];
        $this->dbc_records = [];
        if (($handle = fopen($this->dbcFilePath, 'r')) !== false) {
            $headers = fgetcsv($handle);
            while (($data = fgetcsv($handle)) !== false) {
                $record = [];
                foreach ($headers as $index => $header) {
                    $value = $data[$index];
                    // Decode JSON arrays
                    $decodedValue = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $record[$header] = $decodedValue;
                    } else {
                        $record[$header] = $value;
                    }
                }
                $this->dbc_records[] = $record;
            }
            fclose($handle);
        } else {
            throw new Exception("Failed to open CSV file: " . $this->dbcFilePath);
        }
    }

    private function load_dbc()
    {
        $fileName = str_replace('.dbc', '', basename($this->dbcFilePath));

        include __DIR__ . '/definition.php';
        if (!isset($definition[$fileName])) {
            throw new Exception("No definition found for DBC file: " . $fileName);
        }

        $this->definition = $definition[$fileName];

        $this->file_handle = fopen($this->dbcFilePath, 'r+b');
        if (!$this->file_handle) {
            throw new Exception("Failed to open DBC file: " . $this->dbcFilePath);
        }

        $this->read_dbc_header();
        $this->load_records();
    }

    public function read_dbc_header()
    {
        fseek($this->file_handle, 0);
        $headerData = fread($this->file_handle, 20);
        $header = unpack('Vmagic/Vrecord_count/Vfield_count/Vrecord_size/Vstring_block_size', $headerData);
        if ($header === false) {
            throw new Exception("Failed to read DBC header.");
        }

        if ($header['magic'] !== 0x43424457) { // 'WDBC' in little-endian
            throw new Exception("Invalid DBC file format.");
        }

        if (!empty($header)) {
            $this->dbc_header = $header;
        } else {
            throw new Exception("DBC header is not set.");
        }
    }

    private function load_records()
    {
        $this->dbc_records = [];
        $current_offset = 20; // after header
        $string_block_offset = 20 + ($this->dbc_header['record_count'] * $this->dbc_header['record_size']);
        for ($i = 0; $i < $this->dbc_header['record_count']; $i++) {
            fseek($this->file_handle, $current_offset);
            $recordData = fread($this->file_handle, $this->dbc_header['record_size']);
            $record = [];
            $offset = 0;
            foreach ($this->definition as $field) {
                $type = $field[0];
                $name = $field[1];
                $record[$name] = $this->read_data($type, $recordData, $offset, $string_block_offset);
            }
            $this->dbc_records[] = $record;
            $current_offset += $this->dbc_header['record_size'];
        }
    }

    private function read_data($type, $data, &$offset, $string_block_offset)
    {
        if (preg_match('/^std::array<(.+?),\s*(\d+)>$/', $type, $matches)) {
            $elementType = trim($matches[1]);
            $arraySize = (int)$matches[2];
            $result = [];
            for ($i = 0; $i < $arraySize; $i++) {
                $result[] = $this->read_data($elementType, $data, $offset, $string_block_offset);
            }
            return $result;
        }

        if ($type === 'flag96') {
            return [
                $this->read_int($data, $offset, true, 32),
                $this->read_int($data, $offset, true, 32),
                $this->read_int($data, $offset, true, 32)
            ];
        }

        if (in_array($type, ['uint32', 'int32'])) {
            $uint = ($type === 'uint32');
            return $this->read_int($data, $offset, $uint, 32);
        } elseif (in_array($type, ['uint16', 'int16'])) {
            $uint = ($type === 'uint16');
            return $this->read_int($data, $offset, $uint, 16);
        } elseif (in_array($type, ['uint8', 'int8'])) {
            $uint = ($type === 'uint8');
            return $this->read_int($data, $offset, $uint, 8);
        } elseif (in_array($type, ['uint64', 'int64'])) {
            $uint = ($type === 'uint64');
            return $this->read_int($data, $offset, $uint, 64);
        } elseif (in_array($type, ['float', 'double'])) {
            return $this->read_float($data, $offset, $type === 'double');
        } elseif (preg_match('/^char const\*\[(\d+)\]$/', $type, $matches)) {
            $length = (int)$matches[1];
            $result = '';
            for ($i = 0; $i < $length; $i++) {
                $result .= $this->read_string_from_block($data, $offset, $string_block_offset);
            }

            return $result;
        } elseif (in_array($type, ['char const*', 'string'])) {
            return $this->read_string_from_block($data, $offset, $string_block_offset);
        } elseif ($type === 'DBCPosition2D') {
            return $this->read_2d_position($data, $offset);
        } elseif ($type === 'DBCPosition3D') {
            return $this->read_3d_position($data, $offset);
        } else {
            throw new Exception("Unsupported data type: " . $type);
        }
    }

    private function write_data($type, $value, &$stringBlock, &$stringOffsets)
    {
        $data = '';
        if (preg_match('/^std::array<(.+?),\s*(\d+)>$/', $type, $matches)) {
            $elementType = trim($matches[1]);
            foreach ($value as $element) {
                $data .= $this->write_data($elementType, $element, $stringBlock, $stringOffsets);
            }
        } elseif ($type === 'flag96') {
            foreach ($value as $part) {
                $data .= pack('V', $part);
            }
        } elseif (in_array($type, ['uint32', 'int32'])) {
            $format = ($type === 'uint32') ? 'V' : 'l';
            $data .= pack($format, $value);
        } elseif (in_array($type, ['uint16', 'int16'])) {
            $format = ($type === 'uint16') ? 'v' : 's';
            $data .= pack($format, $value);
        } elseif (in_array($type, ['uint8', 'int8'])) {
            $format = ($type === 'uint8') ? 'C' : 'c';
            $data .= pack($format, $value);
        } elseif (in_array($type, ['uint64', 'int64'])) {
            $format = ($type === 'uint64') ? 'P' : 'q';
            $data .= pack($format, $value);
        } elseif (in_array($type, ['float', 'double'])) {
            $format = ($type === 'double') ? 'd' : 'f';
            $data .= pack($format, $value);
        } elseif (preg_match('/^char const\*\[(\d+)\]$/', $type, $matches)) {
            // String array
            $length = (int)$matches[1];
            $stringValue = is_string($value) ? $value : '';
            for ($i = 0; $i < $length; $i++) {
                $offset = $this->add_to_string_block($stringValue, $stringBlock, $stringOffsets);
                $data .= pack('V', $offset);
            }
        } elseif (in_array($type, ['char const*', 'string'])) {
            // Single string
            $stringValue = is_string($value) ? $value : '';
            $offset = $this->add_to_string_block($stringValue, $stringBlock, $stringOffsets);
            $data .= pack('V', $offset);
        } elseif ($type === 'DBCPosition2D') {
            $data .= pack('f', $value['x'] ?? 0.0);
            $data .= pack('f', $value['y'] ?? 0.0);
        } elseif ($type === 'DBCPosition3D') {
            $data .= pack('f', $value['x'] ?? 0.0);
            $data .= pack('f', $value['y'] ?? 0.0);
            $data .= pack('f', $value['z'] ?? 0.0);
        } else {
            throw new Exception("Unsupported data type for writing: " . $type);
        }
        return $data;
    }

    private function add_to_string_block($string, &$stringBlock, &$stringOffsets)
    {
        if (!isset($stringOffsets[$string])) {
            $stringOffsets[$string] = strlen($stringBlock);
            $stringBlock .= $string . "\0";
        }
        return $stringOffsets[$string];
    }

    private function read_int($data, &$offset, $uint = true, $int_type = 32)
    {
        if ($int_type === 32) {
            $format = $uint ? 'V' : 'l';
            $value = unpack($format, substr($data, $offset, 4))[1];
            $offset += 4;
        } elseif ($int_type === 16) {
            $format = $uint ? 'v' : 's';
            $value = unpack($format, substr($data, $offset, 2))[1];
            $offset += 2;
        } elseif ($int_type === 8) {
            $format = $uint ? 'C' : 'c';
            $value = unpack($format, substr($data, $offset, 1))[1];
            $offset += 1;
        } elseif ($int_type === 64) {
            $format = $uint ? 'P' : 'q';
            $value = unpack($format, substr($data, $offset, 8))[1];
            $offset += 8;
        } else {
            throw new Exception("Unsupported integer type: " . $int_type);
        }
        return $value;
    }

    private function read_float($data, &$offset, $is_double = false)
    {
        if ($is_double) {
            $value = unpack('d', substr($data, $offset, 8))[1];
            $offset += 8;
        } else {
            $value = unpack('f', substr($data, $offset, 4))[1];
            $offset += 4;
        }
        return $value;
    }

    private function read_string_from_block($data, &$offset, $string_block_offset)
    {
        $string_offset = $this->read_int($data, $offset, true, 32);
        $position = $string_block_offset + $string_offset;
        fseek($this->file_handle, $position);
        $string = '';
        while (($char = fread($this->file_handle, 1)) !== "\0" && $char !== false) {
            $string .= $char;
        }
        return $string;
    }

    private function read_2d_position($data, &$offset)
    {
        $x = $this->read_float($data, $offset);
        $y = $this->read_float($data, $offset);
        return ['x' => $x, 'y' => $y];
    }

    private function read_3d_position($data, &$offset)
    {
        $x = $this->read_float($data, $offset);
        $y = $this->read_float($data, $offset);
        $z = $this->read_float($data, $offset);
        return ['x' => $x, 'y' => $y, 'z' => $z];
    }

    public function save_to_csv($outputFilePath)
    {
        $file = fopen($outputFilePath, 'w');
        if (!$file) {
            throw new Exception("Failed to open output CSV file: " . $outputFilePath);
        }

        $headers = array_keys($this->dbc_records[0]);
        fputcsv($file, $headers);

        foreach ($this->dbc_records as $record) {
            $flatRecord = array_map(function ($value) {
                if (is_array($value)) {
                    return json_encode($value);
                }
                return is_string($value) ? str_replace(["\r\n", "\r", "\n"], ' ', $value) : $value;
            }, $record);
            fputcsv($file, $flatRecord);
        }

        fclose($file);
    }

    public function save_to_dbc($outputFilePath)
    {
        if (!is_array($this->dbc_records) || empty($this->dbc_records)) {
            throw new Exception("No records to save.");
        }

        $file = fopen($outputFilePath, 'w+b');
        if (!$file) {
            throw new Exception("Failed to open output DBC file: " . $outputFilePath);
        }

        $recordSize = $this->dbc_header['record_size'] ?? $this->calculate_record_size();

        $stringBlock = "\0";
        $stringOffsets = ['' => 0];

        $allRecordData = '';
        foreach ($this->dbc_records as $record) {
            $recordData = '';
            foreach ($this->definition as $field) {
                $type = $field[0];
                $name = $field[1];
                $value = isset($record[$name]) ? $record[$name] : null;
                $recordData .= $this->write_data($type, $value, $stringBlock, $stringOffsets);
            }
            $allRecordData .= $recordData;
        }

        $headerData = pack(
            'V5',
            0x43424457, // 'WDBC'
            count($this->dbc_records),
            count($this->definition),
            $recordSize,
            strlen($stringBlock)
        );

        fwrite($file, $headerData);
        fwrite($file, $allRecordData);
        fwrite($file, $stringBlock);

        fclose($file);
    }

    private function calculate_record_size()
    {
        $size = 0;
        foreach ($this->definition as $field) {
            $type = $field[0];
            $size += $this->get_type_size($type);
        }
        return $size;
    }

    private function get_type_size($type)
    {
        if (preg_match('/^std::array<(.+?),\s*(\d+)>$/', $type, $matches)) {
            $elementType = trim($matches[1]);
            $arraySize = (int)$matches[2];
            return $this->get_type_size($elementType) * $arraySize;
        }

        if ($type === 'flag96') {
            return 12; // 3 * 4 bytes
        }

        if (preg_match('/^char const\*\[(\d+)\]$/', $type, $matches)) {
            $arraySize = (int)$matches[1];
            return 4 * $arraySize; // Each string is a 4-byte offset
        }

        $sizeMap = [
            'uint32' => 4,
            'int32' => 4,
            'uint16' => 2,
            'int16' => 2,
            'uint8' => 1,
            'int8' => 1,
            'uint64' => 8,
            'int64' => 8,
            'float' => 4,
            'double' => 8,
            'char const*' => 4,
            'string' => 4
        ];

        if (isset($sizeMap[$type])) {
            return $sizeMap[$type];
        }

        if ($type === 'DBCPosition2D') {
            return 8; // 2 floats
        }

        if ($type === 'DBCPosition3D') {
            return 12; // 3 floats
        }

        throw new Exception("Unable to calculate size for type: " . $type);
    }

    public function __destruct()
    {
        if ($this->file_handle) {
            fclose($this->file_handle);
        }
    }
}
