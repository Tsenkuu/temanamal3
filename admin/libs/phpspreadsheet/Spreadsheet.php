<?php

namespace PhpOffice\PhpSpreadsheet;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Spreadsheet
{
    private $sheets = [];
    private $activeSheetIndex = 0;

    public function __construct()
    {
        $this->sheets[] = new Worksheet($this, 'Worksheet');
    }

    public function getActiveSheet()
    {
        return $this->sheets[$this->activeSheetIndex];
    }

    public function createSheet()
    {
        $this->sheets[] = new Worksheet($this, 'Worksheet' . count($this->sheets));
        return end($this->sheets);
    }
}
