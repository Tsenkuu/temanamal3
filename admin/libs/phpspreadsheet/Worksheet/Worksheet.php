<?php

namespace PhpOffice\PhpSpreadsheet\Worksheet;

class Worksheet
{
    private $parent;
    private $title;
    private $cellCollection = [];

    public function __construct($parent, $title)
    {
        $this->parent = $parent;
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    /**
     * [BARU] Menambahkan fungsi setTitle yang hilang.
     * @param string $title Judul baru untuk worksheet.
     * @return $this
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
        return $this;
    }

    public function setCellValue($coordinate, $value)
    {
        $this->cellCollection[$coordinate] = $value;
        return $this;
    }

    public function getCellCollection()
    {
        return $this->cellCollection;
    }
    
    public function getColumnDimension($column)
    {
        // Mock dimension object for auto-sizing
        return new class {
            public function setAutoSize(bool $autoSize): void {}
        };
    }
}

