<?php

namespace App\Exports;

use App\Models\Producto;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

class InventarioExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $productos;

    public function __construct($productos)
    {
        $this->productos = $productos;
    }

    public function collection()
    {
        return $this->productos;
    }

    public function headings(): array
    {
        return [
            'Código',
            'Producto',
            'Categoría',
            'Precio Compra',
            'Precio Venta',
            'Stock',
            'Estado Stock'
        ];
    }

    public function map($producto): array
    {
        return [
            $producto->codigo,
            $producto->nombre,
            $producto->categoria ? $producto->categoria->nombre : 'Sin categoría',
            $producto->ultimo_precio_compra ? '$' . number_format($producto->ultimo_precio_compra, 2) : '-',
            '$' . number_format($producto->precio_venta, 2),
            $producto->stock,
            ucfirst($producto->estado_stock)
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $borderStyle = [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'D0D0D0'],
        ];

        $styles = [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '7E57C2']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders' => [
                    'allBorders' => $borderStyle,
                ]
            ],
            'A' => ['width' => 15],
            'B' => ['width' => 30],
            'C' => ['width' => 18],
            'D' => ['width' => 16],
            'E' => ['width' => 16],
            'F' => ['width' => 12],
            'G' => ['width' => 14],
        ];

        // Colorear filas alternas y aplicar estilos condicionales
        foreach (range(2, $sheet->getHighestRow()) as $row) {
            // Alternancia de colores
            if ($row % 2 == 0) {
                for ($col = 'A'; $col <= 'G'; $col++) {
                    $sheet->getStyle($col . $row)->getFill()->setFillType('solid')->setStartColor(new Color('F5F5F5'));
                }
            }

            // Aplicar bordes y alineación
            for ($col = 'A'; $col <= 'G'; $col++) {
                $style = $sheet->getStyle($col . $row);
                $style->getBorders()->getAllBorders()->setBorderStyle($borderStyle['borderStyle'])->setColor(new Color($borderStyle['color']['rgb']));
                if (in_array($col, ['D', 'E', 'F'])) {
                    $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
            }

            // Colorear estado de stock
            $estadoCell = $sheet->getCell('G' . $row);
            $estadoValue = $estadoCell->getValue();
            
            if ($estadoValue === 'bajo') {
                // Rojo para stock bajo
                $estadoCell->getStyle()->getFill()->setFillType('solid')->setStartColor(new Color('FFCDD2'));
                $estadoCell->getStyle()->getFont()->setBold(true)->setColor(new Color('C62828'));
                $estadoCell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            } elseif ($estadoValue === 'normal') {
                // Verde para stock normal
                $estadoCell->getStyle()->getFill()->setFillType('solid')->setStartColor(new Color('C8E6C9'));
                $estadoCell->getStyle()->getFont()->setBold(true)->setColor(new Color('1B5E20'));
                $estadoCell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        }

        // Congelar la primera fila (encabezados)
        $sheet->freezePane('A2');

        return $styles;
    }
}