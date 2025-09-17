<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Illuminate\Support\Collection;

class ReportExport implements FromCollection, WithHeadings, WithStyles, WithColumnFormatting, WithTitle, ShouldAutoSize
{
    protected $data;
    protected $config;
    protected $fieldLabels;

    public function __construct(Collection $data, array $config, array $fieldLabels)
    {
        $this->data = $data;
        $this->config = $config;
        $this->fieldLabels = $fieldLabels;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->data->map(function ($item) {
            $row = [];
            foreach ($this->config['fields'] as $fieldKey => $fieldValue) {
                $fieldName = is_numeric($fieldKey) ? $fieldValue : $fieldKey;
                $value = $item->{$fieldName} ?? '';
                
                // Format specific field types
                if (str_contains($fieldName, '_at') && $value) {
                    $value = \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s');
                } elseif (in_array($fieldName, ['total', 'subtotal', 'amount', 'estimated_value', 'paid_amount', 'balance']) && is_numeric($value)) {
                    $value = (float) $value; // Keep as number for Excel formatting
                } elseif ($fieldName === 'conversion_rate' && is_numeric($value)) {
                    $value = (float) $value / 100; // Convert percentage for Excel formatting
                }
                
                $row[] = $value;
            }
            return $row;
        });
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        $headings = [];
        foreach ($this->config['fields'] as $fieldKey => $fieldValue) {
            $fieldName = is_numeric($fieldKey) ? $fieldValue : $fieldKey;
            $headings[] = $this->fieldLabels[$fieldName] ?? ucfirst(str_replace('_', ' ', $fieldName));
        }
        return $headings;
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Header row styling
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2563EB'], // Blue header
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            
            // Data rows styling
            'A2:Z1000' => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        $formats = [];
        $columnIndex = 0;
        
        foreach ($this->config['fields'] as $fieldKey => $fieldValue) {
            $fieldName = is_numeric($fieldKey) ? $fieldValue : $fieldKey;
            $columnLetter = $this->getColumnLetter($columnIndex);
            
            if (str_contains($fieldName, '_at')) {
                $formats[$columnLetter] = NumberFormat::FORMAT_DATE_DATETIME;
            } elseif (in_array($fieldName, ['total', 'subtotal', 'amount', 'estimated_value', 'paid_amount', 'balance'])) {
                $formats[$columnLetter] = '#,##0.00_-'; // Currency format
            } elseif ($fieldName === 'conversion_rate') {
                $formats[$columnLetter] = NumberFormat::FORMAT_PERCENTAGE_00;
            } elseif (in_array($fieldName, ['phone', 'reference_number', 'number'])) {
                $formats[$columnLetter] = NumberFormat::FORMAT_TEXT;
            }
            
            $columnIndex++;
        }
        
        return $formats;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return ucfirst($this->config['report_type']) . ' Report';
    }

    /**
     * Convert column index to Excel column letter
     */
    private function getColumnLetter($index)
    {
        $letters = '';
        while ($index >= 0) {
            $letters = chr($index % 26 + 65) . $letters;
            $index = intval($index / 26) - 1;
        }
        return $letters;
    }
}